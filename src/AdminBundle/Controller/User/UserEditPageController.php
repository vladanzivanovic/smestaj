<?php

declare(strict_types=1);

namespace AdminBundle\Controller\User;

use AdminBundle\Formatter\UserEditResponseFormatter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SiteBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

final class UserEditPageController extends AbstractController
{
    private UserEditResponseFormatter $responseFormatter;

    public function __construct(
        UserEditResponseFormatter $responseFormatter
    ) {
        $this->responseFormatter = $responseFormatter;
    }

    /**
     * @Route("/add-user", name="admin.add_user_page", methods={"GET"})
     * @Template("Admin/Pages/userEdit.html.twig")
     *
     * @return array
     */
    public function insert(): array
    {
        return [];
    }

    /**
     * @Route("/edit-user/{id}", name="admin.edit_user_page", methods={"GET"})
     * @Template("Admin/Pages/userEdit.html.twig")
     *
     * @param User $user
     *
     * @return array
     */
    public function update(User $user): array
    {
        return $this->responseFormatter->formatResponse($user);
    }
}
