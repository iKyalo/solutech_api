<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Status;
use App\Models\Task;

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

        return response()->json([
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'in_progress_tasks' => $inProgressTasks,
            'not_started_tasks' => $notStartedTasks,
        ]);
    }

    public function getTaskUsers() {
        $tasks = Task::leftJoin('user_tasks', 'tasks.id', '=', 'user_tasks.task_id')
                  ->leftJoin('users', 'user_tasks.user_id', '=', 'users.id')
                  ->select('tasks.*', 'users.name as assigned_to')
                  ->get();

        return response()->json([
            'tasks' => $tasks
        ]);
    }

    public function getTaskStats() {
        $totalTasks = Task::count();
        
        $completedTasks = Task::whereNotNull('completed_at')->count();
        
        $averageCompletionTime = Task::where('status_id', 3)
            ->leftJoin('user_tasks', 'tasks.id', '=', 'user_tasks.task_id')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, user_tasks.start_time, user_tasks.end_time)) as avg_completion_time')
            ->first()
            ->avg_completion_time; 
        
        $topTasks = Task::where('status_id', 3)
                        ->selectRaw('tasks.*, COUNT(user_tasks.id) / tasks.duration AS completion_rate')
                        ->leftJoin('user_tasks', 'tasks.id', '=', 'user_tasks.task_id')
                        ->groupBy('tasks.id')
                        ->orderByDesc('completion_rate')
                        ->take(3)
                        ->get();
        
        return response()->json(['total_tasks' => $totalTasks, 
                    'completed_tasks' => $completedTasks,
                    'average_completion_time' => $averageCompletionTime, 
                    'top_tasks' => $topTasks 
                ]);
    }
}
