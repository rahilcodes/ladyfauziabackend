<?php

namespace LadyFauzia\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Customer\Models\CustomerProxy;

class ReferralCode extends Model
{
    protected $table = 'ladyfauzia_referral_codes';

    protected $fillable = [
        'customer_id',
        'code',
    ];

    /**
     * Get the customer associated with the referral code.
     */
    public function customer()
    {
        return $this->belongsTo(CustomerProxy::modelClass());
    }
}
