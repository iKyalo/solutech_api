<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Task;
use App\Models\UserTask;
use Carbon\Carbon;

class TasksController extends Controller
{
    public function index()
    {
        $tasks = Task::leftJoin('status', 'status.id', '=', 'tasks.status_id')
            ->leftJoin('user_tasks', 'tasks.id', '=', 'user_tasks.task_id')
            ->leftJoin('users', 'users.id', '=', 'user_tasks.user_id')
            ->select('tasks.*', 'users.name AS user_name', 'status.name AS status_name')
            ->get();

        return response()->json(['tasks' => $tasks]);
    }

    public function my_tasks(Request $request)
    {
        $userId = $request->user_id;
        $tasks = DB::table('user_tasks')
            ->leftJoin('tasks', 'user_tasks.task_id', '=', 'tasks.id')
            ->leftJoin('status', 'user_tasks.status_id', '=', 'status.id')
            ->where('user_tasks.user_id', $userId)
            ->select('tasks.*', 'user_tasks.*', 'status.name AS status_name')
            ->orderBy('tasks.id', 'asc')
            ->get();
        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'description' => '',
            'date' => '',
        ]);

        $task = new Task;
        $task->name = $validatedData['title'];
        $task->description = $validatedData['description'];
        $task->due_date = $validatedData['date'];
        $task->save();

        return response()->json(['task' => $task]);
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'status' => 'required',
            'due_date' => 'required|date_format:Y-m-d',
        ]);

        $task->title = $validatedData['title'];
        $task->description = $validatedData['description'];
        $task->status = $validatedData['status'];
        $task->due_date = $validatedData['due_date'];
        $task->save();
        
        return response()->json(['task' => $task]);
    }

    public function update_tasks(Request $request) {
        $payload = $request->all();

        $ids = $payload['ids'];
        $status_id = $payload['status_id'];
        $user_id = $payload['user_id'];

        // return $payload;

        foreach ($ids as $id) {
            $user_task = UserTask::where('user_id', $user_id)
                                ->where('task_id', $id)
                                ->first();
            if ($user_task) {
                $user_task->status_id = $status_id;
                if($status_id == 3) {
                    $user_task->end_time = Carbon::now();
                }
                if($status_id == 2) {
                    $user_task->start_time = Carbon::now();
                }
                $user_task->save();
            } else {
                $user_task = new UserTask;
                $user_task->user_id = $user_id;
                $user_task->task_id = $id;
                $user_task->status_id = $status_id;
                if($status_id == 3) {
                    $user_task->end_time = Carbon::now();
                }
                if($status_id == 2) {
                    $user_task->start_time = Carbon::now();
                }
                $user_task->save();
            }

            $task = Task::where('id', $id)->first();
            $task->status_id = $status_id;
            $task->save();
        }

        return response()->json(['message' => 'Tasks updated successfully']);
    } 


    public function destroy(Request $request)
    {
        // return $request;
        $task_id = $request->taskId;
        $task = Task::findOrFail($task_id);
        $task->delete();
        return response()->json(['message' => 'Task deleted successfully']);
    }
}


