<?php

namespace App\Providers;

use App\Models\City;
use App\Models\Province;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
            Schema::defaultStringLength(120);

        $mapModels = [
            'Model.City' => City::class,
            'Model.Province' => Province::class,
        ];

        foreach ($mapModels as $key => $model) {
            $this->app->bind($key, $model);
        }
    }
}
