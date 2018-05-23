<?php

declare(strict_types=1);

namespace Randock\DddPaginator\Dto;

abstract class PaginationRequest
{
    public const LIMIT = 500;
    public const PAGE = 1;

    /**
     * @var array
     */
    private $criteria = [];

    /**
     * @var array
     */
    private $sort = [];

    /**
     * @var int
     */
    private $limit = self::LIMIT;

    /**
     * @var int
     */
    private $page = self::PAGE;

    /**
     * PaginationRequest constructor.
     *
     * @param array    $criteria
     * @param array    $sort
     * @param int|null $limit
     * @param int|null $page
     */
    public function __construct(array $criteria = [], array $sort = [], int $limit = null, int $page = null)
    {
        $this->criteria = $criteria;
        $this->sort = $sort;
        $this->limit = $limit ?? self::LIMIT;
        $this->page = $page ?? self::PAGE;
    }

    /**
     * @return array
     */
    public function getCriteria(): array
    {
        return $this->criteria;
    }

    /**
     * @return array
     */
    public function getSort(): array
    {
        return $this->sort;
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
    public function getPage(): int
    {
        return $this->page;
    }
}
