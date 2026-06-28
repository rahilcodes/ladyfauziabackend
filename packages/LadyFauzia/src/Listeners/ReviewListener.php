<?php

namespace LadyFauzia\Listeners;

use LadyFauzia\Models\JoyPointsWallet;
use LadyFauzia\Models\JoyPointsTransaction;

class ReviewListener
{
    /**
     * Handle product review update event.
     */
    public function handle($review): void
    {
        // Only reward points if the review is approved and belongs to a registered customer
        if (! isset($review->status) || $review->status !== 'approved' || ! isset($review->customer_id) || ! $review->customer_id) {
            return;
        }

        // Avoid duplicate points awarding for the same review
        $uniqueDescription = "Approved product review bonus (Review #{$review->id})";
        
        $alreadyAwarded = JoyPointsTransaction::where('customer_id', $review->customer_id)
            ->where('type', 'review')
            ->where('description', $uniqueDescription)
            ->exists();

        if ($alreadyAwarded) {
            return;
        }

        // Award review points
        $reviewPoints = (int) (core()->getConfigData('ladyfauzia.loyalty.points.review_points') ?? 50);
        if ($reviewPoints > 0) {
            JoyPointsWallet::adjustPoints(
                $review->customer_id,
                $reviewPoints,
                'review',
                $uniqueDescription
            );
        }
    }
}
