<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Vinkla\Hashids\Facades\Hashids;

class Bus extends Model
{
    protected $fillable = [
        'bus_number',
        'driver_name',
        'driver_phone',
        'capacity',
        'model',
        'registration_number',
        'is_active',
        'branch_id',
        'company_id',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer',
    ];

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    public function routes(): BelongsToMany
    {
        return $this->belongsToMany(Route::class, 'bus_route', 'bus_id', 'route_id')
                    ->withPivot('assigned_date')
                    ->withTimestamps();
    }

    public function busStops(): HasMany
    {
        return $this->hasMany(BusStop::class);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKey()
    {
        return Hashids::encode($this->getKey());
    }

    /**
     * Get the route key name for the model.
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Retrieve the model for a bound value.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $decoded = Hashids::decode($value);
        $id = $decoded[0] ?? null;

        return $this->where('id', $id)->firstOrFail();
    }
}
