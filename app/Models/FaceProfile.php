<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class FaceProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'descriptors',
        'sample_count',
        'last_enrolled_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'descriptors' => 'array',
            'last_enrolled_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}