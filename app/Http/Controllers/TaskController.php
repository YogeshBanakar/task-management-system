<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\Facades\DataTables;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();

            $tasks = Task::with(['creator', 'assignee'])
                ->visibleTo($user)
                ->when($request->status, fn($q) => $q->where('status', $request->status))
                ->when($request->priority, fn($q) => $q->where('priority', $request->priority))
                ->orderByDesc('created_at')
                ->select('tasks.*');

            return DataTables::of($tasks)
                ->editColumn('due_date', fn($t) => $t->due_date ? $t->due_date->format('d M Y') : 'Not set')
                ->addColumn('creator_name', fn($task) => $task->creator?->name ?? 'N/A')
                ->addColumn('assignee_name', fn($task) => $task->assignee?->name ?? 'Unassigned')
                ->addColumn('status_badge', function ($task) {
                    $badges = [
                        'pending' => 'secondary',
                        'in_progress' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger'
                    ];
                    $badge = $badges[$task->status] ?? 'secondary';
                    return '<span class="badge bg-' . $badge . '">' . ucfirst(str_replace('_', ' ', $task->status)) . '</span>';
                })
                ->addColumn('priority_badge', function ($task) {
                    $badges = [
                        'low' => 'info',
                        'medium' => 'warning',
                        'high' => 'danger',
                        'urgent' => 'danger'
                    ];
                    $badge = $badges[$task->priority] ?? 'secondary';
                    return '<span class="badge bg-' . $badge . '">' . ucfirst($task->priority) . '</span>';
                })
                ->addColumn('action', function ($task) use ($user) {
                    $actions = '<div class="d-flex justify-content-center gap-2">';

                    if (Gate::allows('view', $task)) {
                        $actions .= '<button class="btn btn-sm btn-info view-task" data-id="' . $task->id . '" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>';
                    }
                    if (Gate::allows('update', $task)) {
                        $actions .= '<button class="btn btn-sm btn-warning edit-task" data-id="' . $task->id . '" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>';
                    }
                    if (Gate::allows('delete', $task)) {
                        $actions .= '<button class="btn btn-sm btn-danger delete-task" data-id="' . $task->id . '" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>';
                    }

                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['status_badge', 'priority_badge', 'action'])
                ->make(true);
        }

        return view('tasks.index');
    }

    public function assignableUsers(Request $request)
    {
        $user = Auth::user();
        $users = $this->getAssignableUsers($user)->map(fn($u) => ['id' => $u->id, 'name' => $u->name]);

        return response()->json([
            'success' => true,
            'users'   => $users,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Task::class);
        $users = $this->getAssignableUsers(Auth::user());
        return view('tasks.create', compact('users'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Task::class);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:pending,in_progress,completed,cancelled',
            'priority'    => 'required|in:low,medium,high,urgent',
            'due_date'    => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        // Assignment protection
        if ($request->assigned_to) {
            $allowed = $this->getAssignableUsers(Auth::user())->pluck('id')->toArray();
            if (!in_array($request->assigned_to, $allowed)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to assign this task to the selected user.',
                ], 403);
            }
        }

        $validated['created_by'] = Auth::id();
        $task = Task::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Task created successfully!',
                'task'    => $task
            ]);
        }

        return redirect()->route('tasks.index')->with('success', 'Task created successfully!');
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'task'    => $task->load(['creator', 'assignee', 'audits.user'])
            ]);
        }

        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);
        $users = $this->getAssignableUsers(Auth::user());

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'task'    => $task,
                'users'   => $users
            ]);
        }

        return view('tasks.edit', compact('task', 'users'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:pending,in_progress,completed,cancelled',
            'priority'    => 'required|in:low,medium,high,urgent',
            'due_date'    => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        // Assignment protection again for security
        if ($request->assigned_to) {
            $allowed = $this->getAssignableUsers(Auth::user())->pluck('id')->toArray();
            if (!in_array($request->assigned_to, $allowed)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to assign this task to the selected user.',
                ], 403);
            }
        }

        $task->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully!',
                'task'    => $task
            ]);
        }

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully!');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $task->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully!'
            ]);
        }

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully!');
    }

    private function getAssignableUsers($user)
    {
        $level = $user->getHierarchyLevel();
        if ($level === 3) {
            return User::all(['id', 'name']);
        }
        if ($level === 2) {
            return User::whereHas('roles', function ($q) {
                $q->where('hierarchy_level', '<', 2);
            })
            ->orWhere('id', $user->id)
            ->distinct()
            ->get(['id', 'name']);
        }
        return User::where('id', $user->id)->get(['id', 'name']);
    }
}
