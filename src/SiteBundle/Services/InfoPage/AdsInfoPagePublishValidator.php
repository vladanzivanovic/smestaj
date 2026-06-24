<?php

declare(strict_types=1);

namespace SiteBundle\Services\InfoPage;

/*
 * Publish validation contract for AdsInfoPage.
 *
 * Extension point: add new constraints with `groups: ['publish']` to AdsInfoPage
 * (or any related entity) and they will automatically participate here.
 */

use SiteBundle\Entity\AdsInfoPage;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AdsInfoPagePublishValidator
{
    public function __construct(
        private readonly ValidatorInterface $validator
    ) {
    }

    /**
     * @return array<string, string> field path => first violation message
     */
    public function validate(AdsInfoPage $entity): array
    {
        $violations = $this->validator->validate($entity, null, ['publish']);

        $result = [];

        foreach ($violations as $violation) {
            $path = $violation->getPropertyPath();

            if (true === array_key_exists($path, $result)) {
                continue;
            }

            $result[$path] = (string) $violation->getMessage();
        }

        return $result;
    }
}
