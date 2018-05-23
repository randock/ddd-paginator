<?php

declare(strict_types=1);

namespace Tests\Randock\DddPaginator\Unit\DataTransformer;

use PHPUnit\Framework\TestCase;
use Randock\DddPaginator\Model\PaginatedResponse;
use Tests\Randock\DddPaginator\Unit\Traits\PagerfantaTestTrait;
use Tests\Randock\DddPaginator\Unit\Traits\DataTransformerTrait;
use Randock\DddPaginator\DataTransformer\PaginatedDataTransformer;

class PaginatedDataTransformerTest extends TestCase
{
    use DataTransformerTrait;
    use PagerfantaTestTrait;

    public const PAGINATED_DATA_TRANSFORMER_PAGE = 1;

    public const PAGINATED_DATA_TRANSFORMER_LIMIT = 20;

    public const PAGINATED_DATA_TRANSFORMER_TOTAL = 40;

    public const PAGINATED_DATA_TRANSFORMER_NUM_PAGE = 2;

    /**
     * @var PaginatedDataTransformer
     */
    private $paginatedDataTransformer;

    protected function setUp()
    {
        parent::setUp();
        $this->paginatedDataTransformer = new PaginatedDataTransformer($this->dataTransformerInterface());
    }

    public function testTransform()
    {
        $item['key'] = 'value';
        $items = [
            (object) $item,
        ];

        $this->shouldCallGetCurrentPageResultsOnPagerfanta($items);
        $this->shouldCallTransformOnDataTransformerInterface();
        $this->shouldCallGetCurrentPageOnPagerfanta(self::PAGINATED_DATA_TRANSFORMER_PAGE);
        $this->shouldCallGetMaxPerPageOnPagerfanta(self::PAGINATED_DATA_TRANSFORMER_LIMIT);
        $this->shouldCallGetNbResultsOnPagerfanta(self::PAGINATED_DATA_TRANSFORMER_TOTAL);
        $this->shouldCallGetNbPagesOnPagerfanta(self::PAGINATED_DATA_TRANSFORMER_NUM_PAGE);

        $paginatedResponse = $this->paginatedDataTransformer->transform($this->pagerfanta());

        $this->assertInstanceOf(PaginatedResponse::class, $paginatedResponse);
        $this->assertSame(self::PAGINATED_DATA_TRANSFORMER_PAGE, $paginatedResponse->getPage());
        $this->assertSame(self::PAGINATED_DATA_TRANSFORMER_LIMIT, $paginatedResponse->getLimit());
        $this->assertSame(self::PAGINATED_DATA_TRANSFORMER_NUM_PAGE, $paginatedResponse->getNumPages());
        $this->assertSame(self::PAGINATED_DATA_TRANSFORMER_TOTAL, $paginatedResponse->getTotal());
        $this->assertSame(gettype([]), gettype($paginatedResponse->getItems()));
    }
}
