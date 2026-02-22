<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    protected $fillable = [
        'user_id', 'name', 'description', 'category', 'quantity', 'image_path', 'notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function categories(): array
    {
        return [
            'boards' => 'Boards & MCUs',
            'sensors' => 'Sensors',
            'actuators' => 'Motors & Actuators',
            'displays' => 'Displays & LEDs',
            'cameras' => 'Cameras & Imaging',
            'wires' => 'Wires & Connectors',
            'power' => 'Power & Batteries',
            'storage' => 'Storage & Memory',
            'enclosures' => 'Cases & Mounts',
            'misc' => 'Miscellaneous',
        ];
    }
}
