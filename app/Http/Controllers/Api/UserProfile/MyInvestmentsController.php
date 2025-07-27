<?php

namespace App\Http\Controllers\Api\UserProfile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use App\Models\Inversion;
use App\Models\PerfilInversor;

class MyInvestmentsController extends Controller
{
    /**
     * Display a listing of user's investments
     */
    public function index(Request $request)
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
                'total_return' => 0,
            ], 200);
        }

        $investments = Inversion::where('id_inversor', $perfilInversor->id_perfil_inversor);

        $totalInvested = $investments->sum('monto_invertido');
        $totalInvestments = $investments->count();
        $activeInvestments = $investments->where('estado_inversion', '!=', 'Reembolsada')->count();

        // Calculate average ROI
        $averageRoi = $investments->join('propuestas_inversion', 'inversiones.id_propuesta', '=', 'propuestas_inversion.id_propuesta')
            ->avg('retorno_inversion_proyectado_porcentaje');

        // Calculate total return (simplified)
        $totalReturn = $totalInvested * ($averageRoi / 100);

        return response()->json([
            'total_invested' => $totalInvested,
            'total_investments' => $totalInvestments,
            'average_roi' => round($averageRoi, 2),
            'active_investments' => $activeInvestments,
            'total_return' => round($totalReturn, 2),
        ], 200);
    }

    /**
     * Get investment history
     */
    public function history(Request $request)
    {
        $user = $request->user();

        if ($user->tipo_perfil !== 'Inversor') {
            return response()->json([
                'message' => 'Solo los inversores pueden ver historial'
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

        $query = Inversion::with(['propuesta.proyecto.propiedad', 'propuesta.proyecto.gerenteProyecto'])
            ->where('id_inversor', $perfilInversor->id_perfil_inversor);

        // Filter by status
        if ($request->has('estado')) {
            $query->where('estado_inversion', $request->estado);
        }

        // Filter by date range
        if ($request->has('fecha_inicio')) {
            $query->where('fecha_inversion', '>=', $request->fecha_inicio);
        }

        if ($request->has('fecha_fin')) {
            $query->where('fecha_inversion', '<=', $request->fecha_fin);
        }

        $investments = $query->orderBy('fecha_inversion', 'desc')->paginate(15);

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
     * Display the specified investment
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        if ($user->tipo_perfil !== 'Inversor') {
            return response()->json([
                'message' => 'Solo los inversores pueden ver inversiones'
            ], 403);
        }

        $perfilInversor = $user->perfilInversor;
        if (!$perfilInversor) {
            return response()->json([
                'message' => 'Perfil de inversor no encontrado'
            ], 404);
        }

        $investment = Inversion::with([
            'propuesta.proyecto.propiedad',
            'propuesta.proyecto.gerenteProyecto',
            'propuesta.proyecto.fases'
        ])
        ->where('id_inversion', $id)
        ->where('id_inversor', $perfilInversor->id_perfil_inversor)
        ->first();

        if (!$investment) {
            return response()->json([
                'message' => 'Inversión no encontrada'
            ], 404);
        }

        return response()->json([
            'data' => $investment
        ], 200);
    }

    /**
     * Get investment performance
     */
    public function performance(Request $request)
    {
        $user = $request->user();

        if ($user->tipo_perfil !== 'Inversor') {
            return response()->json([
                'message' => 'Solo los inversores pueden ver rendimiento'
            ], 403);
        }

        $perfilInversor = $user->perfilInversor;
        if (!$perfilInversor) {
            return response()->json([
                'performance' => []
            ], 200);
        }

        // Get monthly performance for the last 12 months
        $performance = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthStart = $month->startOfMonth();
            $monthEnd = $month->endOfMonth();

            $monthlyInvestments = Inversion::where('id_inversor', $perfilInversor->id_perfil_inversor)
                ->whereBetween('fecha_inversion', [$monthStart, $monthEnd])
                ->sum('monto_invertido');

            $performance[] = [
                'month' => $month->format('Y-m'),
                'month_name' => $month->format('M Y'),
                'amount_invested' => $monthlyInvestments,
                'investments_count' => Inversion::where('id_inversor', $perfilInversor->id_perfil_inversor)
                    ->whereBetween('fecha_inversion', [$monthStart, $monthEnd])
                    ->count(),
            ];
        }

        return response()->json([
            'performance' => $performance
        ], 200);
    }

    /**
     * Get investment portfolio
     */
    public function portfolio(Request $request)
    {
        $user = $request->user();

        if ($user->tipo_perfil !== 'Inversor') {
            return response()->json([
                'message' => 'Solo los inversores pueden ver portafolio'
            ], 403);
        }

        $perfilInversor = $user->perfilInversor;
        if (!$perfilInversor) {
            return response()->json([
                'portfolio' => []
            ], 200);
        }

        // Get portfolio by project type
        $portfolio = Inversion::with(['propuesta.proyecto.propiedad'])
            ->where('id_inversor', $perfilInversor->id_perfil_inversor)
            ->where('estado_inversion', '!=', 'Reembolsada')
            ->get()
            ->groupBy('propuesta.proyecto.propiedad.ciudad')
            ->map(function ($investments, $city) {
                return [
                    'ciudad' => $city,
                    'total_invertido' => $investments->sum('monto_invertido'),
                    'proyectos_count' => $investments->count(),
                    'promedio_roi' => $investments->avg('propuesta.retorno_inversion_proyectado_porcentaje'),
                ];
            })
            ->values();

        return response()->json([
            'portfolio' => $portfolio
        ], 200);
    }
}
