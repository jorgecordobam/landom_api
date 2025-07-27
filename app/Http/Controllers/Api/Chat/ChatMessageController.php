<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Events\NewChatMessage;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatMessageController extends Controller
{
    /**
     * Get messages for a chat room
     */
    public function getMessages($roomId): JsonResponse
    {
        try {
            $messages = ChatMessage::where('room_id', $roomId)
                ->with('user:id,nombre,apellido')
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $messages,
                'message' => 'Messages retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving messages: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send a new message
     */
    public function sendMessage(Request $request, $roomId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if user is part of this chat room
            $room = ChatRoom::find($roomId);
            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat room not found'
                ], 404);
            }

            // Create the message
            $message = ChatMessage::create([
                'room_id' => $roomId,
                'user_id' => Auth::id(),
                'message' => $request->message,
                'is_read' => false,
            ]);

            // Load the user relationship
            $message->load('user:id,nombre,apellido');

            // Broadcast the new message event
            broadcast(new NewChatMessage(
                $message->message,
                $message->user,
                $roomId
            ))->toOthers();

            return response()->json([
                'success' => true,
                'data' => $message,
                'message' => 'Message sent successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a message
     */
    public function updateMessage(Request $request, $roomId, $messageId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $message = ChatMessage::where('id', $messageId)
                ->where('room_id', $roomId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$message) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message not found or you are not authorized to edit it'
                ], 404);
            }

            $message->update([
                'message' => $request->message,
            ]);

            $message->load('user:id,nombre,apellido');

            return response()->json([
                'success' => true,
                'data' => $message,
                'message' => 'Message updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a message
     */
    public function deleteMessage($roomId, $messageId): JsonResponse
    {
        try {
            $message = ChatMessage::where('id', $messageId)
                ->where('room_id', $roomId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$message) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message not found or you are not authorized to delete it'
                ], 404);
            }

            $message->delete();

            return response()->json([
                'success' => true,
                'message' => 'Message deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark message as read
     */
    public function markAsRead($roomId, $messageId): JsonResponse
    {
        try {
            $message = ChatMessage::where('id', $messageId)
                ->where('room_id', $roomId)
                ->first();

            if (!$message) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message not found'
                ], 404);
            }

            $message->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Message marked as read'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking message as read: ' . $e->getMessage()
            ], 500);
        }
    }
}
