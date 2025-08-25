<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
// Tambahan import:
use App\Models\Usulan;
use App\Policies\UsulanPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Model => Policy
        Usulan::class => UsulanPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        // Untuk Laravel <=8 masih OK memanggil:
        $this->registerPolicies();

        // Tambahkan Gate khusus di sini kalau diperlukan.
    }
}
