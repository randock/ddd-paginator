<?php

declare(strict_types=1);

namespace Tests\Randock\DddPaginator\Unit\Dto;

use PHPUnit\Framework\TestCase;
use Randock\DddPaginator\Dto\PaginationRequest;

class PaginationRequestTest extends TestCase
{
    public const PAGINATION_REQUEST_TEST_PAGE = 1;

    public const PAGINATION_REQUEST_TEST_LIMIT = 500;

    public const PAGINATION_REQUEST_TEST_SORT = [
        'item' => 'ASC'
    ];

    public const PAGINATION_REQUEST_TEST_CRITERIA = [
        'item' => [
            'operator' => 'EQ',
            'value' => 'value',
        ]
    ];

    public function testGetters()
    {
        $paginationRequest = self::newPaginationRequest();

        $this->assertInstanceOf(PaginationRequest::class, $paginationRequest);
        $this->assertSame(self::PAGINATION_REQUEST_TEST_CRITERIA, $paginationRequest->getCriteria());
        $this->assertSame(self::PAGINATION_REQUEST_TEST_SORT, $paginationRequest->getSort());
        $this->assertSame(self::PAGINATION_REQUEST_TEST_LIMIT, $paginationRequest->getLimit());
        $this->assertSame(self::PAGINATION_REQUEST_TEST_PAGE, $paginationRequest->getPage());
    }

    /**
     * @param array|null $criteria
     * @param array|null $sort
     * @param int|null $limit
     * @param int|null $page
     * @return PaginationRequestTestClass
     */
    public static function newPaginationRequest(array $criteria = null, array $sort = null, int $limit = null, int $page = null): PaginationRequestTestClass
    {
        return new PaginationRequestTestClass(
            $criteria ?? self::PAGINATION_REQUEST_TEST_CRITERIA,
            $sort ?? self::PAGINATION_REQUEST_TEST_SORT,
            $limit ?? self::PAGINATION_REQUEST_TEST_LIMIT,
            $page ?? self::PAGINATION_REQUEST_TEST_PAGE
        );
    }
}
