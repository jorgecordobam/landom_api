<?php

namespace App\Http\Controllers\Api\PlatformAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use App\Models\PerfilInversor;
use App\Models\PerfilTrabajador;
use App\Models\PerfilConstructorContratista;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with(['perfilInversor', 'perfilTrabajador', 'perfilConstructorContratista']);

        // Filter by user type
        if ($request->has('tipo_perfil')) {
            $query->where('tipo_perfil', $request->tipo_perfil);
        }

        // Filter by verification status
        if ($request->has('estado_verificacion')) {
            $query->where('estado_verificacion', $request->estado_verificacion);
        }

        // Filter by date range
        if ($request->has('fecha_inicio')) {
            $query->where('fecha_registro', '>=', $request->fecha_inicio);
        }

        if ($request->has('fecha_fin')) {
            $query->where('fecha_registro', '<=', $request->fecha_fin);
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20);

        return response()->json([
            'data' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ]
        ], 200);
    }

    /**
     * Display the specified user
     */
    public function show($id)
    {
        $user = User::with([
            'perfilInversor',
            'perfilTrabajador', 
            'perfilConstructorContratista',
            'proyectos',
            'propiedades',
            'publicaciones'
        ])->findOrFail($id);

        return response()->json([
            'data' => $user
        ], 200);
    }

    /**
     * Update user status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'estado_verificacion' => 'required|in:Pendiente,En Revisión,Verificado,Rechazado',
            'comentario' => 'nullable|string|max:500',
        ]);

        $user = User::findOrFail($id);

        $user->update([
            'estado_verificacion' => $request->estado_verificacion
        ]);

        // Log the status change
        // You might want to create an audit log here

        return response()->json([
            'message' => 'Estado del usuario actualizado exitosamente',
            'data' => [
                'id' => $user->id_usuario,
                'nombre' => $user->nombre,
                'apellido' => $user->apellido,
                'email' => $user->email,
                'estado_verificacion' => $user->estado_verificacion,
            ]
        ], 200);
    }

    /**
     * Remove the specified user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Check if user has active projects or investments
        $activeProjects = $user->proyectos()->where('estado_proyecto', '!=', 'Completado')->count();
        $activeInvestments = 0;

        if ($user->tipo_perfil === 'Inversor' && $user->perfilInversor) {
            $activeInvestments = $user->perfilInversor->inversiones()
                ->where('estado_inversion', '!=', 'Reembolsada')
                ->count();
        }

        if ($activeProjects > 0 || $activeInvestments > 0) {
            return response()->json([
                'message' => 'No se puede eliminar un usuario con proyectos o inversiones activas'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado exitosamente'
        ], 200);
    }

    /**
     * Get user statistics
     */
    public function statistics(Request $request)
    {
        $statistics = [
            'total_users' => User::count(),
            'users_by_type' => [
                'Inversor' => User::where('tipo_perfil', 'Inversor')->count(),
                'Trabajador' => User::where('tipo_perfil', 'Trabajador')->count(),
                'ConstructorContratista' => User::where('tipo_perfil', 'ConstructorContratista')->count(),
                'General' => User::where('tipo_perfil', 'General')->count(),
            ],
            'users_by_status' => [
                'Pendiente' => User::where('estado_verificacion', 'Pendiente')->count(),
                'En Revisión' => User::where('estado_verificacion', 'En Revisión')->count(),
                'Verificado' => User::where('estado_verificacion', 'Verificado')->count(),
                'Rechazado' => User::where('estado_verificacion', 'Rechazado')->count(),
            ],
            'new_users_this_month' => User::where('fecha_registro', '>=', now()->startOfMonth())->count(),
            'new_users_this_week' => User::where('fecha_registro', '>=', now()->startOfWeek())->count(),
        ];

        return response()->json([
            'data' => $statistics
        ], 200);
    }

    /**
     * Get pending verifications
     */
    public function pendingVerifications(Request $request)
    {
        $query = User::with(['perfilInversor', 'perfilTrabajador', 'perfilConstructorContratista'])
            ->whereIn('estado_verificacion', ['Pendiente', 'En Revisión']);

        // Filter by user type
        if ($request->has('tipo_perfil')) {
            $query->where('tipo_perfil', $request->tipo_perfil);
        }

        $users = $query->paginate(20);

        return response()->json([
            'data' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ]
        ], 200);
    }

    /**
     * Bulk update user status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:usuarios,id_usuario',
            'estado_verificacion' => 'required|in:Pendiente,En Revisión,Verificado,Rechazado',
            'comentario' => 'nullable|string|max:500',
        ]);

        $updatedCount = User::whereIn('id_usuario', $request->user_ids)
            ->update(['estado_verificacion' => $request->estado_verificacion]);

        return response()->json([
            'message' => "Se actualizaron {$updatedCount} usuarios exitosamente",
            'updated_count' => $updatedCount
        ], 200);
    }

    /**
     * Get user activity
     */
    public function userActivity(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $activity = [
            'projects_created' => $user->proyectos()->count(),
            'properties_registered' => $user->propiedades()->count(),
            'publications_created' => $user->publicaciones()->count(),
            'last_login' => $user->last_login_at ?? 'Nunca',
            'registration_date' => $user->fecha_registro,
        ];

        // Add investment activity if user is investor
        if ($user->tipo_perfil === 'Inversor' && $user->perfilInversor) {
            $activity['total_investments'] = $user->perfilInversor->inversiones()->count();
            $activity['total_invested'] = $user->perfilInversor->inversiones()->sum('monto_invertido');
        }

        return response()->json([
            'data' => $activity
        ], 200);
    }
}
