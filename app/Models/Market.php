<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Market extends Model
{
    use HasFactory;

    protected $fillable = [
        'market_id',
        'question',
        'current_probability',
        'volume',
        'last_update',
    ];

    protected $dates = ['last_update'];

    public function probabilities(): HasMany
    {
        return $this->hasMany(MarketProbability::class);
    }
}
