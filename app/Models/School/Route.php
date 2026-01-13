<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Vinkla\Hashids\Facades\Hashids;

class Route extends Model
{
    protected $fillable = ['route_name', 'route_code', 'description', 'company_id', 'branch_id', 'created_by'];

    protected $casts = [
        //
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'route_id', 'id');
    }

    public function buses(): BelongsToMany
    {
        return $this->belongsToMany(Bus::class, 'bus_route', 'route_id', 'bus_id')
                    ->withPivot('assigned_date')
                    ->withTimestamps();
    }

    public function busStops(): BelongsToMany
    {
        return $this->belongsToMany(BusStop::class, 'route_bus_stops', 'route_id', 'bus_stop_id')
                    ->withPivot('sequence_order')
                    ->orderBy('pivot_sequence_order');
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
