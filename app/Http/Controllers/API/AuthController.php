<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Registrazione dell'utente
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:50',
            'email' => 'required|email|max:100|unique:users',
            'password' => 'required|min:8|max:50',
        ], [
            'name.required' => 'Il campo nome è obbligatorio.',
            'name.max' => 'Il nome non può superare i 50 caratteri.',
            
            'email.required' => 'Il campo email è obbligatorio.',
            'email.email' => 'L\'email deve essere un indirizzo email valido.',
            'email.max' => 'L\'email non può superare i 100 caratteri.',
            'email.unique' => 'L\'email inserita è già registrata.',
            
            'password.required' => 'Il campo password è obbligatorio.',
            'password.min' => 'La password deve contenere almeno 8 caratteri.',
            'password.max' => 'La password non può superare i 50 caratteri.',
        ]);
        
        
    if ($validator->fails()) {
        // Personalizzazione dei messaggi di errore
        $errors = $validator->errors()->all();
        $formattedErrors = [];

        foreach ($errors as $error) {
            if (strpos($error, 'name') !== false) {
                $formattedErrors[] =  $error;
            } elseif (strpos($error, 'email') !== false) {
                $formattedErrors[] = $error;
            } elseif (strpos($error, 'password') !== false) {
                $formattedErrors[] =  $error;
            } else {
                $formattedErrors[] = $error;
            }
        }

        return response()->json(['errors' => $formattedErrors], 422);
    }
        

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'user' => $user,
            'message' => 'Registrazione avvenuta con successo!'
        ], 201);
    }

    // Login dell'utente
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:100',
            'password' => 'required|min:8|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Credenziali non valide.'], 401);
        }

        $user = Auth::user();
        return response()->json([
            'user' => $user,
            'message' => 'Login avvenuto con successo!'
        ]);
    }
}


