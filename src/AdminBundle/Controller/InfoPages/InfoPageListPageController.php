<?php

declare(strict_types=1);

namespace AdminBundle\Controller\InfoPages;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class InfoPageListPageController extends AbstractController
{
    #[Route('/info-pages', name: 'admin.info_pages', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('@Admin/Pages/infoPages.html.twig', [
            'routeName' => 'admin.info_pages',
        ]);
    }
}
