<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Joy Points Wallets Table
        Schema::create('ladyfauzia_joy_points_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_id')->unique();
            $table->unsignedInteger('balance')->default(0);
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('cascade');
        });

        // 2. Joy Points Transactions Table
        Schema::create('ladyfauzia_joy_points_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_id');
            $table->integer('points'); // Positive for credits, negative for debits
            $table->string('type'); // e.g., 'signup', 'purchase', 'review', 'referral', 'manual'
            $table->string('description');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('cascade');
        });

        // 3. Customer VIP Spends & Tiers Table
        Schema::create('ladyfauzia_customer_vip', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_id')->unique();
            $table->string('current_tier')->default('Bronze'); // Bronze, Silver, Gold, Elite
            $table->decimal('total_spend', 12, 4)->default(0.0000);
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('cascade');
        });

        // 4. Referral Codes Table
        Schema::create('ladyfauzia_referral_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_id')->unique();
            $table->string('code')->unique();
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('cascade');
        });

        // 5. Referrals Tracking Table
        Schema::create('ladyfauzia_referrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('referrer_id');
            $table->string('friend_email');
            $table->string('status')->default('pending'); // pending, completed, rewarded
            $table->unsignedInteger('order_id')->nullable(); // order completed by friend
            $table->timestamps();

            $table->foreign('referrer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('cascade');

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ladyfauzia_referrals');
        Schema::dropIfExists('ladyfauzia_referral_codes');
        Schema::dropIfExists('ladyfauzia_customer_vip');
        Schema::dropIfExists('ladyfauzia_joy_points_transactions');
        Schema::dropIfExists('ladyfauzia_joy_points_wallets');
    }
};
