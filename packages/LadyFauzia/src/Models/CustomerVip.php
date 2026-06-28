<?php

namespace LadyFauzia\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Customer\Models\CustomerProxy;

class CustomerVip extends Model
{
    protected $table = 'ladyfauzia_customer_vip';

    protected $fillable = [
        'customer_id',
        'current_tier',
        'total_spend',
    ];

    /**
     * Get the customer associated with the VIP status.
     */
    public function customer()
    {
        return $this->belongsTo(CustomerProxy::modelClass());
    }
}
