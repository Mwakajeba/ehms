<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;

class ClassStream extends Model
{
    protected $table = 'class_stream';

    protected $fillable = [
        'class_id',
        'stream_id',
    ];

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function stream()
    {
        return $this->belongsTo(Stream::class, 'stream_id');
    }
}
