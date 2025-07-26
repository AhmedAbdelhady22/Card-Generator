<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function myActivities(Request $request){

        $user = User::find(  Auth::id());
        if (!$user) {
            return $this->errorResponse('User not authenticated', 401);
        }


        $activities = $user->activityLogs()
            ->select(['id', 'action', 'model_type', 'model_id', 'description', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            return $this->successResponse($activities, 'User activities retrieved successfully');

    }

    public function allActivities(Request $request){
        $user = User::find(  Auth::id());
        if (!$user) {
            return $this->errorResponse('User not authenticated', 401);
        }

        if (!$user->isAdmin()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $activities = ActivityLog::with('user:id,name,email')
            ->select(['id','user_id' ,'action', 'model_type', 'model_id', 'description', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->successResponse($activities, 'All activities retrieved successfully');


    }
}
