<?php

declare(strict_types=1);

namespace Tests\Randock\DddPaginator\Unit\Traits;

use Pagerfanta\Pagerfanta;

trait PagerfantaTestTrait
{
    /**
     * @var \Mockery\MockInterface|Pagerfanta
     */
    private $pagerfanta;

    /**
     * @return \Mockery\MockInterface|Pagerfanta
     */
    protected function pagerfanta()
    {
        $this->pagerfanta = $this->pagerfanta ?? \Mockery::mock(Pagerfanta::class);

        return $this->pagerfanta;
    }

    /**
     * @param array $items
     */
    protected function shouldCallGetCurrentPageResultsOnPagerfanta(array $items)
    {
        $this->pagerfanta()
            ->shouldReceive('getCurrentPageResults')
            ->andReturn($items);
    }

    /**
     * @param int $page
     */
    protected function shouldCallGetCurrentPageOnPagerfanta(int $page)
    {
        $this->pagerfanta()
            ->shouldReceive('getCurrentPage')
            ->andReturn($page);
    }

    /**
     * @param int $limit
     */
    protected function shouldCallGetMaxPerPageOnPagerfanta(int $limit)
    {
        $this->pagerfanta()
            ->shouldReceive('getMaxPerPage')
            ->andReturn($limit);
    }

    /**
     * @param int $numPage
     */
    protected function shouldCallGetNbPagesOnPagerfanta(int $numPage)
    {
        $this->pagerfanta()
            ->shouldReceive('getNbPages')
            ->andReturn($numPage);
    }

    /**
     * @param int $total
     */
    protected function shouldCallGetNbResultsOnPagerfanta(int $total)
    {
        $this->pagerfanta()
            ->shouldReceive('getNbResults')
            ->andReturn($total);
    }

    protected function shouldCallSetMaxPerPageOnPagerfanta()
    {
        $this->pagerfanta()
            ->shouldReceive('setMaxPerPage')
            ->withArgs(function ($argument) {
                if (\is_int($argument)) {
                    return true;
                }

                return false;
            });
    }

    protected function shouldCallSetCurrentPageOnPagerfanta()
    {
        $this->pagerfanta()
            ->shouldReceive('setCurrentPage')
            ->withArgs(function ($argument) {
                if (\is_int($argument)) {
                    return true;
                }

                return false;
            });
    }
}
