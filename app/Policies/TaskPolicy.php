<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        $level = $user->getHierarchyLevel();

        if ($level === 3) {
            return true;
        }

        if ($level === 2) {
            return $task->created_by === $user->id ||
                $task->assigned_to === $user->id ||
                ($task->assignee && $task->assignee->getHierarchyLevel() < 2) ||
                ($task->creator && $task->creator->getHierarchyLevel() < 2);
        }

        return $task->created_by === $user->id || $task->assigned_to === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Task $task): bool
    {
        $level = $user->getHierarchyLevel();

        if ($level === 3) {
            return true;
        }

        if ($level === 2) {
            return $task->created_by === $user->id ||
                $task->assigned_to === $user->id ||
                ($task->assignee && $task->assignee->getHierarchyLevel() < 2);
        }

        return $task->assigned_to === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        $level = $user->getHierarchyLevel();

        if ($level === 3) {
            return true;
        }

        if ($level === 2) {
            return $task->created_by === $user->id;
        }

        return $task->created_by === $user->id;
    }

    public function restore(User $user, Task $task): bool
    {
        return $user->getHierarchyLevel() >= 2;
    }

    public function forceDelete(User $user, Task $task): bool
    {
        return $user->hasRole('admin');
    }
}
