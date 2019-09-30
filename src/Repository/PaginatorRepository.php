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
    public const OPERATOR_NOT_LIKE = 'not_like';
    public const OPERATOR_BETWEEN = 'between';
    public const OPERATOR_NOT_EQ = 'not_eq';
    public const OPERATOR_OR = 'or';
    public const OPERATOR_IN = 'in';
    public const OPERATOR_NOT_IN = 'not_in';

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
     * @var array
     */
    protected $aliasJoins;

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
     * @return array
     */
    public function getAliasJoins(): array
    {
        return $this->aliasJoins;
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
        $this->aliasJoins = self::extractAliasJoins($joins);

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

            $fieldName = $criterion['field'] ?? $name;
            $expression = $this->getExpression($alias, $queryBuilder, $fieldName, $criterion);
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
        if (0 === \preg_match("/(([a-z0-9]+)\(([^\(\)]+)\))/ui", $name)) {
            $alias = self::extractAliasFromFieldName($name, $alias, $this->aliasJoins);
            $startsWith = $this->startsWith($name, $alias);
            if (false === $startsWith) {
                return \sprintf(
                    '%s.%s',
                    $alias,
                    $name
                );
            }
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
        $parameter = ':' . \str_replace(['.', '(', ')'], '_', $name) . ++$position;

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
            case static::OPERATOR_NOT_LIKE:
                $expression = $queryBuilder->expr()->notLike($name, $parameter);
                $parameterValue = '%' . $parameterValue . '%';
                break;
            case static::OPERATOR_BETWEEN:
                $expression = $queryBuilder->expr()->between($name, $parameter . '_0', $parameter . '_1');
                break;
            case static::OPERATOR_OR:
                $orExpressions = [];
                foreach ($parameterValue as $criteria) {
                    $orExpressions[] = $this->getExpression(
                        $alias,
                        $queryBuilder,
                        $criteria['field'],
                        $criteria
                    );
                }
                $expression = $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->orX(...$orExpressions)
                );

                $parameterValue = null;
                break;
            case static::OPERATOR_NOT_IN:
                $expression = $queryBuilder->expr()->notIn($name, $parameter);
                break;
            case static::OPERATOR_EQ:
            case static::OPERATOR_NOT_EQ:
            case static::OPERATOR_IN:
            default:
                if (null === $parameterValue) {
                    $expression = $queryBuilder->expr()->isNull($name);
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
            if (static::OPERATOR_BETWEEN === $operation) {
                $queryBuilder->setParameter($parameter . '_0', $parameterValue[0]);
                $queryBuilder->setParameter($parameter . '_1', $parameterValue[1]);
            } else {
                $queryBuilder->setParameter($parameter, $parameterValue);
            }
        }

        return $expression;
    }

    /**
     * @param string $fieldName
     * @param string $parentAlias
     * @param array  $aliasJoins
     *
     * @return string
     */
    private static function extractAliasFromFieldName(
        string $fieldName,
        string $parentAlias,
        array $aliasJoins
    ): string {
        $parts = \explode('.', $fieldName);
        if (2 === \count($parts) &&
            !empty($parts[0]) &&
            \in_array($parts[0], $aliasJoins)) {
            return $parts[0];
        }

        return $parentAlias;
    }

    /**
     * @param array $joins
     *
     * @return array
     */
    private static function extractAliasJoins(array $joins): array
    {
        $aliasJoins = [];
        $valueJoins = \array_values($joins);
        foreach ($valueJoins as $field => $alias) {
            $aliasJoins = \array_merge($aliasJoins, $alias);
        }

        return $aliasJoins;
    }
}
