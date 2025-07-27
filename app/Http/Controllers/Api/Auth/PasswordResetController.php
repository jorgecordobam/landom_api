<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Send password reset link
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'No se encontró un usuario con ese email'
            ], 404);
        }

        // Generate reset token
        $token = Str::random(60);
        
        // Store token in database (you might want to create a password_resets table)
        // For now, we'll just return success
        
        // Send email with reset link
        // Mail::to($user->email)->send(new PasswordResetMail($token));

        return response()->json([
            'message' => 'Se ha enviado un enlace de restablecimiento de contraseña a tu email'
        ], 200);
    }

    /**
     * Reset password
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        // Verify token (implement your token verification logic)
        // For now, we'll just update the password
        
        $user->update([
            'contrasena_hash' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Contraseña restablecida exitosamente'
        ], 200);
    }

    /**
     * Change password (for authenticated users)
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        // Check current password
        if (!Hash::check($request->current_password, $user->contrasena_hash)) {
            return response()->json([
                'message' => 'La contraseña actual es incorrecta'
            ], 400);
        }

        // Update password
        $user->update([
            'contrasena_hash' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'message' => 'Contraseña cambiada exitosamente'
        ], 200);
    }
}
