<?php

namespace LadyFauzia\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Illuminate\Support\Facades\Auth;
use Webkul\BagistoApi\Exception\AuthorizationException;
use LadyFauzia\Models\CustomerLoyalty;
use LadyFauzia\Models\JoyPointsWallet;
use LadyFauzia\Models\CustomerVip;
use LadyFauzia\Models\ReferralCode;
use LadyFauzia\Models\JoyPointsTransaction;
use LadyFauzia\Models\JoyPointsTransactionDto;

class CustomerLoyaltyQueryResolver implements QueryItemResolverInterface
{
    public function __invoke(?object $item, array $context): CustomerLoyalty
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        // 1. Get points balance
        $wallet = JoyPointsWallet::where('customer_id', $customer->id)->first();
        $points = $wallet ? (int) $wallet->balance : 0;

        // 2. Get VIP status
        $vip = CustomerVip::where('customer_id', $customer->id)->first();
        $totalSpend = $vip ? (float) $vip->total_spend : 0.00;
        $vipTier = $vip ? $vip->current_tier : 'Bronze';

        // thresholds
        $silverSpend = (float) (core()->getConfigData('ladyfauzia.vip.thresholds.silver_spend') ?? 500);
        $goldSpend = (float) (core()->getConfigData('ladyfauzia.vip.thresholds.gold_spend') ?? 1500);
        $eliteSpend = (float) (core()->getConfigData('ladyfauzia.vip.thresholds.elite_spend') ?? 3000);

        $nextTierThreshold = 0.00;
        $progressPercent = 0.00;
        $benefits = '';

        if ($vipTier === 'Elite') {
            $nextTierThreshold = $eliteSpend;
            $progressPercent = 100.00;
            $benefits = "Elite Member. 20% points multiplier bonus. Free shipping on all orders. Early access to new collections. Dedicated styling consultant.";
        } elseif ($vipTier === 'Gold') {
            $nextTierThreshold = $eliteSpend;
            $progressPercent = $goldSpend != $eliteSpend ? (($totalSpend - $goldSpend) / ($eliteSpend - $goldSpend)) * 100 : 100.00;
            $benefits = "Gold Member. 10% points multiplier bonus. Free shipping on all orders. Early access to new collections.";
        } elseif ($vipTier === 'Silver') {
            $nextTierThreshold = $goldSpend;
            $progressPercent = $silverSpend != $goldSpend ? (($totalSpend - $silverSpend) / ($goldSpend - $silverSpend)) * 100 : 100.00;
            $benefits = "Silver Member. 5% points multiplier bonus. Free shipping on orders over $100.";
        } else {
            $nextTierThreshold = $silverSpend;
            $progressPercent = $silverSpend > 0 ? ($totalSpend / $silverSpend) * 100 : 100.00;
            $benefits = "Welcome to Lady Fauzia Loyalty. Enjoy earning points on purchases and referrals.";
        }

        $progressPercent = min(100.00, max(0.00, $progressPercent));

        // 3. Referral info
        $refCodeModel = ReferralCode::where('customer_id', $customer->id)->first();
        $referralCode = $refCodeModel ? $refCodeModel->code : '';
        $referralLink = $referralCode ? "/register?ref={$referralCode}" : "";

        // 4. Map transactions
        $transactions = [];
        $customerTransactions = JoyPointsTransaction::where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($customerTransactions as $tx) {
            $dto = new JoyPointsTransactionDto();
            $dto->id = $tx->id;
            $dto->points = $tx->points;
            $dto->type = $tx->type;
            $dto->description = $tx->description;
            $dto->createdAt = $tx->created_at ? $tx->created_at->toIso8601String() : null;
            
            $transactions[] = $dto;
        }

        // 5. Build return object
        $loyalty = new CustomerLoyalty();
        $loyalty->id = (string) $customer->id;
        $loyalty->joyPointsBalance = $points;
        $loyalty->vipTierName = $vipTier;
        $loyalty->totalSpend = $totalSpend;
        $loyalty->progressPercent = round($progressPercent, 2);
        $loyalty->nextTierThreshold = $nextTierThreshold;
        $loyalty->benefits = $benefits;
        $loyalty->referralCode = $referralCode;
        $loyalty->referralLink = $referralLink;
        $loyalty->transactions = $transactions;

        return $loyalty;
    }
}
