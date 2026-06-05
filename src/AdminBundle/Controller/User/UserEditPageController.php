<?php

declare(strict_types=1);

namespace AdminBundle\Controller\User;

use AdminBundle\Formatter\UserEditResponseFormatter;
use SiteBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserEditPageController extends AbstractController
{
    private UserEditResponseFormatter $responseFormatter;

    public function __construct(
        UserEditResponseFormatter $responseFormatter
    ) {
        $this->responseFormatter = $responseFormatter;
    }

    /**
     * @return Response
     */
    #[Route('/add-user', name: 'admin.add_user_page', methods: ['GET'])]
    public function insert(): Response
    {
        return $this->render('@Admin/Pages/userEdit.html.twig', []);
    }

    /**
     * @param User $user
     *
     * @return Response
     */
    #[Route('/edit-user/{id}', name: 'admin.edit_user_page', methods: ['GET'])]
    public function update(User $user): Response
    {
        return $this->render('@Admin/Pages/userEdit.html.twig', $this->responseFormatter->formatResponse($user));
    }
}
