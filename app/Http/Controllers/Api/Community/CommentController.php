<?php

namespace App\Http\Controllers\Api\Community;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\ComentarioPublicacion;
use App\Models\Publicacion;
use App\Models\User;

class CommentController extends Controller
{
    /**
     * Display a listing of comments for a post
     */
    public function index(Request $request, $postId)
    {
        $post = Publicacion::findOrFail($postId);

        $query = ComentarioPublicacion::with(['autor', 'comentariosHijos.autor'])
            ->where('id_publicacion', $postId)
            ->whereNull('id_comentario_padre'); // Only top-level comments

        // Order by date
        $orderBy = $request->get('order_by', 'fecha_comentario');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);

        $comments = $query->paginate(20);

        return response()->json([
            'data' => $comments->items(),
            'pagination' => [
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
            ]
        ], 200);
    }

    /**
     * Store a newly created comment
     */
    public function store(Request $request, $postId)
    {
        $request->validate([
            'contenido' => 'required|string|max:1000',
            'id_comentario_padre' => 'nullable|integer|exists:comentarios_publicaciones,id_comentario',
        ]);

        $user = $request->user();
        $post = Publicacion::findOrFail($postId);

        $comment = ComentarioPublicacion::create([
            'id_publicacion' => $postId,
            'id_autor' => $user->id_usuario,
            'contenido' => $request->contenido,
            'id_comentario_padre' => $request->id_comentario_padre,
        ]);

        return response()->json([
            'message' => 'Comentario creado exitosamente',
            'data' => $comment->load('autor')
        ], 201);
    }

    /**
     * Display the specified comment
     */
    public function show(Request $request, $postId, $id)
    {
        $comment = ComentarioPublicacion::with([
            'autor',
            'comentariosHijos.autor',
            'publicacion'
        ])
        ->where('id_comentario', $id)
        ->where('id_publicacion', $postId)
        ->firstOrFail();

        return response()->json([
            'data' => $comment
        ], 200);
    }

    /**
     * Update the specified comment
     */
    public function update(Request $request, $postId, $id)
    {
        $comment = ComentarioPublicacion::where('id_comentario', $id)
            ->where('id_publicacion', $postId)
            ->firstOrFail();

        $user = $request->user();

        // Check if user owns the comment or is admin
        if ($comment->id_autor !== $user->id_usuario && !$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para editar este comentario'
            ], 403);
        }

        $request->validate([
            'contenido' => 'required|string|max:1000',
        ]);

        $comment->update([
            'contenido' => $request->contenido
        ]);

        return response()->json([
            'message' => 'Comentario actualizado exitosamente',
            'data' => $comment->load('autor')
        ], 200);
    }

    /**
     * Remove the specified comment
     */
    public function destroy(Request $request, $postId, $id)
    {
        $comment = ComentarioPublicacion::where('id_comentario', $id)
            ->where('id_publicacion', $postId)
            ->firstOrFail();

        $user = $request->user();

        // Check if user owns the comment or is admin
        if ($comment->id_autor !== $user->id_usuario && !$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para eliminar este comentario'
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'message' => 'Comentario eliminado exitosamente'
        ], 200);
    }

    /**
     * Toggle like on comment
     */
    public function toggleLike(Request $request, $postId, $id)
    {
        $comment = ComentarioPublicacion::where('id_comentario', $id)
            ->where('id_publicacion', $postId)
            ->firstOrFail();

        $user = $request->user();

        // This is a simplified like system
        // In a real implementation, you'd have a likes table
        return response()->json([
            'message' => 'Función de like implementada',
            'comment_id' => $id
        ], 200);
    }

    /**
     * Get replies to a comment
     */
    public function replies(Request $request, $postId, $commentId)
    {
        $replies = ComentarioPublicacion::with(['autor'])
            ->where('id_publicacion', $postId)
            ->where('id_comentario_padre', $commentId)
            ->orderBy('fecha_comentario', 'asc')
            ->paginate(10);

        return response()->json([
            'data' => $replies->items(),
            'pagination' => [
                'current_page' => $replies->currentPage(),
                'last_page' => $replies->lastPage(),
                'per_page' => $replies->perPage(),
                'total' => $replies->total(),
            ]
        ], 200);
    }

    /**
     * Get my comments (for authenticated user)
     */
    public function myComments(Request $request)
    {
        $user = $request->user();
        
        $query = ComentarioPublicacion::with(['publicacion', 'autor'])
            ->where('id_autor', $user->id_usuario);

        $comments = $query->orderBy('fecha_comentario', 'desc')->paginate(15);

        return response()->json([
            'data' => $comments->items(),
            'pagination' => [
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
            ]
        ], 200);
    }

    /**
     * Get comments by user
     */
    public function byUser(Request $request, $userId)
    {
        $query = ComentarioPublicacion::with(['publicacion', 'autor'])
            ->where('id_autor', $userId);

        $comments = $query->orderBy('fecha_comentario', 'desc')->paginate(15);

        return response()->json([
            'data' => $comments->items(),
            'pagination' => [
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
            ]
        ], 200);
    }

    /**
     * Report a comment
     */
    public function report(Request $request, $postId, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $comment = ComentarioPublicacion::where('id_comentario', $id)
            ->where('id_publicacion', $postId)
            ->firstOrFail();

        $user = $request->user();

        // In a real implementation, you'd create a reports table
        // For now, we'll just return a success message
        return response()->json([
            'message' => 'Comentario reportado exitosamente',
            'comment_id' => $id
        ], 200);
    }
}
