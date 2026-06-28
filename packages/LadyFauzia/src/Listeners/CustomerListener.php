<?php

namespace LadyFauzia\Listeners;

use Illuminate\Support\Str;
use Webkul\Customer\Models\Customer;
use LadyFauzia\Models\JoyPointsWallet;
use LadyFauzia\Models\ReferralCode;
use LadyFauzia\Models\Referral;

class CustomerListener
{
    /**
     * Handle customer registration event.
     */
    public function handle(Customer $customer): void
    {
        // 1. Generate unique referral code for the new customer
        $code = $this->generateUniqueReferralCode();
        ReferralCode::create([
            'customer_id' => $customer->id,
            'code'        => $code,
        ]);

        // 2. Award signup bonus points
        $signupPoints = (int) (core()->getConfigData('ladyfauzia.loyalty.points.signup_points') ?? 100);
        if ($signupPoints > 0) {
            JoyPointsWallet::adjustPoints(
                $customer->id,
                $signupPoints,
                'signup',
                "Welcome bonus points"
            );
        }

        // 3. Process referral registration if signup was via a referral code
        $referralCode = request()->header('X-Referral-Code') 
            ?: (request()->input('referral_code') ?: request()->input('referralCode'));

        if ($referralCode) {
            $referrerCode = ReferralCode::where('code', strtoupper($referralCode))->first();

            // Fraud check: referrer cannot refer themselves
            if ($referrerCode && $referrerCode->customer_id !== $customer->id) {
                // Check if already referred to avoid duplicate entry
                $exists = Referral::where('friend_email', $customer->email)->exists();

                if (! $exists) {
                    Referral::create([
                        'referrer_id'  => $referrerCode->customer_id,
                        'friend_email' => $customer->email,
                        'status'       => 'pending',
                    ]);
                }
            }
        }
    }

    /**
     * Generate a unique referral code.
     */
    protected function generateUniqueReferralCode(): string
    {
        do {
            $code = 'FAUZIA-' . strtoupper(Str::random(6));
        } while (ReferralCode::where('code', $code)->exists());

        return $code;
    }
}
