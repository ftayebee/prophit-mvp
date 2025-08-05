<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MarketProbability extends Model
{
    use HasFactory;

    protected $fillable = [
        'market_id',
        'probability',
        'recorded_at',
    ];

    protected $dates = ['recorded_at'];

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }
}
