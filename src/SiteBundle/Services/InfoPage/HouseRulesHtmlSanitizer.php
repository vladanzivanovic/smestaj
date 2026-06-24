<?php

declare(strict_types=1);

namespace SiteBundle\Services\InfoPage;

use HTMLPurifier;
use HTMLPurifier_Config;

final class HouseRulesHtmlSanitizer
{
    /*
     * Allow-list mirrors the Summernote toolbar `[styleOptions, fontOptions]`
     * registered in src/AdminBundle/Resources/public/js/Services/SummerNote.js:67-68
     * and re-used by InfoPageEditController.js (Phase F, step 11b of the plan).
     * Any change to that toolbar (e.g. enabling lists, headings, or links)
     * MUST update HTML.Allowed below in lockstep — otherwise the editor will
     * render rich markup that the sanitizer silently strips on save.
     */
    private const ALLOWED_HTML = 'p,br,b,strong,i,em,u,s,strike,sup,sub';

    public function __construct(
        private readonly HTMLPurifier $purifier,
    ) {
    }

    public function sanitize(?string $html): ?string
    {
        if (null === $html) {
            return null;
        }

        $trimmed = trim($html);

        if ('' === $trimmed) {
            return null;
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', self::ALLOWED_HTML);
        $config->set('Cache.DefinitionImpl', null);

        $purified = trim($this->purifier->purify($trimmed, $config));

        if ('' === $purified) {
            return null;
        }

        return $purified;
    }
}
