<?php

namespace App\Http\Controllers\Api\ProjectManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Project;
use App\Models\Propiedad;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects
     */
    public function index(Request $request)
    {
        $query = Project::with(['propiedad', 'gerenteProyecto']);

        // Filter by status
        if ($request->has('estado_proyecto')) {
            $query->where('estado_proyecto', $request->estado_proyecto);
        }

        // Filter by manager
        if ($request->has('gerente_id')) {
            $query->where('id_gerente_proyecto', $request->gerente_id);
        }

        // Filter by property
        if ($request->has('propiedad_id')) {
            $query->where('id_propiedad', $request->propiedad_id);
        }

        $projects = $query->paginate(15);

        return response()->json([
            'data' => $projects->items(),
            'pagination' => [
                'current_page' => $projects->currentPage(),
                'last_page' => $projects->lastPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
            ]
        ], 200);
    }

    /**
     * Store a newly created project
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_propiedad' => 'required|exists:propiedades,id_propiedad',
            'nombre_proyecto' => 'required|string|max:255',
            'descripcion_detallada' => 'nullable|string',
            'presupuesto_estimado_total' => 'nullable|numeric|min:0',
            'roi_estimado_porcentaje' => 'nullable|numeric|min:0|max:100',
            'fecha_inicio_estimada' => 'nullable|date',
            'fecha_fin_estimada' => 'nullable|date|after:fecha_inicio_estimada',
        ]);

        $user = $request->user();

        // Check if property already has a project
        $existingProject = Project::where('id_propiedad', $request->id_propiedad)->first();
        if ($existingProject) {
            return response()->json([
                'message' => 'Esta propiedad ya tiene un proyecto asociado'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $project = Project::create([
                'id_propiedad' => $request->id_propiedad,
                'nombre_proyecto' => $request->nombre_proyecto,
                'id_gerente_proyecto' => $user->id_usuario,
                'descripcion_detallada' => $request->descripcion_detallada,
                'presupuesto_estimado_total' => $request->presupuesto_estimado_total,
                'roi_estimado_porcentaje' => $request->roi_estimado_porcentaje,
                'fecha_inicio_estimada' => $request->fecha_inicio_estimada,
                'fecha_fin_estimada' => $request->fecha_fin_estimada,
                'estado_proyecto' => 'Borrador',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Proyecto creado exitosamente',
                'data' => $project->load(['propiedad', 'gerenteProyecto'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear el proyecto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified project
     */
    public function show($id)
    {
        $project = Project::with([
            'propiedad',
            'gerenteProyecto',
            'fases',
            'participantes.usuario',
            'propuestaInversion'
        ])->findOrFail($id);

        return response()->json([
            'data' => $project
        ], 200);
    }

    /**
     * Update the specified project
     */
    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $user = $request->user();

        // Check if user is project manager or admin
        if ($project->id_gerente_proyecto !== $user->id_usuario && !$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para editar este proyecto'
            ], 403);
        }

        $request->validate([
            'nombre_proyecto' => 'sometimes|string|max:255',
            'descripcion_detallada' => 'nullable|string',
            'presupuesto_estimado_total' => 'nullable|numeric|min:0',
            'roi_estimado_porcentaje' => 'nullable|numeric|min:0|max:100',
            'fecha_inicio_estimada' => 'nullable|date',
            'fecha_fin_estimada' => 'nullable|date|after:fecha_inicio_estimada',
            'fecha_inicio_real' => 'nullable|date',
            'fecha_fin_real' => 'nullable|date|after:fecha_inicio_real',
            'estado_proyecto' => 'sometimes|in:Borrador,Propuesta Abierta,Financiado,En Ejecución,En Pausa,Completado,Cancelado',
        ]);

        $project->update($request->all());

        return response()->json([
            'message' => 'Proyecto actualizado exitosamente',
            'data' => $project->load(['propiedad', 'gerenteProyecto'])
        ], 200);
    }

    /**
     * Remove the specified project
     */
    public function destroy(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $user = $request->user();

        // Check if user is project manager or admin
        if ($project->id_gerente_proyecto !== $user->id_usuario && !$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para eliminar este proyecto'
            ], 403);
        }

        // Check if project can be deleted
        if ($project->estado_proyecto !== 'Borrador') {
            return response()->json([
                'message' => 'Solo se pueden eliminar proyectos en estado Borrador'
            ], 400);
        }

        $project->delete();

        return response()->json([
            'message' => 'Proyecto eliminado exitosamente'
        ], 200);
    }

    /**
     * Get projects by user (as manager)
     */
    public function myProjects(Request $request)
    {
        $user = $request->user();
        
        $projects = Project::with(['propiedad', 'gerenteProyecto'])
            ->where('id_gerente_proyecto', $user->id_usuario)
            ->paginate(15);

        return response()->json([
            'data' => $projects->items(),
            'pagination' => [
                'current_page' => $projects->currentPage(),
                'last_page' => $projects->lastPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
            ]
        ], 200);
    }

    /**
     * Get projects where user is participant
     */
    public function participatedProjects(Request $request)
    {
        $user = $request->user();
        
        $projects = Project::with(['propiedad', 'gerenteProyecto'])
            ->whereHas('participantes', function($query) use ($user) {
                $query->where('id_usuario', $user->id_usuario);
            })
            ->paginate(15);

        return response()->json([
            'data' => $projects->items(),
            'pagination' => [
                'current_page' => $projects->currentPage(),
                'last_page' => $projects->lastPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
            ]
        ], 200);
    }

    /**
     * Update project status
     */
    public function updateStatus(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $user = $request->user();

        // Check if user is project manager or admin
        if ($project->id_gerente_proyecto !== $user->id_usuario && !$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para cambiar el estado de este proyecto'
            ], 403);
        }

        $request->validate([
            'estado_proyecto' => 'required|in:Borrador,Propuesta Abierta,Financiado,En Ejecución,En Pausa,Completado,Cancelado',
        ]);

        $project->update([
            'estado_proyecto' => $request->estado_proyecto
        ]);

        return response()->json([
            'message' => 'Estado del proyecto actualizado exitosamente',
            'data' => $project->load(['propiedad', 'gerenteProyecto'])
        ], 200);
    }
}
