<?php

namespace SiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SiteBundle\Constants\MessageConstants;
use SiteBundle\Entity\User;
use SiteBundle\Exceptions\ApplicationException;
use SiteBundle\Repository\AdsRepository;
use SiteBundle\Repository\CityRepository;
use SiteBundle\Repository\UserRepository;
use SiteBundle\Services\ContactService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


class IndexController extends AbstractController
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
     * @Template("@Site/Site/index.html.twig")
     * @param Request $request
     *
     * @return RedirectResponse|array
     */
    public function indexAction(Request $request)
    {
        $recommended = $this->adsRepository->getPayed(15);
        $cities = $this->cityRepository->getForIndex();

        $token = $request->query->get('token');

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

        return array(
            'recommended' => $recommended,
            'cities' => $cities
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ApplicationException
     */
    public function sendContactFormAction(Request $request)
    {
        if(!($jsonData = $this->requestToArray($request))) {
            throw new ApplicationException(MessageConstants::APPLICATION_ERROR);
        }

        /** @var ContactService $contactService */
        $contactService = $this->setService('site.contact_us_service');

        return $this->jsonResponse->setContent($contactService->sendContactEmail($jsonData));
    }
}
