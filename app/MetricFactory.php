<?php

namespace App;

use App\Exceptions\MetricException;
use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;

/**
 * Metric generator.
 *
 * @copyright 2019 Brightfish
 * @author Arnaud Coolsaet <a.coolsaet@brightfish.be>
 */
class MetricFactory
{
    /** @var string */
    const TABLE = 'metrics';

    /** @var array */
    const DEFAULT_DATA = [
        'type' => 'integer',
        'key' => '',
        'value' => 0,
        'unit' => null,
    ];

    /** @var array */
    const VALUE_TYPES = [
        'float', 'int',
    ];

    /** @var DatabaseManager $db Connection to the database */
    protected $db;

    /**
     * MetricFactory constructor.
     * @param DatabaseManager $databaseManager
     */
    public function __construct(DatabaseManager $databaseManager)
    {
        $this->db = $databaseManager;
    }

    /**
     * Create one or more metrics.
     * @param int $eventId
     * @param array $metrics
     * @param Carbon|null $createdAt
     * @return array
     */
    public function create(int $eventId, array $metrics, ?Carbon $createdAt = null): array
    {
        $createdAt = ($createdAt ?? Carbon::now())->toDateTimeString();

        $metrics = array_map(function ($metric) use ($eventId, $createdAt) {
            return $this->createOne($eventId, $metric, $createdAt);
        }, $metrics);

        return $metrics;
    }

    /**
     * Check the raw metric validity and return the raw metric.
     * @param int $eventId
     * @param array $metric
     * @param string $dateTime
     * @return array
     * @throws MetricException
     */
    public function createOne(int $eventId, array $metric, string $dateTime): array
    {
        $metric = array_merge(static::DEFAULT_DATA, $metric);

        if (!is_numeric($metric['value'])) {
            throw new MetricException('A metric can only be numeric.');
        }

        if (!$metric['key'] || strlen($metric['key']) > 255) {
            throw new MetricException('A metric key is missing or too long.');
        }

        if (!in_array($metric['type'], static::VALUE_TYPES)) {
            throw new MetricException('The metric type is not correct.');
        }

        return [
            'event_id' => $eventId,
            'key' => $metric['key'],
            'value' => (float)$metric['value'],
            'type' => $metric['type'],
            'unit' => $metric['unit'] ?: null,
            'created_at' => $dateTime,
        ];
    }

    /**
     * Insert one or more metrics.
     * @param array $metrics
     * @return int
     */
    public function persist(array $metrics): int
    {
        return (int)$this->db->table(self::TABLE)->insert($metrics);
    }
}
