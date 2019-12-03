<?php

namespace App;

use App\Exceptions\EventException;
use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;

/**
 * Data point entry
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
     * @var DatabaseManager $db Connection to the database
     */
    protected $db;

    /**
     * @var string $counterId The counter id this event is for
     */
    protected $counterId = '';

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
     * @param string $counterId
     * @return void
     */
    public function setCounterId(string $counterId): void
    {
        $this->counterId = $counterId;
    }

    /**
     * Persist an event from an array.
     * @param array $body
     * @return int
     * @throws EventException
     */
    public function fromArray(array $body): int
    {
        $this->checkCounterId();

        $createdAt = Carbon::now();

        $eventRaw = [
            'counter_id' => $this->counterId,
            'client_id' => $body['client_id'] ?? null,
            'client_name' => $body['client_name'] ?? null,
            'status_code' => (int)($body['status_code'] ?? 0),
            'status_remark' => $body['status_remark'] ?? null,
            'created_at' => $createdAt->toDateTimeString(),
            'generated_at' => !empty($body['generated_at'])
                ? Carbon::parse($body['generated_at'])->toDateTimeString()
                : null,
        ];

        if (!($eventId = $this->insertEvent($eventRaw))) {
            return 0;
        }

        if (empty($body['metrics'])) {
            return 1;
        }

        return $this->metricFactory->create($eventId, $body['metrics'], $createdAt);
    }

    /**
     * Persist an event and return its primary id.
     * @param array $data
     * @return int
     */
    protected function insertEvent(array $data): int
    {
        return $this->db->table(static::TABLE)->insert($data)
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
