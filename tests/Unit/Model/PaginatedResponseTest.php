<?php

declare(strict_types=1);

namespace Tests\Randock\DddPaginator\Unit\Model;

use PHPUnit\Framework\TestCase;
use Randock\DddPaginator\Model\PaginatedResponse;

class PaginatedResponseTest extends TestCase
{
    public const PAGINATED_RESPONSE_TEST_PAGE = 1;

    public const PAGINATED_RESPONSE_TEST_LIMIT = 20;

    public const PAGINATED_RESPONSE_TEST_NUM_PAGES = 2;

    public const PAGINATED_RESPONSE_TEST_TOTAL = 40;

    public const PAGINATED_RESPONSE_TEST_ITEMS = [];

    public function testGetters()
    {
        $paginatedResponse = self::newPaginatedResponse();

        $this->assertInstanceOf(PaginatedResponse::class, $paginatedResponse);
        $this->assertSame(self::PAGINATED_RESPONSE_TEST_PAGE, $paginatedResponse->getPage());
        $this->assertSame(self::PAGINATED_RESPONSE_TEST_LIMIT, $paginatedResponse->getLimit());
        $this->assertSame(self::PAGINATED_RESPONSE_TEST_NUM_PAGES, $paginatedResponse->getNumPages());
        $this->assertSame(self::PAGINATED_RESPONSE_TEST_TOTAL, $paginatedResponse->getTotal());
        $this->assertSame(self::PAGINATED_RESPONSE_TEST_ITEMS, $paginatedResponse->getItems());
    }

    /**
     * @param int|null   $page
     * @param int|null   $limit
     * @param int|null   $numPages
     * @param int|null   $total
     * @param array|null $items
     *
     * @return PaginatedResponse
     */
    public static function newPaginatedResponse(int $page = null, int $limit = null, int $numPages = null, int $total = null, array $items = null): PaginatedResponse
    {
        return new PaginatedResponse(
            $page ?? self::PAGINATED_RESPONSE_TEST_PAGE,
            $limit ?? self::PAGINATED_RESPONSE_TEST_LIMIT,
            $numPages ?? self::PAGINATED_RESPONSE_TEST_NUM_PAGES,
            $total ?? self::PAGINATED_RESPONSE_TEST_TOTAL,
            $items ?? self::PAGINATED_RESPONSE_TEST_ITEMS
        );
    }
}
