<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use App\Models\UserTask;

class StatusController extends Controller
{
    public function index() {
        $statuses = Status::all();

        return response()->json($statuses);
    }

    public function getTaskStatus() {
        $totalTasks = Task::count();
        $completedTasks = Task::where('status_id', 3)->count();
        $inProgressTasks = Task::where('status_id', 2)->count();
        $notStartedTasks = Task::where('status_id', 1)->count();

        $tasks = Task::leftJoin('user_tasks', 'tasks.id', '=', 'user_tasks.task_id')
                  ->leftJoin('users', 'user_tasks.user_id', '=', 'users.id')
                  ->leftJoin('status', 'tasks.status_id', '=', 'status.id')
                  ->select('tasks.*', 'users.name AS user_name', 'status.name AS status_name' )
                  ->get();

        return response()->json([
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'in_progress_tasks' => $inProgressTasks,
            'not_started_tasks' => $notStartedTasks,
            'tasks' => $tasks
        ]);
    }

    public function getTaskStats() {
        $totalTasks = Task::count();
        
        $completedTasks = Task::where('status_id', 3)->count();
        
        $averageCompletionTime = Task::where('tasks.status_id', 3)
            ->leftJoin('user_tasks', 'tasks.id', '=', 'user_tasks.task_id')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, user_tasks.start_time, user_tasks.end_time)) as avg_completion_time')
            ->first()
            ->avg_completion_time; 
        
        $topTasks = Task::where('tasks.status_id', 3)
            ->selectRaw('tasks.name, COUNT(user_tasks.id) / (TIME_TO_SEC(TIMEDIFF(user_tasks.end_time, user_tasks.start_time)) / 60) AS completion_rate')
            ->leftJoin('user_tasks', 'tasks.id', '=', 'user_tasks.task_id')
            ->groupBy('tasks.name', 'user_tasks.start_time', 'user_tasks.end_time')
            ->orderByDesc('completion_rate')
            ->take(3)
            ->get();
        
        
        return response()->json(['total_tasks' => $totalTasks, 
                    'completed_tasks' => $completedTasks,
                    'average_completion_time' => $averageCompletionTime, 
                    'top_tasks' => $topTasks 
                ]);
    }

    public function getTaskUsers() {
        // Get count of all users
        $userCount = User::count();

        // Get count of active users with assigned tasks
        $activeUserCount = UserTask::select('user_id')->distinct()->count('user_id');

        // $users = UserTask::whereNull('user_tasks.deleted_at')
        //     ->leftJoin('users', 'users.id', '=', 'user_tasks.user_id')
        //     ->orderBy('user_tasks.user_id', 'asc')
        //     ->select('users.name', 'user_tasks.user_id', 'user_tasks.task_id', 'status_id', 'start_time', 'end_time')
        //     ->get();
        // $users->each(function ($user) {
        //     $user->tasks_count = $user->tasks_count ?? 0;
        //     $user->completed_tasks_count = $user->completed_tasks_count ?? 0;
        //     $user->average_completion_time = $user->tasks_count > 0 ? $user->tasks->sum(function ($task) {
        //         return $task->completed_tasks_count > 0 ? $task->duration / $task->completed_tasks_count : 0;
        //     }) / $user->tasks_count : 0;
        // });
        $users = UserTask::whereNull('user_tasks.deleted_at')
            ->leftJoin('users', 'users.id', '=', 'user_tasks.user_id')
            ->leftJoin('tasks', 'tasks.id', '=', 'user_tasks.task_id')
            ->orderBy('user_tasks.user_id', 'asc')
            ->select('user_tasks.user_id', 'users.name')
            ->selectRaw('count(DISTINCT user_tasks.task_id) as tasks_assigned')
            ->selectRaw('count(DISTINCT case when user_tasks.status_id = 3 then user_tasks.task_id end) as tasks_completed')
            ->selectRaw('avg(TIME_TO_SEC(TIMEDIFF(user_tasks.end_time, user_tasks.start_time)) / 3600) as avg_completion_time')
            ->groupBy('user_tasks.user_id', 'users.name')
            ->get();


        return response()->json([
            'total' => $userCount,
            'active' => $activeUserCount,
            'users' => $users
        ]);


    }
}
