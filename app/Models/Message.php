<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = ['project_id', 'role', 'content', 'image_path', 'image_mime', 'has_image', 'user_ip'];

    protected function casts(): array
    {
        return [
            'has_image' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
