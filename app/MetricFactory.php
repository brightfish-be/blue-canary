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
        'type' => 'float',
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

        $metrics = array_map(function ($metric, $key) use ($eventId, $createdAt) {
            $metric = is_array($metric) ? $metric : ['key' => $key, 'value' => $metric];

            return $this->createOne($eventId, $metric, $createdAt);
        }, $metrics, array_keys($metrics));

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

        if (!$metric['key'] || strlen($metric['key']) > 255) {
            throw new MetricException('The character count of a metric key should be between 1 and 255.', 500);
        }

        $key = $metric['key'];

        if (!in_array($metric['type'], static::VALUE_TYPES)) {
            throw new MetricException("The data type for $key can only be 'int' or 'float'.", 500);
        }

        if (!is_numeric($metric['value'])) {
            throw new MetricException("The value for $key is not numeric.", 500);
        }

        if (is_infinite((float)$metric['value'])) {
            throw new MetricException("The value for $key is too long.", 500);
        }

        if (isset($metric['unit']) && strlen($metric['unit']) > 10) {
            throw new MetricException("The unit notation for $key is longer than 10 characters.", 500);
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
