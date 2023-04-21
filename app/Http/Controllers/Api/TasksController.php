<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Task;
use App\Models\UserTask;

class TasksController extends Controller
{
    public function index()
    {
        $tasks = Task::all();
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
            ->get();
        return response()->json(['tasks' => $tasks]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'status' => 'required',
            'due_date' => 'required|date_format:Y-m-d',
        ]);

        $task = new Task;
        $task->title = $validatedData['title'];
        $task->description = $validatedData['description'];
        $task->status = $validatedData['status'];
        $task->due_date = $validatedData['due_date'];
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

        try {
            DB::beginTransaction();

            foreach ($ids as $id) {
                $user_task = UserTask::where('user_id', $user_id)
                                    ->where('task_id', $id)
                                    ->first();

                if ($user_task) {
                    $user_task->status_id = $status_id;
                    $user_task->save();
                } else {
                    $user_task = new UserTask;
                    $user_task->user_id = $user_id;
                    $user_task->task_id = $id;
                    $user_task->status_id = $status_id;
                    $user_task->save();
                }
            }

            DB::commit();

            return response()->json(['message' => 'Tasks updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()]);
        }
    }


    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();
        return response()->json(['message' => 'Task deleted successfully']);
    }
}


