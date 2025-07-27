<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\PerfilInversor;
use App\Models\PerfilTrabajador;
use App\Models\PerfilConstructorContratista;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    /**
     * Handle user registration
     */
    public function register(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|string|min:8|confirmed',
            'telefono' => 'nullable|string|max:50',
            'tipo_perfil' => 'required|in:Inversor,Trabajador,ConstructorContratista,General',
        ]);

        try {
            DB::beginTransaction();

            // Create user
            $user = User::create([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'email' => $request->email,
                'contrasena_hash' => Hash::make($request->password),
                'telefono' => $request->telefono,
                'tipo_perfil' => $request->tipo_perfil,
                'estado_verificacion' => 'Pendiente',
            ]);

            // Create specific profile based on tipo_perfil
            switch ($request->tipo_perfil) {
                case 'Inversor':
                    PerfilInversor::create([
                        'id_usuario' => $user->id_usuario,
                    ]);
                    break;
                case 'Trabajador':
                    PerfilTrabajador::create([
                        'id_usuario' => $user->id_usuario,
                    ]);
                    break;
                case 'ConstructorContratista':
                    PerfilConstructorContratista::create([
                        'id_usuario' => $user->id_usuario,
                        'nombre_empresa' => $request->nombre_empresa ?? '',
                    ]);
                    break;
            }

            DB::commit();

            // Generate token
            $token = $user->createToken('API Token')->plainTextToken;

            return response()->json([
                'message' => 'Usuario registrado exitosamente',
                'user' => [
                    'id' => $user->id_usuario,
                    'nombre' => $user->nombre,
                    'apellido' => $user->apellido,
                    'email' => $user->email,
                    'tipo_perfil' => $user->tipo_perfil,
                    'estado_verificacion' => $user->estado_verificacion,
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al registrar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify user email
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        // Implementation for email verification
        // This would typically involve checking a verification token
        
        return response()->json([
            'message' => 'Email verificado exitosamente'
        ], 200);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'nombre' => 'sometimes|string|max:100',
            'apellido' => 'sometimes|string|max:100',
            'telefono' => 'sometimes|string|max:50',
        ]);

        $user->update($request->only(['nombre', 'apellido', 'telefono']));

        return response()->json([
            'message' => 'Perfil actualizado exitosamente',
            'user' => [
                'id' => $user->id_usuario,
                'nombre' => $user->nombre,
                'apellido' => $user->apellido,
                'email' => $user->email,
                'tipo_perfil' => $user->tipo_perfil,
                'estado_verificacion' => $user->estado_verificacion,
            ]
        ], 200);
    }
}
