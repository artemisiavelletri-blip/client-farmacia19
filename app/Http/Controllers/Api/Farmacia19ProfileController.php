<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class Farmacia19ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->name = $data['name'];
        $user->surname = $data['surname'];
        $user->email = $data['email'];
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profilo aggiornato correttamente.',
            'user' => $user->only(['id', 'name', 'surname', 'email']),
        ]);
    }

    public function password(Request $request)
    {
        $data = $request->validate([
            'old_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/', 'confirmed'],
        ]);
        $user = $request->user();
        if (!Hash::check($data['old_password'], $user->password)) {
            return response()->json(['success' => false, 'message' => 'La vecchia password non è corretta.'], 422);
        }
        $user->password = Hash::make($data['new_password']);
        $user->save();
        return response()->json(['success' => true, 'message' => 'Password aggiornata correttamente.']);
    }

    public function delete(Request $request)
    {
        $data = $request->validate(['password' => ['required', 'string']]);
        $user = $request->user();
        if (!Hash::check($data['password'], $user->password)) {
            return response()->json(['success' => false, 'message' => 'Password non corretta.'], 422);
        }
        DB::transaction(function () use ($user) {
            // Revoca tutti i token prima di eliminare l'account.
            DB::table('farmacia19_api_tokens')->where('user_id', $user->id)->delete();
            $user->email = 'deleted_' . uniqid() . '_' . $user->email;
            $user->save();
            $user->delete();
        });
        return response()->json(['success' => true, 'message' => 'Account eliminato correttamente.']);
    }
}
