<?php

namespace LadyFauzia\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Customer\Models\CustomerProxy;

class JoyPointsWallet extends Model
{
    protected $table = 'ladyfauzia_joy_points_wallets';

    protected $fillable = [
        'customer_id',
        'balance',
    ];

    /**
     * Get the customer associated with the wallet.
     */
    public function customer()
    {
        return $this->belongsTo(CustomerProxy::modelClass());
    }

    /**
     * Adjust customer points balance and log transaction.
     */
    public static function adjustPoints($customerId, int $points, string $type, string $description)
    {
        $wallet = self::firstOrCreate(
            ['customer_id' => $customerId],
            ['balance' => 0]
        );

        $wallet->balance += $points;
        if ($wallet->balance < 0) {
            $wallet->balance = 0;
        }
        $wallet->save();

        JoyPointsTransaction::create([
            'customer_id' => $customerId,
            'points'      => $points,
            'type'        => $type,
            'description' => $description,
        ]);

        return $wallet;
    }
}
