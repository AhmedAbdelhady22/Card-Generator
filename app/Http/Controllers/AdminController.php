<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Card;
use App\Models\Role;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;


class AdminController extends Controller
{
    public function dashboard(){
        $user = User::find(  Auth::id());
        if(!$user->isAdmin()){
            return $this->errorResponse('Unauthorized', 403);
        }
            $stats = [
        'total_users' => User::count(),
        'active_users' => User::where('is_active', true)->count(),
        'total_cards' => Card::count(),
        'active_cards' => Card::where('is_active', true)->count(),
        ];

        $recentActivities = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['id', 'user_id', 'action', 'model_type', 'model_id', 'description', 'created_at']);

            return $this->successResponse([
            'stats' => $stats,
            'recent_activities' => $recentActivities
        ], 
        'Dashboard data retrieved successfully');

    }

    public function getAllUsers(){
        $user = User::find( Auth::id() );
        if(!$user->isAdmin()){
            return $this->errorResponse('Unauthorized', 403);
        }

        $users = User::select(['id', 'name', 'email', 'is_active', 'last_login_at', 'created_at'])
            ->with('role:id,name')
            ->withCount('cards')
            ->orderBy('created_at','desc')
            ->paginate(10);

        return $this->successResponse($users, 'Users retrieved successfully');



    }


    public function getStats() {
        $user = User::find( Auth::id() );
        if(!$user->isAdmin()){
            return $this->errorResponse('Unauthorized', 403);
        }

            $stats = [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'total_cards' => Card::count(),
                'active_cards' => Card::where('is_active', true)->count(),
            ];

            return $this->successResponse($stats, 'Statistics retrieved successfully');
    }

    public function getAllCards() {
        $user = User::find( Auth::id() );
        if(!$user->isAdmin()){
            return $this->errorResponse('Unauthorized', 403);
        }

        $cards = Card::with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->successResponse($cards, 'Cards retrieved successfully');
    }


    public function toggleUserStatus(Request $request, $id) {
        $user = User::find($id);
        if(!$user->isAdmin()){
            return $this->errorResponse('Unauthorized', 403);
        }

        $user = User::find($id);
        if(!$user) {
            return $this->errorResponse('User not found', 404);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $this->logActivity(
            'toggled_user_status',
            $user,
            null,
            ['status' => $user->is_active],
            'Admin toggled user status'
        );

        return $this->successResponse($user, 'User status updated successfully');
    }



}
