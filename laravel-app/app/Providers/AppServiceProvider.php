<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Support\ServiceProvider;

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
        ValidatorFacade::extend('strip_tags', function ($attribute, $value, $parameters, $validator) {
            $allowedTags = ['a', 'code', 'i', 'strong'];
            $value = strip_tags($value, '<'.implode('><', $allowedTags).'>');
            $validator->setData([$attribute => $value]); // set the cleaned value back to the validator

            return true; // return true because it does not require additional validation
        });
    }
}
