<?php

namespace LadyFauzia\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductReview;
use Webkul\Sales\Models\Order;
use LadyFauzia\Models\JoyPointsWallet;
use LadyFauzia\Models\JoyPointsTransaction;
use LadyFauzia\Models\CustomerVip;
use LadyFauzia\Models\ReferralCode;
use LadyFauzia\Models\Referral;

class LadyFauziaTestLoyalty extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ladyfauzia:test-loyalty';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $signature_description = 'Test Lady Fauzia Loyalty Engine, VIP Levels, and Referral System.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('==================================================');
        $this->info('   LADY FAUZIA CO. LOYALTY ENGINE TESTING   ');
        $this->info('==================================================');

        DB::beginTransaction();

        try {
            // -----------------------------------------------------------------
            // TEST 1: Account Creation & Signup Bonus & Referral Code Generation
            // -----------------------------------------------------------------
            $this->comment('Running Test 1: Account Creation & Welcome Bonus...');

            $email = 'test_' . uniqid() . '@ladyfauzia.com';
            $customer1 = Customer::create([
                'first_name' => 'Fauzia',
                'last_name'  => 'Test',
                'email'      => $email,
                'password'   => bcrypt('password123'),
                'status'     => 1,
            ]);

            // Dispatch customer registration event
            Event::dispatch('customer.registration.after', $customer1);

            // Assertions
            $wallet = JoyPointsWallet::where('customer_id', $customer1->id)->first();
            if (! $wallet || $wallet->balance !== 100) {
                throw new \Exception("Test 1 Failed: Wallet balance for new customer should be 100, got: " . ($wallet ? $wallet->balance : 'null'));
            }

            $refCodeModel = ReferralCode::where('customer_id', $customer1->id)->first();
            if (! $refCodeModel || empty($refCodeModel->code)) {
                throw new \Exception("Test 1 Failed: Referral code was not generated.");
            }

            $this->info("✓ Test 1 Passed! Customer created, referral code generated: {$refCodeModel->code}, welcome points credited: {$wallet->balance}");

            // -----------------------------------------------------------------
            // TEST 2: Order saves & points accumulation & VIP Upgrade
            // -----------------------------------------------------------------
            $this->comment('Running Test 2: Order Points Award & VIP Tier Calculations...');

            // Mock an order
            $order = Order::create([
                'increment_id'          => 'TEST-' . rand(100000, 999999),
                'status'                => 'completed',
                'customer_id'           => $customer1->id,
                'customer_email'        => $customer1->email,
                'customer_first_name'   => $customer1->first_name,
                'customer_last_name'    => $customer1->last_name,
                'base_sub_total'        => 650.00,
                'base_discount_amount'  => 50.00,
                'grand_total'           => 600.00,
                'base_grand_total'      => 600.00,
                'total_item_count'      => 1,
                'total_qty_ordered'     => 1,
                'channel_name'          => 'Default',
            ]);

            \Webkul\Sales\Models\OrderPayment::create([
                'order_id'     => $order->id,
                'method'       => 'cashondelivery',
                'method_title' => 'Cash On Delivery',
            ]);

            // Dispatch order saved event
            Event::dispatch('checkout.order.save.after', $order);

            // Assertions
            $wallet->refresh();
            // Net spent = 650 - 50 = 600. Point earn rate = 1.
            // Expected balance = 100 (signup) + 600 (purchase) = 700.
            if ($wallet->balance !== 700) {
                throw new \Exception("Test 2 Failed: Wallet points should be 700, got: {$wallet->balance}");
            }

            $vip = CustomerVip::where('customer_id', $customer1->id)->first();
            if (! $vip) {
                throw new \Exception("Test 2 Failed: VIP record not found for customer.");
            }

            // Total spend = 600. Silver threshold is 500. Expected tier = 'Silver'.
            if ($vip->current_tier !== 'Silver') {
                throw new \Exception("Test 2 Failed: VIP tier should be Silver, got: {$vip->current_tier}");
            }

            $this->info("✓ Test 2 Passed! Points awarded on purchase. VIP tier upgraded to {$vip->current_tier} based on spend: {$vip->total_spend}");

            // -----------------------------------------------------------------
            // TEST 3: Product Review Approval points
            // -----------------------------------------------------------------
            $this->comment('Running Test 3: Product Review Approved Points Award...');

            $product = Product::first();
            if ($product) {
                $review = ProductReview::create([
                    'name'        => $customer1->first_name,
                    'title'       => 'Gorgeous Kaftan',
                    'comment'     => 'Absolute luxury and comfort. Extremely modest.',
                    'status'      => 'pending',
                    'rating'      => 5,
                    'product_id'  => $product->id,
                    'customer_id' => $customer1->id,
                ]);

                // Approve review and dispatch update event
                $review->status = 'approved';
                $review->save();
                Event::dispatch('customer.review.update.after', $review);

                // Assertions
                $wallet->refresh();
                // Expected points = 700 + 50 (review points) = 750.
                if ($wallet->balance !== 750) {
                    throw new \Exception("Test 3 Failed: Wallet points should be 750 after approved review, got: {$wallet->balance}");
                }

                $this->info("✓ Test 3 Passed! Review points (50) credited successfully upon admin approval.");
            } else {
                $this->warn('! Test 3 Skipped: No products found in database to attach review to.');
            }

            // -----------------------------------------------------------------
            // TEST 4: Referrals Invitation and Reward
            // -----------------------------------------------------------------
            $this->comment('Running Test 4: Referral Flow & Invite Friend Verification...');

            // Clear any referral_code in request to simulate
            request()->merge(['referral_code' => $refCodeModel->code]);

            $friendEmail = 'friend_' . uniqid() . '@ladyfauzia.com';
            $customer2 = Customer::create([
                'first_name' => 'Friend',
                'last_name'  => 'Test',
                'email'      => $friendEmail,
                'password'   => bcrypt('password123'),
                'status'     => 1,
            ]);

            // Dispatch customer registration
            Event::dispatch('customer.registration.after', $customer2);

            // Assertions for referral registration
            $referral = Referral::where('friend_email', $friendEmail)->first();
            if (! $referral || $referral->referrer_id !== $customer1->id || $referral->status !== 'pending') {
                throw new \Exception("Test 4 Failed: Pending referral record was not created correctly.");
            }

            // Mock friend making a purchase
            $friendOrder = Order::create([
                'increment_id'          => 'TEST-' . rand(100000, 999999),
                'status'                => 'completed',
                'customer_id'           => $customer2->id,
                'customer_email'        => $customer2->email,
                'customer_first_name'   => $customer2->first_name,
                'customer_last_name'    => $customer2->last_name,
                'base_sub_total'        => 100.00,
                'base_discount_amount'  => 0.00,
                'grand_total'           => 100.00,
                'base_grand_total'      => 100.00,
                'total_item_count'      => 1,
                'total_qty_ordered'     => 1,
                'channel_name'          => 'Default',
            ]);

            \Webkul\Sales\Models\OrderPayment::create([
                'order_id'     => $friendOrder->id,
                'method'       => 'cashondelivery',
                'method_title' => 'Cash On Delivery',
            ]);

            // Dispatch order saved for friend
            Event::dispatch('checkout.order.save.after', $friendOrder);

            // Assertions
            $referral->refresh();
            if ($referral->status !== 'completed' || $referral->order_id !== $friendOrder->id) {
                throw new \Exception("Test 4 Failed: Referral status should be completed and order_id set.");
            }

            $wallet->refresh(); // Referrer's wallet
            // Expected referrer balance: 750 (or 700 if review skipped) + 200 (referral referrer reward) = 950 (or 900)
            $expectedReferrerBalance = $product ? 950 : 900;
            if ($wallet->balance !== $expectedReferrerBalance) {
                throw new \Exception("Test 4 Failed: Referrer wallet should have {$expectedReferrerBalance} points, got: {$wallet->balance}");
            }

            $friendWallet = JoyPointsWallet::where('customer_id', $customer2->id)->first();
            // Expected friend balance: 100 (signup) + 100 (purchase) + 100 (referred friend bonus) = 300
            if (! $friendWallet || $friendWallet->balance !== 300) {
                throw new \Exception("Test 4 Failed: Referred friend wallet should have 300 points, got: " . ($friendWallet ? $friendWallet->balance : 'null'));
            }

            $this->info("✓ Test 4 Passed! Referral code matched, friend invited, pending record created, and rewards distributed upon purchase.");

            $this->info('==================================================');
            $this->info('   ALL LOYALTY ENGINE TESTS PASSED SUCCESSFULLY!   ');
            $this->info('==================================================');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Verification Failed: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }

        DB::rollBack(); // Always rollback test data to keep database clean
        return 0;
    }
}
