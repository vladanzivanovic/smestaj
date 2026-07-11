<?php

declare(strict_types=1);

namespace AdminBundle\Dto\Embedded;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Shared query DTO for the three DataTables server-side list endpoints
 * (`InfoPageListController`, `UserListController`, `ProductListController`).
 * Field names mirror the DataTables 1.10+ wire protocol
 * (<https://datatables.net/manual/server-side>), and both `columns` /
 * `order` / `search` are kept as loose associative arrays because
 * DataTables emits nested bracket-notation keys
 * (`columns[0][data]`, `order[0][column]`, `search[value]`) that a
 * strongly-typed sub-DTO would need to shred column-by-column — the
 * existing DataTableRequestParser only consumes `order[0]` and
 * `search[value]`, so tight typing offers no return.
 */
final class DataTableQueryDto
{
    #[Assert\PositiveOrZero]
    public int $draw = 0;

    #[Assert\PositiveOrZero]
    public int $start = 0;

    #[Assert\PositiveOrZero]
    public int $length = 0;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $columns = [];

    /**
     * @var array<int, array{column: int|string, dir: string}>
     */
    public array $order = [];

    /**
     * @var array{value?: string, regex?: string}
     */
    public array $search = [];
}
