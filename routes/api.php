<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth Routes
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\PasswordResetController;

// Platform Admin Routes
use App\Http\Controllers\Api\PlatformAdmin\UserManagementController;
use App\Http\Controllers\Api\PlatformAdmin\CompanyVerificationController;
use App\Http\Controllers\Api\PlatformAdmin\ProjectOversightController;
use App\Http\Controllers\Api\PlatformAdmin\SystemSettingsController;

// User Profile Routes
use App\Http\Controllers\Api\UserProfile\ProfileController;
use App\Http\Controllers\Api\UserProfile\MyDocumentsController;
use App\Http\Controllers\Api\UserProfile\MyInvestmentsController;

// Project Management Routes
use App\Http\Controllers\Api\ProjectManagement\ProjectController;
use App\Http\Controllers\Api\ProjectManagement\PropertyController;
use App\Http\Controllers\Api\ProjectManagement\TaskController;
use App\Http\Controllers\Api\ProjectManagement\ProjectMediaController;
use App\Http\Controllers\Api\ProjectManagement\ProjectWorkerController;

// Investor Routes
use App\Http\Controllers\Api\Investor\ProjectListingController;
use App\Http\Controllers\Api\Investor\InvestmentController;

// Community Routes
use App\Http\Controllers\Api\Community\PostController;
use App\Http\Controllers\Api\Community\CommentController;

