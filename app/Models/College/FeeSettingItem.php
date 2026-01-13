<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeSettingItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'college_fee_setting_items';

    protected $fillable = [
        'college_fee_setting_id',
        'fee_group_id',
        'fee_period',
        'amount',
        'includes_transport',
        'description',
        'sort_order'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'includes_transport' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function collegeFeeSetting()
    {
        return $this->belongsTo(FeeSetting::class, 'college_fee_setting_id');
    }

    public function feeGroup()
    {
        return $this->belongsTo(\App\Models\FeeGroup::class);
    }
}
