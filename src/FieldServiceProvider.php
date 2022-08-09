<?php

namespace Elbgoods\NovaMapboxShapeField;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Nova::serving(function (ServingNova $event) {
            Nova::script('nova-mapbox-shape-field', __DIR__.'/../dist/js/field.js');
            Nova::style('nova-mapbox-shape-field', __DIR__.'/../dist/css/field.css');
        });
    }
}
