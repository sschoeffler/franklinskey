<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Build extends Model
{
    protected $fillable = [
        'user_id', 'name', 'slug', 'description', 'instructions', 'status', 'image_path', 'sort_order',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(BuildPart::class)->orderBy('sort_order');
    }

    public function getReadinessAttribute(): array
    {
        $parts = $this->parts;
        $inventory = $this->user->inventoryItems;

        $ready = 0;
        $total = $parts->where('is_optional', false)->count();

        foreach ($parts->where('is_optional', false) as $part) {
            $match = $inventory->first(function ($item) use ($part) {
                return stripos($item->name, $part->name) !== false
                    || stripos($part->name, $item->name) !== false;
            });

            if ($match && $match->quantity >= $part->quantity_needed) {
                $ready++;
            }
        }

        return [
            'ready' => $ready,
            'total' => $total,
            'percent' => $total > 0 ? round(($ready / $total) * 100) : 0,
        ];
    }
}
