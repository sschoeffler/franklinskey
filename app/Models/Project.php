<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = ['session_id', 'user_id', 'name', 'slug', 'board_type'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
