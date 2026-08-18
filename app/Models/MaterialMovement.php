<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialMovement extends Model
{
    /** @use \Illuminate\Database\Eloquent\Factories\HasFactory<\Database\Factories\MaterialMovementFactory> */
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'movement_number', 'type', 'has_correlation', 'movement_at', 'responsible',
        'office', 'source_file', 'assignment_reference', 'assignment_id',
    ];

    protected function casts(): array
    {
        return ['has_correlation' => 'boolean', 'movement_at' => 'datetime'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(MaterialMovementItem::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(self::class, 'assignment_id');
    }
}
