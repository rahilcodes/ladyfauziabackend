<?php

namespace LadyFauzia\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Query;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    routePrefix: '/api/shop',
    uriTemplate: '/customer-loyalty',
    shortName: 'CustomerLoyalty',
    paginationEnabled: false,
    operations: [],
    graphQlOperations: [
        new Query(
            name: 'read',
            resolver: \LadyFauzia\Resolver\CustomerLoyaltyQueryResolver::class,
            args: [],
            normalizationContext: [
                'groups' => ['query'],
            ],
            description: 'Read authenticated customer loyalty points, VIP status, and referral info.',
        ),
    ]
)]
class CustomerLoyalty
{
    #[ApiProperty(readable: true, writable: false, identifier: true)]
    #[Groups(['query'])]
    public ?string $id = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?int $joyPointsBalance = 0;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $vipTierName = 'Bronze';

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?float $totalSpend = 0.00;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?float $progressPercent = 0.00;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?float $nextTierThreshold = 0.00;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $benefits = '';

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $referralCode = '';

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $referralLink = '';

    /**
     * @var \LadyFauzia\Models\JoyPointsTransactionDto[]
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?array $transactions = [];
}
