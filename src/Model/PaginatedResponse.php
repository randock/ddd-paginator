<?php

declare(strict_types=1);

namespace Randock\DddPaginator\Model;

class PaginatedResponse
{
    /**
     * @var int
     */
    private $page;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var int
     */
    private $numPages;

    /**
     * @var int
     */
    private $total;

    /**
     * @var array
     */
    private $items;

    /**
     * OrdersResponse constructor.
     *
     * @param int   $page
     * @param int   $limit
     * @param int   $numPages
     * @param int   $total
     * @param array $items
     */
    public function __construct(int $page, int $limit, int $numPages, int $total, array $items)
    {
        $this->page = $page;
        $this->limit = $limit;
        $this->numPages = $numPages;
        $this->total = $total;
        $this->items = $items;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getNumPages(): int
    {
        return $this->numPages;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
