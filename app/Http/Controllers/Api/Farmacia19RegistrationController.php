<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Address;
use App\Services\Google\GmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class Farmacia19RegistrationController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $rules = [
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/', 'confirmed'],
            'user_type' => ['required', Rule::in([0, 1])],
            'terms_service' => ['accepted'],
        ];

        $type = (string) $request->input('user_type');
        if ($type === '0') {
            $rules += [
                'private_name' => ['required', 'string', 'max:255'],
                'private_surname' => ['required', 'string', 'max:255'],
                'private_cf' => ['required', 'string', 'size:16', 'regex:/^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/i'],
                'private_address' => ['required', 'string', 'max:255'],
                'private_city_id' => ['required', 'integer', 'exists:cities,id'],
                'private_cap' => ['required', 'digits:5'],
                'private_phone' => ['required', 'string', 'max:20'],
                'private_note' => ['nullable', 'string'],
            ];
            if (!$request->boolean('private_check_second_address')) {
                $rules += [
                    'private_delivery' => ['required', 'string', 'max:255'],
                    'private_second_address' => ['required', 'string', 'max:255'],
                    'private_second_city_id' => ['required', 'integer', 'exists:cities,id'],
                    'private_second_cap' => ['required', 'digits:5'],
                    'private_second_phone' => ['required', 'string', 'max:20'],
                    'private_second_note' => ['nullable', 'string'],
                ];
            }
        } elseif ($type === '1') {
            $rules += [
                'company_society' => ['required', 'string', 'max:255'],
                'company_name' => ['required', 'string', 'max:255'],
                'company_surname' => ['required', 'string', 'max:255'],
                'company_cf' => ['required', 'string'],
                'company_pi' => ['required', 'string', 'max:11'],
                'company_sdi' => ['nullable', 'string', 'max:255'],
                'company_address' => ['required', 'string', 'max:255'],
                'company_city_id' => ['required', 'integer', 'exists:cities,id'],
                'company_cap' => ['required', 'digits:5'],
                'company_phone' => ['required', 'string', 'max:20'],
                'company_note' => ['nullable', 'string'],
            ];
            if (!$request->boolean('company_check_second_address')) {
                $rules += [
                    'company_second_society' => ['required', 'string', 'max:255'],
                    'company_second_address' => ['required', 'string', 'max:255'],
                    'company_second_city_id' => ['required', 'integer', 'exists:cities,id'],
                    'company_second_cap' => ['required', 'digits:5'],
                    'company_second_phone' => ['required', 'string', 'max:20'],
                    'company_second_note' => ['nullable', 'string'],
                ];
            }
        }

        $validated = Validator::make($request->all(), $rules)->validate();

        $user = DB::transaction(function () use ($request, $validated, $type) {
            $prefix = $type === '0' ? 'private' : 'company';
            $user = new User();
            $user->user_type = (int) $type;
            $user->role = 'user';
            $user->email = $validated['email'];
            $user->password = Hash::make($validated['password']);
            $user->name = $validated[$prefix.'_name'];
            $user->surname = $validated[$prefix.'_surname'];
            if ($type === '0') {
                $user->cf = $validated['private_cf'];
            } else {
                $user->company_society = $validated['company_society'];
                $user->company_cf = $validated['company_cf'];
                $user->company_pi = $validated['company_pi'];
                $user->company_sdi = $validated['company_sdi'] ?? null;
            }
            $user->save();

            $billing = new Address();
            $billing->user_id = $user->id;
            $billing->type = 'billing';
            $billing->address = $validated[$prefix.'_address'];
            $billing->city_id = $validated[$prefix.'_city_id'];
            $billing->cap = $validated[$prefix.'_cap'];
            $billing->phone = $validated[$prefix.'_phone'];
            $billing->note = $validated[$prefix.'_note'] ?? null;
            $billing->save();

            if ($request->boolean($prefix.'_check_second_address')) {
                $shipping = $billing->replicate();
                $shipping->type = 'shipping';
                $shipping->recipient_name = trim($user->name.' '.$user->surname);
            } else {
                $shipping = new Address();
                $shipping->user_id = $user->id;
                $shipping->type = 'shipping';
                $shipping->address = $validated[$prefix.'_second_address'];
                $shipping->city_id = $validated[$prefix.'_second_city_id'];
                $shipping->cap = $validated[$prefix.'_second_cap'];
                $shipping->phone = $validated[$prefix.'_second_phone'];
                $shipping->note = $validated[$prefix.'_second_note'] ?? null;
                $shipping->recipient_name = $type === '0'
                    ? $validated['private_delivery']
                    : $validated['company_second_society'];
            }
            $shipping->default = 1;
            $shipping->save();
            return $user;
        });

        // L'email è inviata dopo il commit: un problema di posta non annulla l'account.
        try {
            app(GmailService::class)->sendEmail(
                $user->email,
                'Registrazione Farmacia19',
                'emails.register',
                []
            );
        } catch (Throwable $e) {
            report($e);
        }

        // Genera il token per l'accesso automatico
        $plainToken = \Illuminate\Support\Str::random(80);

        \Illuminate\Support\Facades\DB::table('farmacia19_api_tokens')->insert([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plainToken),
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registrazione completata con successo!',
            'token' => $plainToken,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'email' => $user->email,
            ],
        ], 201);
    }
}
