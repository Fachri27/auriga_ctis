<?php

namespace App\Providers;

use App\Models\CaseDocument;
use App\Models\CaseModel;
use App\Models\CaseTask;
use App\Models\Report;
use App\Policies\CaseDocumentPolicy;
use App\Policies\CasePolicy;
use App\Policies\CaseTaskPolicy;
use App\Policies\ReportPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Report::class => ReportPolicy::class,
        CaseModel::class => CasePolicy::class,
        CaseTask::class => CaseTaskPolicy::class,
        CaseDocument::class => CaseDocumentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Global bypass for system administrators with system.all permission
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return true;
            }

            return null;
        });


        // gate superadmin
        Gate::before(function ($user) {
            return $user->hasRole('superadmin') ? true : null;
        });

        // Generic publish gate — map model base name to permission
        Gate::define('publish', function ($user, $model) {
            $type = strtolower(class_basename($model));
            // Possible model names: CaseModel => 'case', Report => 'report'
            if (stripos($type, 'case') !== false && stripos($type, 'casemodel') !== false) {
                $perm = 'case.publish';
            } else {
                $perm = $type.'.publish';
            }

            return $user->hasPermissionTo($perm);
        });

        Gate::define('map.publish', fn ($user, $model) => $user->hasPermissionTo('case.map.publish'));
    }
}
