<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'short_name', 'city', 'country', 'flag', 'world_rank',
        'acceptance_rate', 'international_rate', 'tuition', 'tuition_value',
        'requirements', 'deadline', 'type', 'accent', 'description', 'is_published',
    ];

    protected function casts(): array
    {
        return ['requirements' => 'array', 'is_published' => 'boolean'];
    }
}
