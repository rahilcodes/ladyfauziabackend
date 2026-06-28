<?php

namespace LadyFauzia\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use LadyFauzia\Dto\InviteFriendInput;
use LadyFauzia\Dto\InviteFriendOutput;
use LadyFauzia\Models\Referral;

class InviteFriendProcessor implements ProcessorInterface
{
    /**
     * Process the friend invitation.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): InviteFriendOutput
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        if (! ($data instanceof InviteFriendInput)) {
            $output = new InviteFriendOutput();
            $output->success = false;
            $output->message = 'Invalid input parameters.';
            return $output;
        }

        $validator = Validator::make([
            'friend_email' => $data->friendEmail,
        ], [
            'friend_email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            throw new InvalidInputException(implode(' ', $validator->errors()->all()));
        }

        // Fraud prevention checks
        // 1. Self-referral
        if (strtolower($customer->email) === strtolower($data->friendEmail)) {
            $output = new InviteFriendOutput();
            $output->success = false;
            $output->message = 'You cannot refer yourself.';
            return $output;
        }

        // 2. Already a customer
        $friendExists = \Webkul\Customer\Models\Customer::where('email', $data->friendEmail)->exists();
        if ($friendExists) {
            $output = new InviteFriendOutput();
            $output->success = false;
            $output->message = 'This email is already registered as a Lady Fauzia member.';
            return $output;
        }

        // 3. Already invited
        $alreadyReferred = Referral::where('friend_email', $data->friendEmail)->exists();
        if ($alreadyReferred) {
            $output = new InviteFriendOutput();
            $output->success = false;
            $output->message = 'This friend has already been referred.';
            return $output;
        }

        // Create referral record
        Referral::create([
            'referrer_id'  => $customer->id,
            'friend_email' => strtolower($data->friendEmail),
            'status'       => 'pending',
        ]);

        $output = new InviteFriendOutput();
        $output->success = true;
        $output->message = 'Invitation successfully sent! Once your friend places an order, your reward points will be credited.';

        return $output;
    }
}
