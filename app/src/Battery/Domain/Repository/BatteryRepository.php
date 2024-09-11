<?php

namespace App\Battery\Domain\Repository;

use App\Battery\Domain\Model\Battery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Result;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class BatteryRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
    )
    {
        parent::__construct($registry, Battery::class);
    }

    /**
     * @param \DateTimeImmutable|null $from
     * @param \DateTimeImmutable|null $to
     *
     * @return array<array-key, {hour: string, levelAtStart: int, levelAtEnd: int, recordCount: int}>
     */
    public function getBatteryStatsPerHour(?\DateTimeImmutable $from = null, ?\DateTimeImmutable $to = null): array
    {
        $qb = $this->createQueryBuilder('b1');

        $qb->select('DATE_TRUNC(\'hour\', b1.recordedAt) AS hour');
        $qb->groupBy('hour');


        $levelAtStart = $this->createQueryBuilder('b2')
            ->distinct()
            ->select('b2.level')
            ->where('b2.recordedAt = MIN(b1.recordedAt)')
            ->getQuery()
            ->getDQL();

        $levelAtEnd = $this->createQueryBuilder('b3')
            ->distinct()
            ->select('b3.level')
            ->where('b3.recordedAt = MAX(b1.recordedAt)')
            ->getQuery()
            ->getDQL();


        $qb->addSelect("({$levelAtStart}) AS levelAtStart");
        $qb->addSelect("({$levelAtEnd}) AS levelAtEnd");

        $qb->addSelect($qb->expr()->count('b1.recordedAt') . ' AS recordCount');

        if ($from !== null) {
            $qb->andWhere('b1.recordedAt >= :from');
            $qb->setParameter('from', $from);
        }

        if ($to !== null) {
            $qb->andWhere('b1.recordedAt <= :to');
            $qb->setParameter('to', $to);
        }

        $qb->orderBy('hour', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
