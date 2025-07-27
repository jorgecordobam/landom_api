<?php

namespace App\Http\Controllers\Api\UserProfile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the authenticated user's profile
     */
    public function show(Request $request)
    {
        $user = $request->user();
        
        $profileData = [
            'id' => $user->id_usuario,
            'nombre' => $user->nombre,
            'apellido' => $user->apellido,
            'email' => $user->email,
            'telefono' => $user->telefono,
            'tipo_perfil' => $user->tipo_perfil,
            'estado_verificacion' => $user->estado_verificacion,
            'fecha_registro' => $user->fecha_registro,
        ];

        // Add specific profile data based on user type
        switch ($user->tipo_perfil) {
            case 'Inversor':
                if ($user->perfilInversor) {
                    $profileData['perfil_inversor'] = [
                        'es_acreditado' => $user->perfilInversor->es_acreditado,
                        'documentos_completados' => $this->getDocumentosCompletados($user->perfilInversor),
                    ];
                }
                break;
            case 'Trabajador':
                if ($user->perfilTrabajador) {
                    $profileData['perfil_trabajador'] = [
                        'disponibilidad_actual' => $user->perfilTrabajador->disponibilidad_actual,
                        'experiencia_laboral' => $user->perfilTrabajador->experiencia_laboral,
                    ];
                }
                break;
            case 'ConstructorContratista':
                if ($user->perfilConstructorContratista) {
                    $profileData['perfil_constructor'] = [
                        'nombre_empresa' => $user->perfilConstructorContratista->nombre_empresa,
                        'nit_o_registro_empresa' => $user->perfilConstructorContratista->nit_o_registro_empresa,
                    ];
                }
                break;
        }

        return response()->json([
            'data' => $profileData
        ], 200);
    }

    /**
     * Update the authenticated user's profile
     */
    public function update(Request $request)
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
            'data' => [
                'id' => $user->id_usuario,
                'nombre' => $user->nombre,
                'apellido' => $user->apellido,
                'email' => $user->email,
                'telefono' => $user->telefono,
                'tipo_perfil' => $user->tipo_perfil,
                'estado_verificacion' => $user->estado_verificacion,
            ]
        ], 200);
    }

    /**
     * Update user avatar
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $user = $request->user();

        // Delete old avatar if exists
        if ($user->avatar_url) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        // Store new avatar
        $avatarPath = $request->file('avatar')->store('avatars', 'public');
        
        $user->update(['avatar_url' => $avatarPath]);

        return response()->json([
            'message' => 'Avatar actualizado exitosamente',
            'avatar_url' => Storage::url($avatarPath)
        ], 200);
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
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
            'contrasena_hash' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'Contraseña actualizada exitosamente'
        ], 200);
    }

    /**
     * Get user statistics
     */
    public function statistics(Request $request)
    {
        $user = $request->user();
        
        $statistics = [
            'total_proyectos' => 0,
            'total_inversiones' => 0,
            'total_propiedades' => 0,
            'total_publicaciones' => 0,
        ];

        // Count user's projects
        $statistics['total_proyectos'] = $user->proyectos()->count();

        // Count user's properties
        $statistics['total_propiedades'] = $user->propiedades()->count();

        // Count user's publications
        $statistics['total_publicaciones'] = $user->publicaciones()->count();

        // Count investments if user is investor
        if ($user->tipo_perfil === 'Inversor' && $user->perfilInversor) {
            $statistics['total_inversiones'] = $user->perfilInversor->inversiones()->count();
        }

        return response()->json([
            'data' => $statistics
        ], 200);
    }

    /**
     * Get documentos completados for investor profile
     */
    private function getDocumentosCompletados($perfilInversor)
    {
        $documentos = [
            'id_oficial' => !empty($perfilInversor->url_id_oficial),
            'prueba_fondos' => !empty($perfilInversor->url_prueba_fondos),
            'formulario_tributario' => !empty($perfilInversor->url_formulario_tributario),
            'contrato_inversion_marco' => !empty($perfilInversor->url_contrato_inversion_marco),
            'perfil_riesgo' => !empty($perfilInversor->url_perfil_riesgo),
            'verificacion_direccion' => !empty($perfilInversor->url_verificacion_direccion),
        ];

        return $documentos;
    }
}
