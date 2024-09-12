<?php

declare(strict_types=1);

namespace App\Battery\Domain\Repository;

use App\Battery\Domain\Model\Battery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Battery>
 */
class BatteryRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, Battery::class);
    }

    /**
     * @return list<array{hour: string, levelAtStart: int, levelAtEnd: int, levelChange: int, recordCount: int}>
     */
    public function getBatteryStatsPerHourRawSql(
        ?\DateTimeImmutable $from = null,
        ?\DateTimeImmutable $to = null,
    ): array {
        $wheres = [];
        $parameters = [];

        if ($from instanceof \DateTimeImmutable) {
            $wheres[] = 'b1.recorded_at >= :from';
            $parameters['from'] = $from->format('Y-m-d H:i:s');
        }

        if ($to instanceof \DateTimeImmutable) {
            $wheres[] = 'b1.recorded_at <= :to';
            $parameters['to'] = $to->format('Y-m-d H:i:s');
        }

        $whereClause = implode(' AND ', $wheres);
        $whereClause = $whereClause === '' || $whereClause === '0' ? '' : "WHERE {$whereClause}";

        $sql = <<<SQL
            SELECT
                DATE_TRUNC('hour', recorded_at) AS "hour",  -- Group by hour
                (SELECT level FROM public.battery b2
                 WHERE b2.recorded_at = MIN(b1.recorded_at)
                 LIMIT 1) AS "levelAtStart",
                (SELECT level FROM public.battery b3
                 WHERE b3.recorded_at = MAX(b1.recorded_at)
                 LIMIT 1) AS "levelAtEnd",
                (SELECT level FROM public.battery b3 WHERE b3.recorded_at = MAX(b1.recorded_at)
                 LIMIT 1) -
                COUNT(*) AS "recordCount"  -- Number of records in that hour
            FROM
                public.battery b1
            {$whereClause}
            GROUP BY
                DATE_TRUNC('hour', recorded_at)
            ORDER BY
                hour;
            SQL;

        /** @var list<array{hour: string, levelAtStart: int, levelAtEnd: int, levelChange: int, recordCount: int}> */
        return $this->getEntityManager()->getConnection()->executeQuery($sql, $parameters)->fetchAllAssociative();
    }

    /**
     * @return list<array{hour: string, levelAtStart: int, levelAtEnd: int, levelChange: int, recordCount: int}>
     */
    public function getBatteryStatsPerTenMinutesRawSql(
        ?\DateTimeImmutable $from = null,
        ?\DateTimeImmutable $to = null,
    ): array {
        $wheres = [];
        $parameters = [];

        if ($from instanceof \DateTimeImmutable) {
            $wheres[] = 'b1.recorded_at >= :from';
            $parameters['from'] = $from->format('Y-m-d H:i:s');
        }

        if ($to instanceof \DateTimeImmutable) {
            $wheres[] = 'b1.recorded_at <= :to';
            $parameters['to'] = $to->format('Y-m-d H:i:s');
        }

        $whereClause = implode(' AND ', $wheres);
        $whereClause = $whereClause === '' || $whereClause === '0' ? '' : "WHERE {$whereClause}";

        $sql = <<<SQL
            WITH minuteGroups AS (
                SELECT
                    -- Group by 10-minute intervals
                    DATE_TRUNC('hour', b1.recorded_at) + INTERVAL '10 minute' * FLOOR(EXTRACT(MINUTE FROM b1.recorded_at) / 10) AS interval,
                    MIN(b1.recorded_at) AS minRecordedAt,
                    MAX(b1.recorded_at) AS maxRecordedAt
                FROM
                    public.battery b1
                {$whereClause}
                GROUP BY
                    DATE_TRUNC('hour', recorded_at) + INTERVAL '10 minute' * FLOOR(EXTRACT(MINUTE FROM b1.recorded_at) / 10)
            )
            SELECT
                mg.interval AS "hour",
                b_start.level AS "levelAtStart",
                b_end.level AS "levelAtEnd",
                (b_end.level - b_start.level) AS "levelChange",
                COUNT(b1.id) AS "recordCount"
            FROM
                minuteGroups mg
            JOIN
                public.battery b1 ON
                    DATE_TRUNC('hour', b1.recorded_at) + INTERVAL '10 minute' * FLOOR(EXTRACT(MINUTE FROM b1.recorded_at) / 10) = mg.interval
            JOIN
                public.battery b_start ON b_start.recorded_at = mg.minRecordedAt
            JOIN
                public.battery b_end ON b_end.recorded_at = mg.maxRecordedAt
            GROUP BY
                mg.interval, b_start.level, b_end.level
            ORDER BY
                mg.interval;

            SQL;

        /** @var list<array{hour: string, levelAtStart: int, levelAtEnd: int, levelChange: int, recordCount: int}> */
        return $this->getEntityManager()->getConnection()->executeQuery($sql, $parameters)->fetchAllAssociative();
    }
}
