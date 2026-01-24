<?php

namespace App\Policies;

use App\Models\CaseTask;
use App\Models\User;

class CaseTaskPolicy
{
    public function view(User $user, CaseTask $task): bool
    {
        return $user->hasPermissionTo('case.task.view');
    }

    public function submit(User $user, CaseTask $task): bool
    {
        return $user->hasPermissionTo('case.task.submit');
    }

    public function approve(User $user, CaseTask $task): bool
    {
        return $user->hasPermissionTo('case.task.approve');
    }
}
