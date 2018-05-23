<?php

declare(strict_types=1);

namespace Randock\DddPaginator\Repository;

use Pagerfanta\Pagerfanta;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\ArrayAdapter;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;

/**
 * Class EntityRepository.
 */
abstract class PaginatorRepository
{
    public const OPERATOR_GT = 'gt';
    public const OPERATOR_LT = 'lt';
    public const OPERATOR_EQ = 'eq';
    public const OPERATOR_LTE = 'lte';
    public const OPERATOR_GTE = 'gte';
    public const OPERATOR_LIKE = 'like';
    public const OPERATOR_BETWEEN = 'between';

    public const JOIN_LEFT = 'left';
    public const JOIN_INNER = 'inner';

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * PaginatorRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $alias
     * @param array  $criteria
     * @param array  $sort
     * @param array  $joins
     *
     * @return Pagerfanta
     */
    protected function pagination(string $alias = 'o', array $criteria = [], array $sort = [], array $joins = []): Pagerfanta
    {
        $queryBuilder = $this->repository->createQueryBuilder($alias);

        return $this->createOperatorPaginator($queryBuilder, $alias, $criteria, $sort, $joins);
    }

    /**
     * @param array $objects
     *
     * @return Pagerfanta
     */
    protected function getArrayPaginator(array $objects): Pagerfanta
    {
        return new Pagerfanta(new ArrayAdapter($objects));
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string       $alias
     * @param array        $criteria
     * @param array        $sorting
     * @param array        $joins
     *
     * @return Pagerfanta
     */
    private function createOperatorPaginator(
        QueryBuilder $queryBuilder,
        string $alias,
        array $criteria = [],
        array $sorting = [],
        array $joins = []
    ): Pagerfanta {
        $this->applyJoins($joins, $queryBuilder);
        $this->applyCriteriaOperator($alias, $queryBuilder, $criteria);
        $this->applySorting($alias, $queryBuilder, $sorting);

        return $this->getPaginator($queryBuilder);
    }

    /**
     * @param array        $joins
     * @param QueryBuilder $queryBuilder
     *
     * @return QueryBuilder
     */
    private function applyJoins(array $joins, QueryBuilder $queryBuilder): QueryBuilder
    {
        foreach ($joins as $type => $join) {
            foreach ($join as $table => $alias) {
                switch ($type) {
                    case self::JOIN_LEFT:
                        $queryBuilder->leftJoin($table, $alias);
                        break;
                    case self::JOIN_INNER:
                    default:
                        $queryBuilder->innerJoin($table, $alias);
                        break;
                }
            }
        }

        return $queryBuilder;
    }

    /**
     * @param string       $alias
     * @param QueryBuilder $queryBuilder
     * @param array        $criteria
     *
     * @return QueryBuilder
     */
    private function applyCriteriaOperator(
        string $alias,
        QueryBuilder $queryBuilder,
        array $criteria = []
    ): QueryBuilder {
        $position = 0;
        foreach ($criteria as $name => $expression) {
            if (null === $expression) {
                continue;
            }
            $name = $this->getPropertyName($alias, $name);
            $parameter = ':' . str_replace('.', '_', $name) . $position;

            $operation = $expression['operator'];
            $parameterValue = $expression['value'];

            switch ($operation) {
                case static::OPERATOR_GT:
                    $queryBuilder->andWhere($queryBuilder->expr()->gt($name, $parameter));
                    break;
                case static::OPERATOR_LT:
                    $queryBuilder->andWhere($queryBuilder->expr()->lt($name, $parameter));
                    break;
                case static::OPERATOR_GTE:
                    $queryBuilder->andWhere($queryBuilder->expr()->gte($name, $parameter));
                    break;
                case static::OPERATOR_LTE:
                    $queryBuilder->andWhere($queryBuilder->expr()->lte($name, $parameter));
                    break;
                case static::OPERATOR_LIKE:
                    $queryBuilder->andWhere($queryBuilder->expr()->like($name, $parameter));
                    $parameterValue = '%' . $parameterValue . '%';
                    break;
                case static::OPERATOR_BETWEEN:
                    $queryBuilder->andWhere($queryBuilder->expr()->between($name, $parameterValue[0], $parameterValue[1]));
                    break;
                case static::OPERATOR_EQ:

                default:
                    if (null === $parameterValue) {
                        $queryBuilder->andWhere($queryBuilder->expr()->isNull($parameter));
                    } elseif (is_array($parameterValue)) {
                        $queryBuilder->andWhere($queryBuilder->expr()->in($name, $parameter));
                    } elseif ('' !== $parameterValue) {
                        $queryBuilder->andWhere($queryBuilder->expr()->eq($name, $parameter));
                    }
            }

            $queryBuilder->setParameter($parameter, $parameterValue);
            ++$position;
        }

        return $queryBuilder;
    }

    /**
     * @param string       $alias
     * @param QueryBuilder $queryBuilder
     * @param array        $sorting
     *
     * @return QueryBuilder
     */
    private function applySorting(string $alias, QueryBuilder $queryBuilder, array $sorting = []): QueryBuilder
    {
        foreach ($sorting as $property => $order) {
            if (!empty($order)) {
                $queryBuilder->addOrderBy($this->getPropertyName($alias, $property), $order);
            }
        }

        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     *
     * @return Pagerfanta
     */
    private function getPaginator(QueryBuilder $queryBuilder): Pagerfanta
    {
        return new Pagerfanta(new DoctrineORMAdapter($queryBuilder, true, false));
    }

    /**
     * @param string $alias
     * @param string $name
     *
     * @return string
     */
    private function getPropertyName(string $alias, string $name): string
    {
        return (false === strpos($name, '.') && false === $this->startsWith($name, $alias)) ? $alias . '.' . $name : $name;
    }

    /**
     * @param string $haystack
     * @param string $needle
     *
     * @return bool
     */
    private function startsWith($haystack, $needle): bool
    {
        return '' === $needle || false !== strrpos($haystack, $needle, -strlen($haystack));
    }
}
