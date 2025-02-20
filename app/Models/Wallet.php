<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'balance','limit'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function checkDailyLimit($amount)
    {
        $todayTransactions = $this->transactions()
            ->whereDate('created_at', today())
            ->sum('amount');


        return ($todayTransactions + $amount) <= $this->limit;
    }

    public function detectSuspiciousActivity($amount)
    {
        $thresholdAmount = 50;  
        $timeWindow = Carbon::now()->subMinutes(5);

        $highValueTransactions = $this->transactions()
            ->where('amount', '>=', $thresholdAmount)
            ->where('created_at', '>=', $timeWindow)
            ->count();

        return $highValueTransactions > 1;
    }
}
