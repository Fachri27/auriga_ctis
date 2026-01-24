<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function view(?User $user, Report $report): bool
    {
        if ($report->is_published ?? false) {
            return true;
        }

        if (! $user) {
            return false;
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        return $user->id === $report->created_by;
    }

    public function create(?User $user): bool
    {
        // allow guest or any authenticated public user
        return true;
    }

    public function update(User $user, Report $report): bool
    {
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        return $report->created_by && $user->id === $report->created_by;
    }

    public function verify(User $user, Report $report): bool
    {
        // allow admins to verify without explicit permission
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        return $user->hasPermissionTo('report.verify');
    }

    public function reject(User $user, Report $report): bool
    {
        // allow admins to reject without explicit permission
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        return $user->hasPermissionTo('report.reject');
    }

    public function publish(User $user, Report $report): bool
    {
        // allow admins to publish without explicit permission
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        return $user->hasPermissionTo('report.publish');
    }

    // LEGACY: previous simple permission based view implementation
    /*
    public function view(User $user, Report $report): bool
    {
        if ($report->is_published ?? false) {
            return true;
        }

        return $user->hasPermissionTo('report.view');
    }

    public function verify(User $user, Report $report): bool
    {
        return $user->hasPermissionTo('report.verify');
    }

    public function reject(User $user, Report $report): bool
    {
        return $user->hasPermissionTo('report.reject');
    }
    */
}
