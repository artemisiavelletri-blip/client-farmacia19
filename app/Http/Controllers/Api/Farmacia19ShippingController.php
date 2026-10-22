<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class Farmacia19ShippingController extends Controller
{
    private function formatAddress(Address $address): array
    {
        return [
            'id' => $address->id,
            'recipient_name' => $address->recipient_name,
            'address' => $address->address,
            'city_id' => $address->city_id,
            'city_name' => method_exists($address, 'city') ? optional($address->city)->name : null,
            'cap' => $address->cap,
            'phone' => $address->phone,
            'note' => $address->note,
            'is_default' => (bool) $address->default,
        ];
    }

    public function index(Request $request)
    {
        $addresses = $request->user()->shippingAddresses()->orderByDesc('default')->orderByDesc('id')->get();
        return response()->json(['success' => true, 'data' => $addresses->map(fn ($address) => $this->formatAddress($address))->values()]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city_id' => ['required', 'integer', 'min:1'],
            'cap' => ['required', 'digits:5'],
            'phone' => ['required', 'string', 'max:20'],
            'note' => ['nullable', 'string', 'max:1000'],
            'is_default' => ['sometimes', 'boolean'],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $user = $request->user();
        $address = DB::transaction(function () use ($user, $data) {
            $existing = $user->shippingAddresses()->lockForUpdate()->get();
            $makeDefault = count($existing) === 0 || !empty($data['is_default']);
            if ($makeDefault) {
                $user->shippingAddresses()->update(['default' => null]);
            }
            $address = new Address();
            $address->user_id = $user->id;
            $address->type = 'shipping';
            foreach (['recipient_name', 'address', 'city_id', 'cap', 'phone', 'note'] as $field) {
                $address->{$field} = $data[$field] ?? null;
            }
            $address->default = $makeDefault ? 1 : null;
            $address->save();
            return $address;
        });
        return response()->json(['success' => true, 'data' => $this->formatAddress($address)], 201);
    }

    public function update(Request $request, int $id)
    {
        $data = $this->validated($request);
        $user = $request->user();
        $address = DB::transaction(function () use ($user, $id, $data) {
            $address = $user->shippingAddresses()->whereKey($id)->lockForUpdate()->firstOrFail();
            if (!empty($data['is_default'])) {
                $user->shippingAddresses()->where('id', '!=', $address->id)->update(['default' => null]);
                $address->default = 1;
            }
            foreach (['recipient_name', 'address', 'city_id', 'cap', 'phone', 'note'] as $field) {
                $address->{$field} = $data[$field] ?? null;
            }
            $address->save();
            return $address;
        });
        return response()->json(['success' => true, 'data' => $this->formatAddress($address)]);
    }

    public function setDefault(Request $request, int $id)
    {
        $user = $request->user();
        DB::transaction(function () use ($user, $id) {
            $address = $user->shippingAddresses()->whereKey($id)->lockForUpdate()->firstOrFail();
            $user->shippingAddresses()->where('id', '!=', $address->id)->update(['default' => null]);
            $address->default = 1;
            $address->save();
        });
        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, int $id)
    {
        $user = $request->user();
        DB::transaction(function () use ($user, $id) {
            $address = $user->shippingAddresses()->whereKey($id)->lockForUpdate()->firstOrFail();
            $wasDefault = (bool) $address->default;
            $address->delete();
            if ($wasDefault) {
                $replacement = $user->shippingAddresses()->orderByDesc('id')->lockForUpdate()->first();
                if ($replacement) {
                    $replacement->default = 1;
                    $replacement->save();
                }
            }
        });
        return response()->json(['success' => true]);
    }
}
