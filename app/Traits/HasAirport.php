<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use App\Models\Scopes\AirportScope;


trait HasAirport
{
    protected static function bootHasAirport()
    {
        static::creating(function ($model) {
            if (Auth::check() && empty($model->airport_id)) {
                $model->airport_id = Auth::user()->airport_id;
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->airport_id = Auth::user()->airport_id;
            }
        });
        
        static::addGlobalScope(new AirportScope());
    }
}