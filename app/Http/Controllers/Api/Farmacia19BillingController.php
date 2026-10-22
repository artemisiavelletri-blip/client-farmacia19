<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class Farmacia19BillingController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $address = $user->billingAddresses()->first();
        return response()->json([
            'success' => true,
            'data' => [
                'name' => $user->name,
                'surname' => $user->surname,
                'user_type' => $user->user_type,
                'cf' => $user->cf,
                'company_society' => $user->company_society,
                'company_cf' => $user->company_cf,
                'company_pi' => $user->company_pi,
                'company_sdi' => $user->company_sdi,
                'company_pec' => $user->company_pec,
                'address' => $address ? [
                    'id' => $address->id,
                    'address' => $address->address,
                    'city_id' => $address->city_id,
                    'city_name' => method_exists($address, 'city') ? optional($address->city)->name : null,
                    'cap' => $address->cap,
                    'phone' => $address->phone,
                    'note' => $address->note,
                ] : null,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $company = (int) $user->user_type === 1;
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city_id' => ['required', 'integer', 'min:1'],
            'cap' => ['required', 'digits:5'],
            'phone' => ['required', 'string', 'max:20'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
        if ($company) {
            $rules += [
                'company_society' => ['required', 'string', 'max:255'],
                'company_cf' => ['required', 'string'],
                'company_pi' => ['required', 'string', 'max:11'],
                'company_sdi' => ['nullable', 'string', 'max:20'],
                'company_pec' => ['nullable', 'email', 'max:255'],
            ];
        } else {
            $rules['cf'] = ['required', 'string', 'size:16', 'regex:/^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/i'];
        }
        $data = $request->validate($rules);
        DB::transaction(function () use ($user, $data, $company) {
            $user->name = $data['name'];
            $user->surname = $data['surname'];
            if ($company) {
                foreach (['company_society', 'company_cf', 'company_pi', 'company_sdi', 'company_pec'] as $field) {
                    $user->{$field} = $data[$field] ?? null;
                }
            } else {
                $user->cf = strtoupper($data['cf']);
            }
            $user->save();
            $address = $user->billingAddresses()->first();
            if (!$address) {
                $address = new \App\Models\Address();
                $address->user_id = $user->id;
                $address->type = 'billing';
            }
            foreach (['address', 'city_id', 'cap', 'phone', 'note'] as $field) {
                $address->{$field} = $data[$field] ?? null;
            }
            $address->save();
        });
        return $this->show($request);
    }
}
