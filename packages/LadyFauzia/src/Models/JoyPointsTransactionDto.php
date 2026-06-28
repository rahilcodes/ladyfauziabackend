<?php

namespace LadyFauzia\Models;

use Symfony\Component\Serializer\Annotation\Groups;

class JoyPointsTransactionDto
{
    #[Groups(['query'])]
    public ?int $id = null;

    #[Groups(['query'])]
    public ?int $points = null;

    #[Groups(['query'])]
    public ?string $type = null;

    #[Groups(['query'])]
    public ?string $description = null;

    #[Groups(['query'])]
    public ?string $createdAt = null;
}
