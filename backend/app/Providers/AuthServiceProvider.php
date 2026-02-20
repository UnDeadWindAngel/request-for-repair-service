<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\RepairRequest;
use App\Policies\RepairRequestPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        RepairRequest::class => RepairRequestPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
