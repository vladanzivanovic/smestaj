<?php

declare(strict_types=1);

namespace SiteBundle\Dto;

use SiteBundle\Dto\Contact\ContactRequestError;
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

    public string $website = '';

    public ?ContactRequestError $error = null;
}
