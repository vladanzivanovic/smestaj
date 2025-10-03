<?php

declare(strict_types=1);

namespace SiteBundle\View;

use SiteBundle\Entity\Contact;

final class ContactView
{
    public function view(Contact $contact): array
    {
        $view = [
            'id' => $contact->getId(),
            'first_name' => $contact->getFirstname(),
            'last_name' => $contact->getLastname(),
            'email' => $contact->getContactEmail(),
            'telephone' => [
                'classic' => $contact->getTelephone(),
                'mobile' => $contact->getMobilePhone(),
                'viber' => $contact->getViber(),
            ]
        ];

        return $view;
    }
}
