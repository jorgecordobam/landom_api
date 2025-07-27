<?php

namespace App\Http\Controllers\Api\Investor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Inversion;
use App\Models\PropuestaInversion;
use App\Models\PerfilInversor;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InvestmentController extends Controller
{
    /**
     * Display a listing of investments
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Inversion::with(['propuesta.proyecto.propiedad', 'inversor.user']);

        // Filter by investor
        if ($request->has('inversor_id')) {
            $query->where('id_inversor', $request->inversor_id);
        }

        // Filter by status
        if ($request->has('estado_inversion')) {
            $query->where('estado_inversion', $request->estado_inversion);
        }

        // If user is investor, show only their investments
        if ($user->tipo_perfil === 'Inversor') {
            $perfilInversor = $user->perfilInversor;
            if ($perfilInversor) {
                $query->where('id_inversor', $perfilInversor->id_perfil_inversor);
            }
        }

        $investments = $query->paginate(15);

        return response()->json([
            'data' => $investments->items(),
            'pagination' => [
                'current_page' => $investments->currentPage(),
                'last_page' => $investments->lastPage(),
                'per_page' => $investments->perPage(),
                'total' => $investments->total(),
            ]
        ], 200);
    }

    /**
     * Store a newly created investment
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Check if user is an investor
        if ($user->tipo_perfil !== 'Inversor') {
            return response()->json([
                'message' => 'Solo los inversores pueden realizar inversiones'
            ], 403);
        }

        $request->validate([
            'id_propuesta' => 'required|exists:propuestas_inversion,id_propuesta',
            'monto_invertido' => 'required|numeric|min:0',
            'participacion_porcentaje_proyecto' => 'nullable|numeric|min:0|max:100',
        ]);

        $propuesta = PropuestaInversion::with('proyecto')->findOrFail($request->id_propuesta);

        // Check if proposal is active
        if ($propuesta->estado_propuesta !== 'Activa') {
            return response()->json([
                'message' => 'Esta propuesta de inversión no está activa'
            ], 400);
        }

        // Check if investment amount is valid
        if ($request->monto_invertido > $propuesta->monto_financiacion_requerido) {
            return response()->json([
                'message' => 'El monto de inversión no puede ser mayor al monto requerido'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $perfilInversor = $user->perfilInversor;
            if (!$perfilInversor) {
                return response()->json([
                    'message' => 'Debes completar tu perfil de inversor antes de realizar inversiones'
                ], 400);
            }

            $investment = Inversion::create([
                'id_propuesta' => $request->id_propuesta,
                'id_inversor' => $perfilInversor->id_perfil_inversor,
                'monto_invertido' => $request->monto_invertido,
                'participacion_porcentaje_proyecto' => $request->participacion_porcentaje_proyecto,
                'estado_inversion' => 'Pendiente',
            ]);

            // Update proposal status if fully funded
            $totalInvested = Inversion::where('id_propuesta', $request->id_propuesta)
                ->where('estado_inversion', '!=', 'Reembolsada')
                ->sum('monto_invertido');

            if ($totalInvested >= $propuesta->monto_financiacion_requerido) {
                $propuesta->update(['estado_propuesta' => 'Financiada']);
                $propuesta->proyecto->update(['estado_proyecto' => 'Financiado']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Inversión realizada exitosamente',
                'data' => $investment->load(['propuesta.proyecto.propiedad', 'inversor.user'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al realizar la inversión',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified investment
     */
    public function show($id)
    {
        $investment = Inversion::with([
            'propuesta.proyecto.propiedad',
            'inversor.user'
        ])->findOrFail($id);

        return response()->json([
            'data' => $investment
        ], 200);
    }

    /**
     * Update the specified investment
     */
    public function update(Request $request, $id)
    {
        $investment = Inversion::findOrFail($id);
        $user = $request->user();

        // Check if user owns the investment or is admin
        if ($investment->inversor->id_usuario !== $user->id_usuario && !$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para editar esta inversión'
            ], 403);
        }

        $request->validate([
            'estado_inversion' => 'sometimes|in:Pendiente,Confirmada,Reembolsada',
            'participacion_porcentaje_proyecto' => 'nullable|numeric|min:0|max:100',
        ]);

        $investment->update($request->all());

        return response()->json([
            'message' => 'Inversión actualizada exitosamente',
            'data' => $investment->load(['propuesta.proyecto.propiedad', 'inversor.user'])
        ], 200);
    }

    /**
     * Get investment opportunities (active proposals)
     */
    public function opportunities(Request $request)
    {
        $query = PropuestaInversion::with(['proyecto.propiedad', 'proyecto.gerenteProyecto'])
            ->where('estado_propuesta', 'Activa');

        // Filter by ROI
        if ($request->has('roi_min')) {
            $query->where('retorno_inversion_proyectado_porcentaje', '>=', $request->roi_min);
        }

        if ($request->has('roi_max')) {
            $query->where('retorno_inversion_proyectado_porcentaje', '<=', $request->roi_max);
        }

        // Filter by investment amount
        if ($request->has('monto_min')) {
            $query->where('monto_financiacion_requerido', '>=', $request->monto_min);
        }

        if ($request->has('monto_max')) {
            $query->where('monto_financiacion_requerido', '<=', $request->monto_max);
        }

        $opportunities = $query->paginate(15);

        return response()->json([
            'data' => $opportunities->items(),
            'pagination' => [
                'current_page' => $opportunities->currentPage(),
                'last_page' => $opportunities->lastPage(),
                'per_page' => $opportunities->perPage(),
                'total' => $opportunities->total(),
            ]
        ], 200);
    }

    /**
     * Get my investments (for authenticated investor)
     */
    public function myInvestments(Request $request)
    {
        $user = $request->user();

        if ($user->tipo_perfil !== 'Inversor') {
            return response()->json([
                'message' => 'Solo los inversores pueden ver sus inversiones'
            ], 403);
        }

        $perfilInversor = $user->perfilInversor;
        if (!$perfilInversor) {
            return response()->json([
                'data' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 15,
                    'total' => 0,
                ]
            ], 200);
        }

        $investments = Inversion::with(['propuesta.proyecto.propiedad', 'propuesta.proyecto.gerenteProyecto'])
            ->where('id_inversor', $perfilInversor->id_perfil_inversor)
            ->paginate(15);

        return response()->json([
            'data' => $investments->items(),
            'pagination' => [
                'current_page' => $investments->currentPage(),
                'last_page' => $investments->lastPage(),
                'per_page' => $investments->perPage(),
                'total' => $investments->total(),
            ]
        ], 200);
    }

    /**
     * Get investment statistics
     */
    public function statistics(Request $request)
    {
        $user = $request->user();

        if ($user->tipo_perfil !== 'Inversor') {
            return response()->json([
                'message' => 'Solo los inversores pueden ver estadísticas'
            ], 403);
        }

        $perfilInversor = $user->perfilInversor;
        if (!$perfilInversor) {
            return response()->json([
                'total_invested' => 0,
                'total_investments' => 0,
                'average_roi' => 0,
                'active_investments' => 0,
            ], 200);
        }

        $investments = Inversion::where('id_inversor', $perfilInversor->id_perfil_inversor);

        $totalInvested = $investments->sum('monto_invertido');
        $totalInvestments = $investments->count();
        $activeInvestments = $investments->where('estado_inversion', '!=', 'Reembolsada')->count();

        // Calculate average ROI
        $averageRoi = $investments->join('propuestas_inversion', 'inversiones.id_propuesta', '=', 'propuestas_inversion.id_propuesta')
            ->avg('retorno_inversion_proyectado_porcentaje');

        return response()->json([
            'total_invested' => $totalInvested,
            'total_investments' => $totalInvestments,
            'average_roi' => round($averageRoi, 2),
            'active_investments' => $activeInvestments,
        ], 200);
    }
}
