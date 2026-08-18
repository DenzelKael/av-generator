<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialMovementItem extends Model
{
    protected $fillable = [
        'line_number', 'description', 'unit', 'lot', 'serial_from', 'serial_to', 'quantity', 'status',
    ];

    public function movement(): BelongsTo
    {
        return $this->belongsTo(MaterialMovement::class, 'material_movement_id');
    }
}
