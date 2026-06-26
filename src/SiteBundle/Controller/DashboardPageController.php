<?php

declare(strict_types=1);

namespace SiteBundle\Controller;

use SiteBundle\Collector\DashboardCollector;
use SiteBundle\Formatter\DashboardFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardPageController extends AbstractController
{
    private DashboardCollector $dashboardCollector;

    private DashboardFormatter $dashboardFormatter;

    public function __construct(
        DashboardCollector $dashboardCollector,
        DashboardFormatter $dashboardFormatter
    ) {
        $this->dashboardCollector = $dashboardCollector;
        $this->dashboardFormatter = $dashboardFormatter;
    }

    #[Route('/korisnicki-profil', name: 'site_user_profile', methods: ['GET'])]
    public function index(): Response
    {
        $data = $this->dashboardCollector->collect();

        return $this->render('@Site/Site/userProfile.html.twig', $this->dashboardFormatter->format($data));
    }
}