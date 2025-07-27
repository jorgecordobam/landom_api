<?php

namespace App\Http\Controllers\Api\ProjectManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Task;
use App\Models\FaseProyecto;
use App\Models\User;
use App\Models\Project;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks for a project
     */
    public function index(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        
        $query = Task::with(['fase', 'responsable'])
            ->whereHas('fase', function($q) use ($projectId) {
                $q->where('id_proyecto', $projectId);
            });

        // Filter by status
        if ($request->has('estado_tarea')) {
            $query->where('estado_tarea', $request->estado_tarea);
        }

        // Filter by phase
        if ($request->has('fase_id')) {
            $query->where('id_fase', $request->fase_id);
        }

        // Filter by responsible
        if ($request->has('responsable_id')) {
            $query->where('id_responsable', $request->responsable_id);
        }

        // Filter by date range
        if ($request->has('fecha_vencimiento_inicio')) {
            $query->where('fecha_vencimiento_estimada', '>=', $request->fecha_vencimiento_inicio);
        }

        if ($request->has('fecha_vencimiento_fin')) {
            $query->where('fecha_vencimiento_estimada', '<=', $request->fecha_vencimiento_fin);
        }

        $tasks = $query->orderBy('fecha_vencimiento_estimada', 'asc')->paginate(20);

        return response()->json([
            'data' => $tasks->items(),
            'pagination' => [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
            ]
        ], 200);
    }

    /**
     * Store a newly created task
     */
    public function store(Request $request, $projectId)
    {
        $request->validate([
            'id_fase' => 'required|exists:fases_proyectos,id_fase',
            'nombre_tarea' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_vencimiento_estimada' => 'nullable|date',
            'id_responsable' => 'nullable|exists:usuarios,id_usuario',
        ]);

        // Verify that the phase belongs to the project
        $fase = FaseProyecto::where('id_fase', $request->id_fase)
            ->where('id_proyecto', $projectId)
            ->firstOrFail();

        $task = Task::create([
            'id_fase' => $request->id_fase,
            'nombre_tarea' => $request->nombre_tarea,
            'descripcion' => $request->descripcion,
            'fecha_vencimiento_estimada' => $request->fecha_vencimiento_estimada,
            'id_responsable' => $request->id_responsable,
            'estado_tarea' => 'Pendiente',
            'progreso_porcentaje' => 0.00,
        ]);

        return response()->json([
            'message' => 'Tarea creada exitosamente',
            'data' => $task->load(['fase', 'responsable'])
        ], 201);
    }

    /**
     * Display the specified task
     */
    public function show(Request $request, $projectId, $id)
    {
        $task = Task::with(['fase', 'responsable'])
            ->whereHas('fase', function($q) use ($projectId) {
                $q->where('id_proyecto', $projectId);
            })
            ->where('id_tarea', $id)
            ->firstOrFail();

        return response()->json([
            'data' => $task
        ], 200);
    }

    /**
     * Update the specified task
     */
    public function update(Request $request, $projectId, $id)
    {
        $task = Task::with(['fase'])
            ->whereHas('fase', function($q) use ($projectId) {
                $q->where('id_proyecto', $projectId);
            })
            ->where('id_tarea', $id)
            ->firstOrFail();

        $user = $request->user();

        // Check if user is project manager or task responsible
        if ($task->fase->proyecto->id_gerente_proyecto !== $user->id_usuario && 
            $task->id_responsable !== $user->id_usuario && 
            !$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para editar esta tarea'
            ], 403);
        }

        $request->validate([
            'nombre_tarea' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_vencimiento_estimada' => 'nullable|date',
            'id_responsable' => 'nullable|exists:usuarios,id_usuario',
            'estado_tarea' => 'sometimes|in:Pendiente,En Curso,Completada,Bloqueada,Cancelada',
            'progreso_porcentaje' => 'sometimes|numeric|min:0|max:100',
        ]);

        $task->update($request->all());

        return response()->json([
            'message' => 'Tarea actualizada exitosamente',
            'data' => $task->load(['fase', 'responsable'])
        ], 200);
    }

    /**
     * Remove the specified task
     */
    public function destroy(Request $request, $projectId, $id)
    {
        $task = Task::with(['fase'])
            ->whereHas('fase', function($q) use ($projectId) {
                $q->where('id_proyecto', $projectId);
            })
            ->where('id_tarea', $id)
            ->firstOrFail();

        $user = $request->user();

        // Check if user is project manager
        if ($task->fase->proyecto->id_gerente_proyecto !== $user->id_usuario && !$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para eliminar esta tarea'
            ], 403);
        }

        $task->delete();

        return response()->json([
            'message' => 'Tarea eliminada exitosamente'
        ], 200);
    }

    /**
     * Update task status
     */
    public function updateStatus(Request $request, $projectId, $id)
    {
        $task = Task::with(['fase'])
            ->whereHas('fase', function($q) use ($projectId) {
                $q->where('id_proyecto', $projectId);
            })
            ->where('id_tarea', $id)
            ->firstOrFail();

        $user = $request->user();

        // Check if user is task responsible or project manager
        if ($task->id_responsable !== $user->id_usuario && 
            $task->fase->proyecto->id_gerente_proyecto !== $user->id_usuario && 
            !$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para cambiar el estado de esta tarea'
            ], 403);
        }

        $request->validate([
            'estado_tarea' => 'required|in:Pendiente,En Curso,Completada,Bloqueada,Cancelada',
            'progreso_porcentaje' => 'nullable|numeric|min:0|max:100',
        ]);

        $task->update([
            'estado_tarea' => $request->estado_tarea,
            'progreso_porcentaje' => $request->progreso_porcentaje ?? $task->progreso_porcentaje,
        ]);

        return response()->json([
            'message' => 'Estado de la tarea actualizado exitosamente',
            'data' => $task->load(['fase', 'responsable'])
        ], 200);
    }

    /**
     * Get tasks by responsible user
     */
    public function byResponsible(Request $request, $projectId)
    {
        $user = $request->user();
        
        $query = Task::with(['fase', 'responsable'])
            ->whereHas('fase', function($q) use ($projectId) {
                $q->where('id_proyecto', $projectId);
            })
            ->where('id_responsable', $user->id_usuario);

        // Filter by status
        if ($request->has('estado_tarea')) {
            $query->where('estado_tarea', $request->estado_tarea);
        }

        $tasks = $query->orderBy('fecha_vencimiento_estimada', 'asc')->paginate(20);

        return response()->json([
            'data' => $tasks->items(),
            'pagination' => [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
            ]
        ], 200);
    }

    /**
     * Get overdue tasks
     */
    public function overdue(Request $request, $projectId)
    {
        $query = Task::with(['fase', 'responsable'])
            ->whereHas('fase', function($q) use ($projectId) {
                $q->where('id_proyecto', $projectId);
            })
            ->where('fecha_vencimiento_estimada', '<', now())
            ->where('estado_tarea', '!=', 'Completada');

        $tasks = $query->orderBy('fecha_vencimiento_estimada', 'asc')->paginate(20);

        return response()->json([
            'data' => $tasks->items(),
            'pagination' => [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
            ]
        ], 200);
    }

    /**
     * Get tasks statistics for a project
     */
    public function statistics(Request $request, $projectId)
    {
        $tasks = Task::whereHas('fase', function($q) use ($projectId) {
            $q->where('id_proyecto', $projectId);
        });

        $statistics = [
            'total_tasks' => $tasks->count(),
            'tasks_by_status' => [
                'Pendiente' => $tasks->where('estado_tarea', 'Pendiente')->count(),
                'En Curso' => $tasks->where('estado_tarea', 'En Curso')->count(),
                'Completada' => $tasks->where('estado_tarea', 'Completada')->count(),
                'Bloqueada' => $tasks->where('estado_tarea', 'Bloqueada')->count(),
                'Cancelada' => $tasks->where('estado_tarea', 'Cancelada')->count(),
            ],
            'overdue_tasks' => $tasks->where('fecha_vencimiento_estimada', '<', now())
                ->where('estado_tarea', '!=', 'Completada')
                ->count(),
            'average_progress' => $tasks->avg('progreso_porcentaje'),
        ];

        return response()->json([
            'data' => $statistics
        ], 200);
    }
}
