<?php

namespace App\Providers;

use App\Models\Task;
use App\Policies\TaskPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Task::class => TaskPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('admin-access', function ($user) {
            return $user->hasRole('admin');
        });

        Gate::define('manager-access', function ($user) {
            return $user->hasAnyRole(['admin', 'manager']);
        });

        Gate::define('employee-access', function ($user) {
            return $user->hasAnyRole(['admin', 'manager', 'employee']);
        });
    }
}
