<?php

declare(strict_types=1);

namespace Module\ApiPlatformEasyFilter\Filter\Trait;

use ApiPlatform\OpenApi\Model\Parameter;
use Module\ApiPlatformEasyFilter\Attribute\ApiParameter;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Use this trait to add sorting to your filter.
 *
 * Your filter should implement QueryBuilderApiFilterInterface.
 *
 * @see \App\ApiPlatform\Adapter\QueryBuilderApiFilterInterface
 */
trait SortTrait
{
    /**
     * @var array<string>
     */
    protected const array ALLOWED_SORT_FIELDS = ['id'];

    /**
     * @var array<string>
     */
    protected const array BLACKLISTED_SORT_FIELDS = ['password'];

    #[ApiParameter(description: 'Sort by field.')]
    public string $sort = 'id';

    #[Assert\Choice(choices: ['ASC', 'DESC'])]
    #[ApiParameter(
        openApi: new Parameter(
            name: 'direction',
            in: 'query',
            schema: [
                'type' => 'string',
                'enum' => ['ASC', 'DESC'],
            ],
        ),
        description: 'Sort direction.'
    )]
    public string $direction = 'ASC';

    /**
     * @param array<string> $allowedSortFields
     * @param array<string> $blacklistedSortFields
     */
    protected function applySortToQueryBuilder(
        QueryBuilder $qb,
        array        $allowedSortFields = self::ALLOWED_SORT_FIELDS,
        array        $blacklistedSortFields = self::BLACKLISTED_SORT_FIELDS,
        ?string      $rootAlias = null,
    ): QueryBuilder
    {
        $rootAlias ??= $qb->getRootAliases()[0];
        $shouldSort = false;
        if (\in_array($this->sort, $allowedSortFields, true)) {
            $shouldSort = true;
        }

        if (\in_array($this->sort, $blacklistedSortFields, true)) {
            $shouldSort = false;
        }

        if ($shouldSort) {
            $qb->addOrderBy("{$rootAlias}.{$this->sort}", $this->direction);
        }

        return $qb;
    }
}
