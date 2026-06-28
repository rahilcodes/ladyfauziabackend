<?php

namespace LadyFauzia\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Customer\Models\CustomerProxy;

class JoyPointsTransaction extends Model
{
    protected $table = 'ladyfauzia_joy_points_transactions';

    protected $fillable = [
        'customer_id',
        'points',
        'type',
        'description',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Get the customer associated with the transaction.
     */
    public function customer()
    {
        return $this->belongsTo(CustomerProxy::modelClass());
    }
}
