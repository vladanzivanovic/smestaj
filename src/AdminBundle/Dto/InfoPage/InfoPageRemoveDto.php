<?php

declare(strict_types=1);

namespace AdminBundle\Dto\InfoPage;

use Symfony\Component\Validator\Constraints as Assert;

final class InfoPageRemoveDto
{
    #[Assert\EqualTo(value: 'obriši', message: 'Otkucajte tačno "obriši" za potvrdu brisanja.')]
    public string $confirm = '';
}
