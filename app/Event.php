<?php

namespace App;

use App\Exceptions\EventException;
use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;

/**
 * Data point entry.
 *
 * @copyright 2019 Brightfish
 * @author Arnaud Coolsaet <a.coolsaet@brightfish.be>
 */
class Event
{
    /** @var string */
    const TABLE = 'events';

    /**
     * Validates and generates metric from the current event.
     * @var MetricFactory
     */
    protected $metricFactory;

    /**
     * Connection to the database.
     * @var DatabaseManager
     */
    protected $db;

    /**
     * The counter id this event is for.
     * @var string
     */
    protected $counterId = '';

    /**
     * Data for this event.
     * @var array
     */
    protected $attributes = [];

    /**
     * Event constructor.
     * @param DatabaseManager $databaseManager
     * @param MetricFactory $metricFactory
     */
    public function __construct(DatabaseManager $databaseManager, MetricFactory $metricFactory)
    {
        $this->db = $databaseManager;

        $this->metricFactory = $metricFactory;
    }

    /**
     * Set the counter id this event is for.
     * @param int $counterId
     * @return Event
     */
    public function setCounterId(int $counterId): Event
    {
        $this->counterId = $counterId;

        return $this;
    }

    /**
     * Build the event attributes from an array.
     * @param array $data
     * @return Event
     * @throws EventException
     */
    public function create(array $data): Event
    {
        $this->checkCounterId();

        $createdAt = Carbon::now();

        $this->attributes = [
            'counter_id' => $this->counterId,
            'client_id' => $data['client_id'] ?? null,
            'client_name' => $data['client_name'] ?? null,
            'status_code' => (int)($data['status_code'] ?? 0),
            'status_remark' => $data['status_remark'] ?? null,
            'created_at' => $createdAt,
            'generated_at' => !empty($data['generated_at']) ? Carbon::parse($data['generated_at']) : null,
        ];

        return $this;
    }

    /**
     * Create and persist metrics for an event.
     * @param int $eventId
     * @param array $metrics
     * @return int
     */
    public function addMetrics(int $eventId, array $metrics): int
    {
        $metrics = $this->metricFactory->create($eventId, $metrics, $this->attributes['created_at']);

        return $this->metricFactory->persist($metrics);
    }

    /**
     * Return the event's data.
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Persist an event and return its primary id.
     * @return int
     * @throws EventException
     */
    public function save(): int
    {
        if (!$this->attributes) {
            throw new EventException('There is no data to save to this event.');
        }

        return $this->db->table(static::TABLE)->insert($this->attributes)
            ? (int)$this->db->getPdo()->lastInsertId()
            : 0;
    }

    /**
     * Check if we have the minimal data point info.
     * @return void
     * @throws EventException
     */
    protected function checkCounterId(): void
    {
        if (!$this->counterId) {
            throw new EventException('A counter id is missing.');
        }
    }
}
