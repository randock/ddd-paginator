<?php

declare(strict_types=1);

namespace Randock\DddPaginator\Repository;

use Pagerfanta\Pagerfanta;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\ArrayAdapter;
use Doctrine\ORM\Query\Expr\Comparison;
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
    public const OPERATOR_NOT_EQ = 'not_eq';
    public const OPERATOR_OR = 'or';

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
        foreach ($criteria as $name => $criterion) {
            if (null === $criterion) {
                continue;
            }
            $expression = $this->getExpression($alias, $queryBuilder, $name, $criterion);
            $queryBuilder->andWhere($expression);
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
    private function getPropertyName(
        string $alias,
        string $name
    ): string {
        $alias = self::extractAliasFromFieldName($name, $alias);
        $aa = $this->startsWith($name, $alias);
        if (false === $aa) {
            return sprintf(
                '%s.%s',
                $alias,
                $name
            );
        }

        return $name;
    }

    /**
     * @param string $haystack
     * @param string $needle
     *
     * @return bool
     */
    private function startsWith($haystack, $needle): bool
    {
        return '' === $needle || \preg_match(
                \sprintf(
                    "/^(%s\.)/u",
                    \preg_quote(
                        $needle,
                        '/'
                    )
                ),
                $haystack
            );
    }

    /**
     * @param string       $alias
     * @param QueryBuilder $queryBuilder
     * @param string       $name
     * @param array        $criterion
     *
     * @return Comparison|Func|Orx|string|Comparison|Andx|null
     */
    private function getExpression(string $alias, QueryBuilder $queryBuilder, string $name, array $criterion)
    {
        static $position = 0;

        $name = $this->getPropertyName($alias, $name);
        $parameter = ':' . str_replace('.', '_', $name) . ++$position;

        $operation = $criterion['operator'];
        $parameterValue = $criterion['value'];

        $expression = null;
        switch ($operation) {
            case static::OPERATOR_GT:
                $expression = $queryBuilder->expr()->gt($name, $parameter);
                break;
            case static::OPERATOR_LT:
                $expression = $queryBuilder->expr()->lt($name, $parameter);
                break;
            case static::OPERATOR_GTE:
                $expression = $queryBuilder->expr()->gte($name, $parameter);
                break;
            case static::OPERATOR_LTE:
                $expression = $queryBuilder->expr()->lte($name, $parameter);
                break;
            case static::OPERATOR_LIKE:
                $expression = $queryBuilder->expr()->like($name, $parameter);
                $parameterValue = '%' . $parameterValue . '%';
                break;
            case static::OPERATOR_BETWEEN:
                $expression = $queryBuilder->expr()->between($name, $parameterValue[0], $parameterValue[1]);
                break;
            case static::OPERATOR_OR:
                $ors = [];
                foreach ($parameterValue as $criteria) {
                    $ors[] = $this->getExpression(
                        $alias,
                        $queryBuilder,
                        $criteria['field'],
                        $criteria
                    );
                }
                $expression = $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->orX(...$ors)
                );

                $parameterValue = null;
                break;
            case static::OPERATOR_EQ:
            case static::OPERATOR_NOT_EQ:

            default:
                if (null === $parameterValue) {
                    $expression = $queryBuilder->expr()->isNull($parameter);
                } elseif (\is_array($parameterValue)) {
                    $expression = $queryBuilder->expr()->in($name, $parameter);
                } elseif ('' !== $parameterValue) {
                    $expression = $queryBuilder->expr()->eq($name, $parameter);
                }
        }

        if (static::OPERATOR_NOT_EQ === $operation) {
            $expression = $queryBuilder->expr()->not($expression);
        }

        if (null !== $parameterValue) {
            $queryBuilder->setParameter($parameter, $parameterValue);
        }

        return $expression;
    }

    /**
     * @param string $fieldName
     * @param string $parentAlias
     *
     * @return string
     */
    private static function extractAliasFromFieldName(
        string $fieldName,
        string $parentAlias
    ): string {
        $parts = \explode('.', $fieldName);
        if (2 === \count($parts) && !empty($parts[0])) {
            return $parts[0];
        }

        return $parentAlias;
    }
}
