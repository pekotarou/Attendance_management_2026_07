<?php

namespace App\Providers;

use App\Models\Attendance;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Policies\AttendanceRecordPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
    */
    protected $policies = [
        // 修正: AttendanceモデルにAPI用Policyを紐づけ
        Attendance::class => AttendanceRecordPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
    */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
