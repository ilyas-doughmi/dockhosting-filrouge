<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Container extends Model
{
    protected $fillable = [
        'project_id',
        'container_id',
        'container_name',
        'image',
        'port',
        'internal_port',
        'status',
        'started_at',
        'stopped_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'stopped_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
