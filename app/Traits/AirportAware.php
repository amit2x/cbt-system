<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait AirportAware
{
    /**
     * Tables without airport_id should not use this trait.
     */
    // protected static function bootAirportAware(): void
    // {
    //     static::creating(function ($model) {

    //         if (
    //             empty($model->airport_id)
    //             && ($airportId = static::getCurrentAirportId())
    //         ) {
    //             $model->airport_id = $airportId;
    //         }

    //     });

    //     static::addGlobalScope('airport', function (Builder $builder) {

    //         if (!config('app.multi_airport', true)) {
    //             return;
    //         }

    //         if (app()->runningInConsole()) {
    //             return;
    //         }

    //         $airportId = static::getCurrentAirportId();

    //         if (!$airportId) {
    //             return;
    //         }

    //         $builder->where(
    //             $builder->getModel()->getTable() . '.airport_id',
    //             $airportId
    //         );

    //     });
    // }

    /**
     * Current airport from authenticated user.
     */
    public static function getCurrentAirportId(): ?int
    {
        try {

            $user = Auth::user();

            if ($user && !empty($user->airport_id)) {
                return (int) $user->airport_id;
            }

        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    /**
     * Airport relationship.
     */
    public function airport()
    {
        return $this->belongsTo(
            \App\Models\Airport::class
        );
    }

    /**
     * Query specific airport.
     */
    public function scopeForAirport(
        $query,
        int $airportId
    ) {
        return $query->where(
            'airport_id',
            $airportId
        );
    }

    /**
     * Remove airport scope.
     */
    public function scopeAllAirports($query)
    {
        return $query->withoutGlobalScope(
            'airport'
        );
    }
}
