<?php

namespace LadyFauzia\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class InviteFriendInput
{
    #[Groups(['mutation'])]
    public ?string $friendEmail = null;
}
