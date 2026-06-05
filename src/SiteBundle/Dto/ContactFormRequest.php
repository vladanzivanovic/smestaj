<?php

declare(strict_types=1);

namespace SiteBundle\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class ContactFormRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 120)]
    public string $name = '';

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    public string $email = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 200)]
    public string $subject = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 5000)]
    public string $message = '';

    #[Assert\Blank]
    public string $website = '';
}
