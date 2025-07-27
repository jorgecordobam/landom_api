<?php

namespace App\Http\Controllers\Api\ProjectManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Propiedad;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PropertyController extends Controller
{
    /**
     * Display a listing of properties
     */
    public function index(Request $request)
    {
        $query = Propiedad::with('propietarioRegistrador');

        // Filter by user if specified
        if ($request->has('user_id')) {
            $query->where('id_propietario_registrador', $request->user_id);
        }

        // Filter by city
        if ($request->has('ciudad')) {
            $query->where('ciudad', 'like', '%' . $request->ciudad . '%');
        }

        // Filter by state
        if ($request->has('estado')) {
            $query->where('estado', 'like', '%' . $request->estado . '%');
        }

        $properties = $query->paginate(15);

        return response()->json([
            'data' => $properties->items(),
            'pagination' => [
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
            ]
        ], 200);
    }

    /**
     * Store a newly created property
     */
    public function store(Request $request)
    {
        $request->validate([
            'direccion' => 'required|string|max:255',
            'ciudad' => 'required|string|max:100',
            'estado' => 'nullable|string|max:100',
            'codigo_postal' => 'nullable|string|max:20',
            'descripcion_corta' => 'nullable|string',
            'metros_cuadrados_construccion' => 'nullable|numeric|min:0',
            'metros_cuadrados_terreno' => 'nullable|numeric|min:0',
            'numero_habitaciones' => 'nullable|integer|min:0',
            'numero_banos' => 'nullable|numeric|min:0',
            'urls_fotos_actuales' => 'nullable|array',
            'urls_planos' => 'nullable|array',
            'estado_actual_descripcion' => 'nullable|string',
        ]);

        $user = $request->user();

        $property = Propiedad::create([
            'direccion' => $request->direccion,
            'ciudad' => $request->ciudad,
            'estado' => $request->estado,
            'codigo_postal' => $request->codigo_postal,
            'descripcion_corta' => $request->descripcion_corta,
            'metros_cuadrados_construccion' => $request->metros_cuadrados_construccion,
            'metros_cuadrados_terreno' => $request->metros_cuadrados_terreno,
            'numero_habitaciones' => $request->numero_habitaciones,
            'numero_banos' => $request->numero_banos,
            'urls_fotos_actuales' => $request->urls_fotos_actuales,
            'urls_planos' => $request->urls_planos,
            'estado_actual_descripcion' => $request->estado_actual_descripcion,
            'id_propietario_registrador' => $user->id_usuario,
        ]);

        return response()->json([
            'message' => 'Propiedad creada exitosamente',
            'data' => $property->load('propietarioRegistrador')
        ], 201);
    }

    /**
     * Display the specified property
     */
    public function show($id)
    {
        $property = Propiedad::with(['propietarioRegistrador', 'proyecto'])->findOrFail($id);

        return response()->json([
            'data' => $property
        ], 200);
    }

    /**
     * Update the specified property
     */
    public function update(Request $request, $id)
    {
        $property = Propiedad::findOrFail($id);
        $user = $request->user();

        // Check if user owns the property or is admin
        if ($property->id_propietario_registrador !== $user->id_usuario && !$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para editar esta propiedad'
            ], 403);
        }

        $request->validate([
            'direccion' => 'sometimes|string|max:255',
            'ciudad' => 'sometimes|string|max:100',
            'estado' => 'nullable|string|max:100',
            'codigo_postal' => 'nullable|string|max:20',
            'descripcion_corta' => 'nullable|string',
            'metros_cuadrados_construccion' => 'nullable|numeric|min:0',
            'metros_cuadrados_terreno' => 'nullable|numeric|min:0',
            'numero_habitaciones' => 'nullable|integer|min:0',
            'numero_banos' => 'nullable|numeric|min:0',
            'urls_fotos_actuales' => 'nullable|array',
            'urls_planos' => 'nullable|array',
            'estado_actual_descripcion' => 'nullable|string',
        ]);

        $property->update($request->all());

        return response()->json([
            'message' => 'Propiedad actualizada exitosamente',
            'data' => $property->load('propietarioRegistrador')
        ], 200);
    }

    /**
     * Remove the specified property
     */
    public function destroy(Request $request, $id)
    {
        $property = Propiedad::findOrFail($id);
        $user = $request->user();

        // Check if user owns the property or is admin
        if ($property->id_propietario_registrador !== $user->id_usuario && !$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para eliminar esta propiedad'
            ], 403);
        }

        // Check if property has an active project
        if ($property->proyecto) {
            return response()->json([
                'message' => 'No se puede eliminar una propiedad que tiene un proyecto activo'
            ], 400);
        }

        $property->delete();

        return response()->json([
            'message' => 'Propiedad eliminada exitosamente'
        ], 200);
    }

    /**
     * Get properties by user
     */
    public function myProperties(Request $request)
    {
        $user = $request->user();
        
        $properties = Propiedad::with(['propietarioRegistrador', 'proyecto'])
            ->where('id_propietario_registrador', $user->id_usuario)
            ->paginate(15);

        return response()->json([
            'data' => $properties->items(),
            'pagination' => [
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
            ]
        ], 200);
    }
} 