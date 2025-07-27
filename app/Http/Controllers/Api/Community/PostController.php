<?php

namespace App\Http\Controllers\Api\Community;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Publicacion;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Display a listing of posts
     */
    public function index(Request $request)
    {
        $query = Publicacion::with(['autor', 'comentarios.autor'])
            ->where('estado_publicacion', 'Publicado');

        // Filter by author
        if ($request->has('autor_id')) {
            $query->where('id_autor', $request->autor_id);
        }

        // Filter by date range
        if ($request->has('fecha_inicio')) {
            $query->where('fecha_publicacion', '>=', $request->fecha_inicio);
        }

        if ($request->has('fecha_fin')) {
            $query->where('fecha_publicacion', '<=', $request->fecha_fin);
        }

        // Search by title or content
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('contenido_html', 'like', "%{$search}%");
            });
        }

        // Order by
        $orderBy = $request->get('order_by', 'fecha_publicacion');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);

        $posts = $query->paginate(15);

        return response()->json([
            'data' => $posts->items(),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ]
        ], 200);
    }

    /**
     * Store a newly created post
     */
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'contenido_html' => 'required|string',
            'url_imagen_principal' => 'nullable|url|max:500',
            'estado_publicacion' => 'sometimes|in:Borrador,Publicado,Archivado',
        ]);

        $user = $request->user();

        $post = Publicacion::create([
            'id_autor' => $user->id_usuario,
            'titulo' => $request->titulo,
            'contenido_html' => $request->contenido_html,
            'url_imagen_principal' => $request->url_imagen_principal,
            'estado_publicacion' => $request->estado_publicacion ?? 'Borrador',
        ]);

        return response()->json([
            'message' => 'Publicación creada exitosamente',
            'data' => $post->load('autor')
        ], 201);
    }

    /**
     * Display the specified post
     */
    public function show($id)
    {
        $post = Publicacion::with([
            'autor',
            'comentarios.autor',
            'comentarios.comentariosHijos.autor'
        ])->findOrFail($id);

        // Increment view count (you might want to track this in a separate table)
        // $post->increment('views_count');

        return response()->json([
            'data' => $post
        ], 200);
    }

    /**
     * Update the specified post
     */
    public function update(Request $request, $id)
    {
        $post = Publicacion::findOrFail($id);
        $user = $request->user();

        // Check if user owns the post or is admin
        if ($post->id_autor !== $user->id_usuario && !$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para editar esta publicación'
            ], 403);
        }

        $request->validate([
            'titulo' => 'sometimes|string|max:255',
            'contenido_html' => 'sometimes|string',
            'url_imagen_principal' => 'nullable|url|max:500',
            'estado_publicacion' => 'sometimes|in:Borrador,Publicado,Archivado',
        ]);

        $post->update($request->all());

        return response()->json([
            'message' => 'Publicación actualizada exitosamente',
            'data' => $post->load('autor')
        ], 200);
    }

    /**
     * Remove the specified post
     */
    public function destroy(Request $request, $id)
    {
        $post = Publicacion::findOrFail($id);
        $user = $request->user();

        // Check if user owns the post or is admin
        if ($post->id_autor !== $user->id_usuario && !$user->isAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para eliminar esta publicación'
            ], 403);
        }

        $post->delete();

        return response()->json([
            'message' => 'Publicación eliminada exitosamente'
        ], 200);
    }

    /**
     * Toggle like on post
     */
    public function toggleLike(Request $request, $id)
    {
        $post = Publicacion::findOrFail($id);
        $user = $request->user();

        // This is a simplified like system
        // In a real implementation, you'd have a likes table
        return response()->json([
            'message' => 'Función de like implementada',
            'post_id' => $id
        ], 200);
    }

    /**
     * Get featured posts
     */
    public function featured(Request $request)
    {
        $posts = Publicacion::with(['autor', 'comentarios'])
            ->where('estado_publicacion', 'Publicado')
            ->orderBy('fecha_publicacion', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => $posts
        ], 200);
    }

    /**
     * Get posts by user
     */
    public function byUser(Request $request, $userId)
    {
        $query = Publicacion::with(['autor', 'comentarios'])
            ->where('id_autor', $userId)
            ->where('estado_publicacion', 'Publicado');

        $posts = $query->orderBy('fecha_publicacion', 'desc')->paginate(15);

        return response()->json([
            'data' => $posts->items(),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ]
        ], 200);
    }

    /**
     * Get my posts (for authenticated user)
     */
    public function myPosts(Request $request)
    {
        $user = $request->user();
        
        $query = Publicacion::with(['autor', 'comentarios'])
            ->where('id_autor', $user->id_usuario);

        // Filter by status
        if ($request->has('estado')) {
            $query->where('estado_publicacion', $request->estado);
        }

        $posts = $query->orderBy('fecha_publicacion', 'desc')->paginate(15);

        return response()->json([
            'data' => $posts->items(),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ]
        ], 200);
    }

    /**
     * Search posts
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $query = Publicacion::with(['autor', 'comentarios'])
            ->where('estado_publicacion', 'Publicado')
            ->where(function($q) use ($request) {
                $q->where('titulo', 'like', "%{$request->q}%")
                  ->orWhere('contenido_html', 'like', "%{$request->q}%");
            });

        $posts = $query->orderBy('fecha_publicacion', 'desc')->paginate(15);

        return response()->json([
            'data' => $posts->items(),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ]
        ], 200);
    }
}
