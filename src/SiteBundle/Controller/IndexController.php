<?php

declare(strict_types=1);

namespace SiteBundle\Controller;

use SiteBundle\Dto\Index\IndexRequest;
use SiteBundle\Entity\User;
use SiteBundle\Repository\AdsRepository;
use SiteBundle\Repository\CityRepository;
use SiteBundle\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


final class IndexController extends AbstractController
{
    private AdsRepository $adsRepository;
    private CityRepository $cityRepository;
    private UserRepository $userRepository;
    private RouterInterface $router;
    private TranslatorInterface $translator;

    public function __construct(
        AdsRepository $adsRepository,
        CityRepository $cityRepository,
        UserRepository $userRepository,
        RouterInterface $router,
        TranslatorInterface $translator
    ) {
        $this->adsRepository = $adsRepository;
        $this->cityRepository = $cityRepository;
        $this->userRepository = $userRepository;
        $this->router = $router;
        $this->translator = $translator;
    }

    /**
     * @return RedirectResponse|Response
     */
    public function indexAction(#[MapQueryString] IndexRequest $query): RedirectResponse|Response
    {
        $recommended = $this->adsRepository->getPayed(15);
        $cities = $this->cityRepository->getForIndex();

        $token = $query->token;

        if (!empty($token)) {
            /** @var User $user */
            $user = $this->userRepository->findOneBy(['token' => $token]);
            if (!$user instanceof User) {
                $this->addFlash('message', $this->translator->trans('reset_password_token_not_valid'));
                return new RedirectResponse($this->router->generate('site_index'));
            }
            $tokenDate = $user->getTokenValid();
            $diff = $tokenDate->diff(new \DateTime())->format('%a');

            if ($diff > 7) {
                return new RedirectResponse($this->router->generate('site_index'));
            }
        }

        return $this->render('@Site/Site/index.html.twig', array(
            'recommended' => $recommended,
            'cities' => $cities
        ));
    }
}
