<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //Passport::routes();   
        Passport::routes(null, ['middleware' => [ \Fruitcake\Cors\HandleCors::class ]]);        

        //token dura 1 hora     
        Passport::tokensExpireIn(now()->addHours(1)); 
        Passport::refreshTokensExpireIn(now()->addDays(1));      

        //
    }
}
