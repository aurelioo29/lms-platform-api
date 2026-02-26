<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        \App\Models\CourseModule::class => \App\Policies\CourseModulePolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
