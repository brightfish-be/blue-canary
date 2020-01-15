<?php

use App\Event;
use App\Exceptions\EventException;
use App\MetricFactory;
use Illuminate\Database\DatabaseManager;

class EventUnitTest extends TestCase
{
    /** @var MetricFactory */
    protected $metricFactory;

    /** @var DatabaseManager */
    protected $db;

    /** {@inheritdoc} */
    public function setUp(): void
    {
        parent::setUp();

        $this->db = $this->app['db'];
        $this->metricFactory = new MetricFactory($this->db);
    }

    public function test_event_creation_without_counter_id_throws_exception()
    {
        $this->expectException(EventException::class);

        $event = new Event($this->db, $this->metricFactory);

        $event->create([]);
    }

    public function test_event_saving_without_attributes_throws_exception()
    {
        $this->expectException(EventException::class);

        $event = new Event($this->db, $this->metricFactory);

        $event->setCounterId(3)->save();
    }

    public function test_event_data_model_has_correct_schema()
    {
        $event = new Event($this->db, $this->metricFactory);

        $event->setCounterId(3);

        $event->create([]);

        $attr = $event->getAttributes();

        $this->assertArrayHasKey('counter_id', $attr);
        $this->assertArrayHasKey('status_code', $attr);
        $this->assertArrayHasKey('created_at', $attr);
    }

}
