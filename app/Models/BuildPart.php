<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuildPart extends Model
{
    protected $fillable = [
        'build_id', 'name', 'description', 'category', 'quantity_needed', 'is_optional', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_optional' => 'boolean',
        ];
    }

    public function build(): BelongsTo
    {
        return $this->belongsTo(Build::class);
    }
}
