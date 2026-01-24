<?php

namespace App\Policies;

use App\Models\CaseModel;
use App\Models\User;

class CasePolicy
{
    public function view(User $user, CaseModel $case): bool
    {
        if (($case->is_public ?? false) || ($case->published_at ?? false)) {
            return true;
        }

        return $user->hasPermissionTo('case.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('case.create');
    }

    public function update(User $user, CaseModel $case): bool
    {
        // allow if user has general update permission or is the creator
        if ($user->hasPermissionTo('case.update')) {
            return true;
        }

        return $case->created_by && $case->created_by === $user->id && $user->hasPermissionTo('case.update');
    }

    public function changeStatus(User $user, CaseModel $case): bool
    {
        return $user->hasPermissionTo('case.change-status');
    }

    public function requestPublish(User $user, CaseModel $case): bool
    {
        return $user->hasPermissionTo('case.publish.request') && ! ($case->is_public ?? false);
    }

    public function approvePublish(User $user, CaseModel $case): bool
    {
        return $user->hasPermissionTo('case.publish.approve');
    }

    public function publish(User $user, CaseModel $case): bool
    {
        return $user->hasPermissionTo('case.publish');
    }

    public function unpublish(User $user, CaseModel $case): bool
    {
        return $user->hasPermissionTo('case.unpublish') || $user->hasPermissionTo('case.publish');
    }

    public function publishMap(User $user, CaseModel $case): bool
    {
        return $user->hasPermissionTo('case.map.publish');
    }

    // LEGACY: previous minimal publish methods
    /*
    public function publish(User $user, CaseModel $case): bool
    {
        return $user->hasPermissionTo('case.publish');
    }

    public function publishMap(User $user, CaseModel $case): bool
    {
        return $user->hasPermissionTo('case.map.publish');
    }
    */
}
