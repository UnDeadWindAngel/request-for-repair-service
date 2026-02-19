<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\RepairRequest;
use App\Policies\RepairRequestPolicy;
use Illuminate\Support\Facades\Event;
use App\Events\RequestStatusChanged;
use App\Listeners\LogRequestAudit;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(
            RepairRequest::class,
            RepairRequestPolicy::class
        );

        Event::listen(
            RequestStatusChanged::class,
            LogRequestAudit::class,
        );
    }
}
