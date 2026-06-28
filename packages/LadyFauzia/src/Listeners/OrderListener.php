<?php

namespace LadyFauzia\Listeners;

use Webkul\Sales\Models\Order;
use LadyFauzia\Models\JoyPointsWallet;
use LadyFauzia\Models\CustomerVip;
use LadyFauzia\Models\Referral;

class OrderListener
{
    /**
     * Handle order save event.
     */
    public function handle(Order $order): void
    {
        // Only process loyalty rewards for registered customers
        if (! $order->customer_id) {
            return;
        }

        $spent = $order->base_sub_total - $order->base_discount_amount;
        if ($spent <= 0) {
            return;
        }

        // 1. Award loyalty points on purchase
        $earnRate = (float) (core()->getConfigData('ladyfauzia.loyalty.points.earn_rate') ?? 1);
        $points = (int) round($spent * $earnRate);

        if ($points > 0) {
            JoyPointsWallet::adjustPoints(
                $order->customer_id,
                $points,
                'purchase',
                "Earned points on order #{$order->increment_id}"
            );
        }

        // 2. Recalculate VIP tiers
        $vipRecord = CustomerVip::firstOrCreate(
            ['customer_id' => $order->customer_id],
            ['total_spend' => 0.00, 'current_tier' => 'Bronze']
        );

        $vipRecord->total_spend += $spent;

        $silverThreshold = (float) (core()->getConfigData('ladyfauzia.vip.thresholds.silver_spend') ?? 500);
        $goldThreshold = (float) (core()->getConfigData('ladyfauzia.vip.thresholds.gold_spend') ?? 1500);
        $eliteThreshold = (float) (core()->getConfigData('ladyfauzia.vip.thresholds.elite_spend') ?? 3000);

        $newTier = 'Bronze';
        if ($vipRecord->total_spend >= $eliteThreshold) {
            $newTier = 'Elite';
        } elseif ($vipRecord->total_spend >= $goldThreshold) {
            $newTier = 'Gold';
        } elseif ($vipRecord->total_spend >= $silverThreshold) {
            $newTier = 'Silver';
        }

        $vipRecord->current_tier = $newTier;
        $vipRecord->save();

        // 3. Process referral rewards if friend was referred
        $referral = Referral::where('friend_email', $order->customer_email)
            ->where('status', 'pending')
            ->first();

        if ($referral) {
            $referral->status = 'completed';
            $referral->order_id = $order->id;
            $referral->save();

            // Reward referrer
            $referrerReward = (int) (core()->getConfigData('ladyfauzia.referral.points.referrer_reward') ?? 200);
            if ($referrerReward > 0) {
                JoyPointsWallet::adjustPoints(
                    $referral->referrer_id,
                    $referrerReward,
                    'referral',
                    "Referral reward for inviting {$order->customer_email}"
                );
            }

            // Reward referred friend
            $referredReward = (int) (core()->getConfigData('ladyfauzia.referral.points.referred_reward') ?? 100);
            if ($referredReward > 0) {
                JoyPointsWallet::adjustPoints(
                    $order->customer_id,
                    $referredReward,
                    'referral',
                    "Referral bonus for order #{$order->increment_id}"
                );
            }
        }
    }
}
