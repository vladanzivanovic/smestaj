<?php

declare(strict_types=1);

namespace AdminBundle\Model;

use SiteBundle\Entity\AdsInfoPage;

/**
 * Supplementary payload produced by InfoPageEditRequestParser.
 *
 * Scalar fields are written onto $entity directly by the parser; this DTO
 * carries only the data the handler must reconcile after parsing — namely
 * the tag rows, per-row image states (id + isMain + originalFilePath +
 * deleted), and the requested-publish flag (which must be revalidated
 * against the publish group).
 */
final class InfoPageEditModel
{
    /**
     * @param array<string, array<int, string>> $tags          Shape: [<tag_type_label>][<tag_id>] => <value-string>.
     *                                                          Range-type values are meter strings; non-range are '1'.
     * @param array<int, array{id: ?int, isMain: bool, fileName: ?string, originalFilePath: ?string, deleted: bool}> $imageStates
     * @param array<string, string> $parserViolations Field-name => human-readable Serbian message for shape-level
     *                                                rejections produced by InfoPageEditRequestParser before any
     *                                                domain validation runs. Surfaced in the same 422 envelope
     *                                                as handler violations.
     */
    public function __construct(
        public readonly AdsInfoPage $entity,
        public readonly bool $requestedPublish,
        public readonly array $tags,
        public readonly array $imageStates,
        public readonly array $parserViolations = [],
    ) {
    }
}
