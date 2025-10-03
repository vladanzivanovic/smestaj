<?php

namespace SiteBundle\Controller;


class EventtypeController extends SiteController
{
    public function getAllAction()
    {
        return $this->jsonResponse->setData($this->setEntity()->getAll());
    }
}
