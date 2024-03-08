<?php

declare(strict_types=1);

namespace Randock\DddPaginator\DataTransformer;

use Pagerfanta\Pagerfanta;
use Randock\DddPaginator\Model\PaginatedResponse;

class PaginatedDataTransformer
{
    /**
     * @var DataTransformerInterface
     */
    private $dataTransformer;

    /**
     * PaginatedDataTransformer constructor.
     *
     * @param DataTransformerInterface $dataTransformerInterface
     */
    public function __construct(DataTransformerInterface $dataTransformerInterface)
    {
        $this->dataTransformer = $dataTransformerInterface;
    }

    public function transform(Pagerfanta $pager): PaginatedResponse
    {
        $items = [];
        /** @var object $item */
        foreach ($pager->getCurrentPageResults() as $item) {
            $items[] = $this->dataTransformer->transform($item);
        }

        return new PaginatedResponse(
            $pager->getCurrentPage(),
            $pager->getMaxPerPage(),
            $pager->getNbPages(),
            $pager->getNbResults(),
            $items
        );
    }
}