// Chat Routes
use App\Http\Controllers\Api\Chat\ChatRoomController;
use App\Http\Controllers\Api\Chat\ChatMessageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/password/send-reset-link', [PasswordResetController::class, 'sendResetLink']);
    Route::post('/password/reset', [PasswordResetController::class, 'reset']);
    Route::post('/verify-email', [RegisterController::class, 'verifyEmail']);
});

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth Routes (Authenticated)
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout']);
        Route::get('/me', [LoginController::class, 'me']);
        Route::put('/profile', [RegisterController::class, 'updateProfile']);
        Route::put('/password/change', [PasswordResetController::class, 'changePassword']);
    });
    
    // User Profile Routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::post('/avatar', [ProfileController::class, 'updateAvatar']);
        Route::put('/password', [ProfileController::class, 'updatePassword']);
        
        // My Documents
        Route::prefix('documents')->group(function () {
            Route::get('/', [MyDocumentsController::class, 'index']);
            Route::post('/', [MyDocumentsController::class, 'store']);
            Route::get('/{id}', [MyDocumentsController::class, 'show']);
            Route::put('/{id}', [MyDocumentsController::class, 'update']);
            Route::delete('/{id}', [MyDocumentsController::class, 'destroy']);
        });
        
        // My Investments
        Route::prefix('investments')->group(function () {
            Route::get('/', [MyInvestmentsController::class, 'index']);
            Route::get('/statistics', [MyInvestmentsController::class, 'statistics']);
            Route::get('/history', [MyInvestmentsController::class, 'history']);
            Route::get('/{id}', [MyInvestmentsController::class, 'show']);
        });
    });
    
    // Platform Admin Routes
    Route::middleware('is_platform_admin')->prefix('admin')->group(function () {
        // User Management
        Route::prefix('users')->group(function () {
            Route::get('/', [UserManagementController::class, 'index']);
            Route::get('/{id}', [UserManagementController::class, 'show']);
            Route::put('/{id}/status', [UserManagementController::class, 'updateStatus']);
            Route::delete('/{id}', [UserManagementController::class, 'destroy']);
        });
        
        // Company Verification
        Route::prefix('companies')->group(function () {
            Route::get('/pending', [CompanyVerificationController::class, 'index']);
            Route::get('/{id}', [CompanyVerificationController::class, 'show']);
            Route::post('/{id}/verify', [CompanyVerificationController::class, 'verify']);
            Route::post('/{id}/reject', [CompanyVerificationController::class, 'reject']);
        });
        
        // Project Oversight
        Route::prefix('projects')->group(function () {
            Route::get('/', [ProjectOversightController::class, 'index']);
            Route::get('/statistics', [ProjectOversightController::class, 'statistics']);
            Route::get('/{id}', [ProjectOversightController::class, 'show']);
            Route::put('/{id}/status', [ProjectOversightController::class, 'updateStatus']);
        });
        
        // System Settings
        Route::prefix('settings')->group(function () {
            Route::get('/', [SystemSettingsController::class, 'index']);
            Route::put('/', [SystemSettingsController::class, 'update']);
            Route::get('/templates', [SystemSettingsController::class, 'manageTemplates']);
            Route::put('/templates/{id}', [SystemSettingsController::class, 'updateTemplate']);
        });
    });
    
    // Properties Routes
    Route::prefix('properties')->group(function () {
        Route::get('/', [PropertyController::class, 'index']);
        Route::post('/', [PropertyController::class, 'store']);
        Route::get('/my-properties', [PropertyController::class, 'myProperties']);
        Route::get('/{id}', [PropertyController::class, 'show']);
        Route::put('/{id}', [PropertyController::class, 'update']);
        Route::delete('/{id}', [PropertyController::class, 'destroy']);
    });
    
    // Project Management Routes (For Constructors and Project Managers)
    Route::prefix('projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index']);
        Route::post('/', [ProjectController::class, 'store']);
        Route::get('/my-projects', [ProjectController::class, 'myProjects']);
        Route::get('/participated', [ProjectController::class, 'participatedProjects']);
        Route::get('/{id}', [ProjectController::class, 'show']);
        Route::put('/{id}', [ProjectController::class, 'update']);
        Route::delete('/{id}', [ProjectController::class, 'destroy']);
        Route::put('/{id}/status', [ProjectController::class, 'updateStatus']);
        
        // Project Tasks
        Route::prefix('{projectId}/tasks')->group(function () {
            Route::get('/', [TaskController::class, 'index']);
            Route::post('/', [TaskController::class, 'store']);
            Route::get('/{id}', [TaskController::class, 'show']);
            Route::put('/{id}', [TaskController::class, 'update']);
            Route::delete('/{id}', [TaskController::class, 'destroy']);
            Route::put('/{id}/status', [TaskController::class, 'updateStatus']);
        });
        
        // Project Media
        Route::prefix('{projectId}/media')->group(function () {
            Route::get('/', [ProjectMediaController::class, 'index']);
            Route::post('/', [ProjectMediaController::class, 'store']);
            Route::get('/{id}', [ProjectMediaController::class, 'show']);
            Route::put('/{id}', [ProjectMediaController::class, 'update']);
            Route::delete('/{id}', [ProjectMediaController::class, 'destroy']);
            Route::get('/type/{type}', [ProjectMediaController::class, 'getByType']);
        });
        
        // Project Workers
        Route::prefix('{projectId}/workers')->group(function () {
            Route::get('/', [ProjectWorkerController::class, 'index']);
            Route::post('/', [ProjectWorkerController::class, 'store']);
            Route::put('/{workerId}', [ProjectWorkerController::class, 'update']);
            Route::delete('/{workerId}', [ProjectWorkerController::class, 'destroy']);
            Route::get('/{workerId}/performance', [ProjectWorkerController::class, 'performance']);
        });
    });
    
    // Investor Routes
    Route::prefix('investor')->group(function () {
        // Project Listings
        Route::prefix('projects')->group(function () {
            Route::get('/', [ProjectListingController::class, 'index']);
            Route::get('/featured', [ProjectListingController::class, 'featured']);
            Route::get('/search', [ProjectListingController::class, 'search']);
            Route::post('/filter', [ProjectListingController::class, 'filter']);
            Route::get('/{id}', [ProjectListingController::class, 'show']);
        });
        
        // Investments
        Route::prefix('investments')->group(function () {
            Route::get('/', [InvestmentController::class, 'index']);
            Route::post('/', [InvestmentController::class, 'store']);
            Route::get('/opportunities', [InvestmentController::class, 'opportunities']);
            Route::get('/my-investments', [InvestmentController::class, 'myInvestments']);
            Route::get('/statistics', [InvestmentController::class, 'statistics']);
            Route::get('/{id}', [InvestmentController::class, 'show']);
            Route::put('/{id}', [InvestmentController::class, 'update']);
        });
    });
    
    // Community Routes
    Route::prefix('community')->group(function () {
        // Posts
        Route::prefix('posts')->group(function () {
            Route::get('/', [PostController::class, 'index']);
            Route::post('/', [PostController::class, 'store']);
            Route::get('/{id}', [PostController::class, 'show']);
            Route::put('/{id}', [PostController::class, 'update']);
            Route::delete('/{id}', [PostController::class, 'destroy']);
            Route::post('/{id}/toggle-like', [PostController::class, 'toggleLike']);
            
            // Comments
            Route::prefix('{postId}/comments')->group(function () {
                Route::get('/', [CommentController::class, 'index']);
                Route::post('/', [CommentController::class, 'store']);
                Route::get('/{id}', [CommentController::class, 'show']);
                Route::put('/{id}', [CommentController::class, 'update']);
                Route::delete('/{id}', [CommentController::class, 'destroy']);
                Route::post('/{id}/toggle-like', [CommentController::class, 'toggleLike']);
            });
        });
    });
    
    // Chat Routes
    Route::prefix('chat')->group(function () {
        // Chat Rooms
        Route::prefix('rooms')->group(function () {
            Route::get('/', [ChatRoomController::class, 'index']);
            Route::post('/', [ChatRoomController::class, 'store']);
            Route::get('/{id}', [ChatRoomController::class, 'show']);
            Route::put('/{id}', [ChatRoomController::class, 'update']);
            Route::post('/{id}/add-user', [ChatRoomController::class, 'addUser']);
            Route::post('/{id}/remove-user', [ChatRoomController::class, 'removeUser']);
            
            // Messages
            Route::prefix('{roomId}/messages')->group(function () {
                Route::get('/', [ChatMessageController::class, 'getMessages']);
                Route::post('/', [ChatMessageController::class, 'sendMessage']);
                Route::put('/{messageId}', [ChatMessageController::class, 'updateMessage']);
                Route::delete('/{messageId}', [ChatMessageController::class, 'deleteMessage']);
                Route::post('/{messageId}/mark-read', [ChatMessageController::class, 'markAsRead']);
            });
        });
    });
});

// Get authenticated user
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
