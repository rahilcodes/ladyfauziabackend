<?php

namespace LadyFauzia\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use LadyFauzia\Dto\InviteFriendInput;
use LadyFauzia\Dto\InviteFriendOutput;
use LadyFauzia\State\Processor\InviteFriendProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
        new Post(
            uriTemplate: '/loyalty/invite',
            input: InviteFriendInput::class,
            output: InviteFriendOutput::class,
            processor: InviteFriendProcessor::class,
            denormalizationContext: [
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            description: 'Invite a friend by email to earn loyalty points.',
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: InviteFriendInput::class,
            output: InviteFriendOutput::class,
            processor: InviteFriendProcessor::class,
            denormalizationContext: [
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            description: 'Invite a friend by email to earn loyalty points.',
        ),
    ]
)]
class InviteFriend
{
    #[ApiProperty(readable: true, writable: false, identifier: true)]
    public ?int $id = null;

    #[ApiProperty(readable: true, writable: false)]
    public bool $success = false;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $message = null;
}
