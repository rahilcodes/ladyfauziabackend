<?php

namespace LadyFauzia\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Customer\Models\CustomerProxy;
use Webkul\Sales\Models\OrderProxy;

class Referral extends Model
{
    protected $table = 'ladyfauzia_referrals';

    protected $fillable = [
        'referrer_id',
        'friend_email',
        'status',
        'order_id',
    ];

    /**
     * Get the referrer customer.
     */
    public function referrer()
    {
        return $this->belongsTo(CustomerProxy::modelClass(), 'referrer_id');
    }

    /**
     * Get the associated order that completed the referral.
     */
    public function order()
    {
        return $this->belongsTo(OrderProxy::modelClass(), 'order_id');
    }
}
