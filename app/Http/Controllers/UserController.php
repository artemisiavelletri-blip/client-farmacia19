<?php

namespace App\Http\Controllers;

use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


use App\Models\User;
use App\Models\Address;
use App\Models\PaymentMethod;

use App\Mail\ResetPasswordMail;
use App\Mail\RegistrazioneMail;

class UserController extends Controller
{
    public function register(Request $request)
    {

        $rules = [
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/',
            'user_type'=> 'required|in:0,1',
            'terms_service' => 'accepted',
        ];

        // PRIVATO
        if ($request->user_type == 0) {
            $rules = array_merge($rules, [
                'private_name'       => 'required|string|max:255',
                'private_surname'    => 'required|string|max:255',
                'private_cf'         => 'required|string|size:16|regex:/^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/i',
                'private_address'    => 'required|string|max:255',
                'private_city_id'    => 'required|integer',
                'private_cap'        => 'required|digits:5',
                'private_phone'      => 'required|string|max:20',
            ]);

            // indirizzo di spedizione diverso
            if (!$request->boolean('private_check_second_address')) {
                $rules = array_merge($rules, [
                    'private_delivery'        => 'required|string|max:255',
                    'private_second_address'  => 'required|string|max:255',
                    'private_second_city_id'  => 'required|integer',
                    'private_second_cap'      => 'required|digits:5',
                    'private_second_phone'    => 'required|string|max:20',
                ]);
            }
        }

        // AZIENDA
        if ($request->user_type == 1) {
            $rules = array_merge($rules, [
                'company_society'  => 'required|string|max:255',
                'company_name'     => 'required|string|max:255',
                'company_surname'  => 'required|string|max:255',
                'company_cf'       => 'required|string',
                'company_pi'       => 'required|string|max:11',
                'company_address'  => 'required|string|max:255',
                'company_city_id'  => 'required|integer',
                'company_cap'      => 'required|digits:5',
            ]);

            if (!$request->boolean('company_check_second_address')) {
                $rules = array_merge($rules, [
                    'company_second_society' => 'required|string|max:255',
                    'company_second_address' => 'required|string|max:255',
                    'company_second_city_id' => 'required|integer',
                    'company_second_cap'     => 'required|digits:5',
                    'company_second_phone'   => 'required|string|max:20',
                ]);
            }
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        // 2️⃣ Creazione utente
        $user = new User();
        $user->user_type = $request->user_type;
        $user->email = $request->email;
        $user->password = Hash::make($request->password); // 🔒 criptata
        $user->prescriber_id = session('prescriber_id');

        if ($request->user_type == 0) {
            // Privato
            $user->name = $request->private_name;
            $user->surname = $request->private_surname;
            $user->cf = $request->private_cf;
        } else {
            // Azienda
            $user->company_society = $request->company_society;
            $user->name = $request->company_name;
            $user->surname = $request->company_surname;
            $user->company_cf = $request->company_cf;
            $user->company_pi = $request->company_pi;
            $user->company_sdi = $request->company_sdi;
        }

        $user->save();

        $addressBilling = new Address();
        $addressBilling->user_id = $user->id;
        $addressBilling->type = 'billing';

        if ($request->user_type == 0) {
            $addressBilling->address = $request->private_address;
            $addressBilling->cap = $request->private_cap;
            $addressBilling->city_id = $request->private_city_id;
            $addressBilling->note = !empty($request->private_note) ? $request->private_note : null;
            $addressBilling->phone = $request->private_phone;
            $addressBilling->save();

            if (!$request->boolean('private_check_second_address')) {
                $addressShipping = new Address();
                $addressShipping->user_id = $user->id;
                $addressShipping->type = 'shipping';
                $addressShipping->address = $request->private_second_address;
                $addressShipping->cap = $request->private_second_cap;
                $addressShipping->city_id = $request->private_second_city_id;
                $addressShipping->recipient_name = $request->private_delivery;
                $addressShipping->phone = $request->private_second_phone;
                $addressShipping->note = !empty($request->private_second_note) ? $request->private_second_note : null;
                $addressShipping->save();
            } else {
                $newItem = $addressBilling->replicate();
                $newItem->type = 'shipping';
                $newItem->recipient_name = ($user->name . ' '. $user->surname);
                $newItem->save();
            }

        } else {
            $addressBilling->address = $request->company_address;
            $addressBilling->cap = $request->company_cap;
            $addressBilling->city_id = $request->company_city_id;
            $addressBilling->phone = $request->company_phone;
            $addressBilling->note = !empty($request->company_note) ? $request->company_note : null;
            $addressBilling->note = $request->company_note;
            $addressBilling->save();

            if (!$request->boolean('company_check_second_address')) {
                $addressShipping = new Address();
                $addressShipping->user_id = $user->id;
                $addressShipping->type = 'shipping';
                $addressShipping->address = $request->company_second_address;
                $addressShipping->cap = $request->company_second_cap;
                $addressShipping->city_id = $request->company_second_city_id;
                $addressShipping->recipient_name = $request->company_second_society ? $request->company_second_society : ($user->name . ' '. $user->surname);
                $addressShipping->phone = $request->company_second_phone;
                $addressShipping->note = !empty($request->company_second_note) ? $request->company_second_note : null;
                $addressShipping->save();
            } else {
                $newItem = $addressBilling->replicate();
                $newItem->type = 'shipping';
                $newItem->recipient_name = ($user->name . ' '. $user->surname);
                $newItem->save();
            }
        }

        Mail::to($user->email)->send(new RegistrazioneMail());

        return redirect()->route('login')->with('success', 'Registrazione completata con successo!');
    }

    public function login(Request $request)
    {
        // Validazione
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'Inserisci la tua email.',
            'email.email' => 'Inserisci un indirizzo email valido.',
            'password.required' => 'Inserisci la password.',
            'password.min' => 'La password deve contenere almeno 6 caratteri.',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            if (Auth::user()->role === 'admin') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Email o password non corretti.',
                ])->withInput();
            }
            $request->session()->regenerate();

            // Redirect in base al tipo di utente
            $user = Auth::user();
            $prescriberId = session('prescriber_id');
            if ($prescriberId) {
                $user->prescriber_id = $prescriberId;
                $user->save();
            }
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Email o password non corretti.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function edit_user_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'surname' => 'required',
            'email' => 'required|email',
        ], [
            'name.required' => 'Il campo nome è obbligatorio',
            'surname.required' => 'Il campo cognome è obbligatorio',
            'email.required' => 'Il campo email è obbligatorio',
            'email.email' => 'Inserisci un indirizzo email valido.'
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        $user->name = $request['name'];
        $user->surname = $request['surname'];
        $user->email = $request['email'];
        $user->save();

        return redirect()->back()->with('success', 'I dati sono stati aggiornati con successo!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/|confirmed'
        ], [
            'old_password.required' => 'Inserisci la vecchia password',
            'new_password.required' => 'Inserisci la nuova password',
            'new_password.min' => 'La password deve avere almeno 8 caratteri',
            'new_password.regex' => 'La password deve contenere almeno una lettera maiuscola, una minuscola, un numero ed un carattere speciale',
            'new_password.confirmed' => 'Le password non coincidono'
        ]);

        $user = Auth::user();

        // controllo vecchia password
        if (!Hash::check($request->old_password, $user->password)) {
            return back()->withErrors([
                'old_password' => 'La vecchia password non è corretta'
            ]);
        }

        // aggiorna password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password aggiornata con successo!');
    }

    public function create_stripe_consumer()
    {
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        $customer = $stripe->customers->create([
          'name' => Auth::user()->name . ' ' . Auth::user()->surname,
          'email' => Auth::user()->email,
        ]);
    }

    public function payment_method(Request $request)
    {
        $payment_method = PaymentMethod::where('user_id', Auth::user()->id)->select(['brand','last4','exp_month','exp_year','stripe_payment_method_id','holder_name'])->get();
        return view('auth.payment-method', [
            'payment_method' => $payment_method
        ]);
    }

    public function billing_address(Request $request)
    {
        $billing_address = Auth::user()->billingAddresses()->first();
        return view('auth.billing-address', [
            'billing_address' => $billing_address
        ]);
    }

    public function edit_billing_address(Request $request)
    {;
        return view('auth.edit-address', [
            'edit' => 0
        ]);
    }

    public function edit_billing_address_private(Request $request)
    {
        if (Auth::user()->user_type == 0) {
            $rules = [
                'private_name'       => 'required|string|max:255',
                'private_surname'    => 'required|string|max:255',
                'private_cf'         => 'required|string|size:16|regex:/^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/i',
                'private_address'    => 'required|string|max:255',
                'private_city_id'    => 'required|integer',
                'private_cap'        => 'required|digits:5',
                'private_phone'      => 'required|string|max:20',
            ];
        } else {
            $rules = [
                'company_society'  => 'required|string|max:255',
                'company_name'     => 'required|string|max:255',
                'company_surname'  => 'required|string|max:255',
                'company_cf'       => 'required|string',
                'company_pi'       => 'required|string|max:11',
                'company_address'  => 'required|string|max:255',
                'company_city_id'  => 'required|integer',
                'company_cap'      => 'required|digits:5',
                'company_phone'    => 'required|string|max:20',
            ];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        if (Auth::user()->user_type == 0) {
            $updateUser = User::findOrFail(Auth::user()->id);
            $updateUser->name = $request->private_name;
            $updateUser->surname = $request->private_surname;
            $updateUser->cf = $request->private_cf;

            $updateUser->save();

            $updateBilling = Address::findOrFail(Auth::user()->billingAddresses()->first()->id);
            $updateBilling->address = $request->private_address;
            $updateBilling->city_id = $request->private_city_id;
            $updateBilling->cap = $request->private_cap;
            $updateBilling->phone = $request->private_phone;
            $updateBilling->note = $request->private_note;

            $updateBilling->save();
        } else {
            $updateUser = User::findOrFail(Auth::user()->id);
            $updateUser->company_society = $request->company_society;
            $updateUser->name = $request->company_name;
            $updateUser->surname = $request->company_surname;
            $updateUser->company_cf = $request->company_cf;
            $updateUser->company_pi = $request->company_pi;
            $updateUser->company_sdi = $request->company_sdi;
            $updateUser->company_pec = $request->company_pec;

            $updateUser->save();

            $updateBilling = Address::findOrFail(Auth::user()->billingAddresses()->first()->id);
            $updateBilling->address = $request->company_address;
            $updateBilling->city_id = $request->company_city_id;
            $updateBilling->cap = $request->company_cap;
            $updateBilling->phone = $request->company_phone;
            $updateBilling->note = $request->company_note;

            $updateBilling->save();
        }

        return redirect()->route('settingsbilling_address')->with('success', 'Dati aggiornati con successo!');
    }

    public function shipping_address()
    {
        $shipping_address = Auth::user()->shippingAddresses()->first();
        return view('auth.shipping-address', [
            'shipping_address' => $shipping_address
        ]);
    }

    public function edit_shipping_address()
    {
        $shipping_address = Auth::user()->shippingAddresses()->first();
        return view('auth.edit-shipping-address', [
            'shipping_address' => $shipping_address
        ]);
    }

    public function edit_shipping_address_post(Request $request)
    {
        if (Auth::user()->user_id == 0) {
            $rules = [
                'recipient_name'       => 'required|string|max:255',
                'address'    => 'required|string|max:255',
                'city_id'    => 'required|integer',
                'cap'        => 'required|digits:5',
                'phone'      => 'required|string|max:20',
            ];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $updateShipping = Address::findOrFail(Auth::user()->shippingAddresses()->first()->id);
        $updateShipping->address = $request->address;
        $updateShipping->city_id = $request->city_id;
        $updateShipping->cap = $request->cap;
        $updateShipping->phone = $request->phone;
        $updateShipping->note = $request->note;

        $updateShipping->save();

        return redirect()->route('settingsshipping_address')->with('success', 'Dati aggiornati con successo!');
    }

    public function sendResetEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ], [
            'email.required' => 'Inserisci la tua email',
            'email.email' => 'Inserisci un indirizzo email valido'
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $token = Str::random(64);

            DB::table('password_resets')->updateOrInsert(
                ['email' => $user->email],
                ['token' => $token, 'created_at' => Carbon::now()]
            );

            Mail::to($user->email)->send(new ResetPasswordMail($user, $token));
        }

        // Messaggio generico, sempre mostrato
        return back()->with('success', 'Se l’email esiste nel nostro sistema, ti invieremo un link per reimpostare la password');
    }

    public function resetToken(Request $request,$token)
    {

        $email = $request->query('email');

        $record = DB::table('password_resets')
                    ->where('email', $email)
                    ->where('token', $token)
                    ->first();

        if (!$record) {
            return redirect('/login')->withErrors(['token' => 'Token non valido o scaduto.']);
        }


        return view('auth.reset_password', [
            'token' => $token,
            'email' => $email
        ]);
    }

    public function resetPassword(Request $request)
    {
        // Validazione
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/|confirmed',
        ], [
            'new_password.required' => 'Inserisci la nuova password',
            'new_password.min' => 'La password deve avere almeno 8 caratteri',
            'new_password.regex' => 'La password deve contenere almeno una lettera maiuscola, una minuscola, un numero ed un carattere speciale',
            'new_password.confirmed' => 'Le password non coincidono'
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $record = DB::table('password_resets')
                    ->where('email', $request['email'])
                    ->where('token', $request['token'])
                    ->first();

        if (!$record) {
            return redirect('/login')->withErrors(['token' => 'Token non valido o scaduto.']);
        }

        $user = User::where('email',$request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect('/login')->with('success', 'Password modificata con successo');

    }

    public function delete_account()
    {
        $user = Auth::user();
        $user->email = 'deleted_' . uniqid() . '_' . $user->email;
        $user->save();
        $user->delete();

        return redirect('/login')->with('success', 'Account eliminato con successo');
    }
}
