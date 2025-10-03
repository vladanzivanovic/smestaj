<?php

declare(strict_types=1);

namespace SiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SiteBundle\Collector\DashboardCollector;
use SiteBundle\Formatter\DashboardFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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

    /**
     * @Route("/korisnicki-profil", name="site_user_profile", methods={"GET"})
     * @Template("@Site/Site/userProfile.html.twig")
     * @return array
     */
    public function index(Request $request): array
    {
        $data = $this->dashboardCollector->collect();

        return $this->dashboardFormatter->format($data);
    }
}