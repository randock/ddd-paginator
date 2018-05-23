<?php

declare(strict_types=1);

namespace Tests\Randock\DddPaginator\Unit\Traits;

use Randock\DddPaginator\DataTransformer\DataTransformerInterface;

trait DataTransformerTrait
{
    /**
     * @var DataTransformerInterface
     */
    private $dataTransformerInterface;

    /**
     * @return \Mockery\MockInterface|DataTransformerInterface
     */
    protected function dataTransformerInterface()
    {
        $this->dataTransformerInterface = $this->dataTransformerInterface ?? \Mockery::mock(DataTransformerInterface::class);

        return $this->dataTransformerInterface;
    }

    protected function shouldCallTransformOnDataTransformerInterface()
    {
        $this->dataTransformerInterface()
            ->shouldReceive('transform')
            ->andReturn(\Mockery::mock(DataTransformerInterface::class));
    }
}
