<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('max_chars', function ($attribute, $value, $parameters, $validator) {
            $max = $parameters[0] ?? 400;
            return mb_strlen($value) <= $max;
        }, '本文は400文字以内で入力してください');
    }
}
