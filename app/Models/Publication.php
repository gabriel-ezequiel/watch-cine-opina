<?php

namespace App\Models;

use App\Enums\PublicationStatus;
use App\Enums\PublicationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Publication extends Model
{

    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'type',
        'description',
        'status',
    ];

    protected $casts = [
        'type' => PublicationType::class,
        'status' => PublicationStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function follows(): HasMany
    {
        return $this->hasMany(Follow::class);
    }
}
