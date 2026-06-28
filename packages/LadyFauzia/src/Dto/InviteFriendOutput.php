<?php

namespace LadyFauzia\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class InviteFriendOutput
{
    #[Groups(['mutation'])]
    public bool $success = false;

    #[Groups(['mutation'])]
    public ?string $message = null;
}
