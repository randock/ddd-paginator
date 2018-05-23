<?php

declare(strict_types=1);

namespace Tests\Randock\DddPaginator\Unit\Dto;

use Randock\DddPaginator\Dto\PaginationRequest;

class PaginationRequestTestClass extends PaginationRequest
{
    /**
     * PaginationRequestTestClass constructor.
     *
     * @param array    $criteria
     * @param array    $sort
     * @param int|null $limit
     * @param int|null $page
     */
    public function __construct(array $criteria = [], array $sort = [], int $limit = null, int $page = null)
    {
        parent::__construct($criteria, $sort, $limit, $page);
    }
}
