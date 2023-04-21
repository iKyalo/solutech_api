<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserTask;

class UsersController extends Controller
{
    public function fetchUserCounts() {
        // Total Users
        $totalUsers = User::count();

        // Active Users
        $activeUsers = UserTask::distinct('user_id')->count('user_id');

        return response()->json([
            'total' => $totalUsers,
            'active' => $activeUsers
        ]);
    }

    public function fetchUserActivity() {
        $users = DB::table('users')
            ->leftJoin('user_tasks', 'users.id', '=', 'user_tasks.user_id')
            ->leftJoin('tasks', 'user_tasks.task_id', '=', 'tasks.id')
            ->select('users.name', 
                     DB::raw('COUNT(DISTINCT user_tasks.task_id) as tasks_assigned'),
                     DB::raw('SUM(CASE WHEN tasks.status_id = 3 THEN 1 ELSE 0 END) as tasks_completed'),
                     DB::raw('AVG(CASE WHEN tasks.status_id = 3 THEN TIMESTAMPDIFF(HOUR, user_tasks.start_time, user_tasks.end_time) ELSE NULL END) as avg_completion_time'))
            ->groupBy('users.id')
            ->get();

        return response()->json(['users' => $users]);

    }
}
