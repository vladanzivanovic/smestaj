<?php

declare(strict_types=1);

namespace SiteBundle\Controller;


final class EventtypeController extends SiteController
{
    public function getAllAction()
    {
        return $this->jsonResponse->setData($this->setEntity()->getAll());
    }
}
