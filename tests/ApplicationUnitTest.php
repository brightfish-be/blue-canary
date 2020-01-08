<?php

use App\Event;
use App\Exceptions\EventException;
use App\Exceptions\MetricException;
use App\MetricFactory;
use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;

class ApplicationUnitTest extends TestCase
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

    public function test_event_creation_without_counter_id()
    {
        $this->expectException(EventException::class);

        $event = new Event($this->db, $this->metricFactory);

        $event->create([]);
    }

    public function test_event_saving_without_attributes()
    {
        $this->expectException(EventException::class);

        $event = new Event($this->db, $this->metricFactory);

        $event->setCounterId(3)->save();
    }

    public function test_event_data_model()
    {
        $event = new Event($this->db, $this->metricFactory);

        $event->setCounterId(3);

        $event->create([]);

        $attr = $event->getAttributes();

        $this->assertArrayHasKey('counter_id', $attr);
        $this->assertArrayHasKey('status_code', $attr);
        $this->assertArrayHasKey('created_at', $attr);
    }

    public function test_metric_data_model()
    {
        $raw = [
            'key' => 'test',
            'value' => 234,
            'type' => 'int',
        ];

        $metric = $this->metricFactory->createOne(5, $raw, Carbon::now());

        $this->assertArrayHasKey('key', $metric);
        $this->assertArrayHasKey('value', $metric);
        $this->assertArrayHasKey('type', $metric);
    }

    public function test_metric_wrong_type_throws_exception()
    {
        $raw = ['key' => 'test', 'value' => 234, 'type' => 'array'];

        $this->expectException(MetricException::class);

        $this->metricFactory->createOne(5, $raw, Carbon::now());
    }

    public function test_metric_missing_key_throws_exception()
    {
        $raw = ['value' => 234, 'type' => 'int'];

        $this->expectException(MetricException::class);

        $this->metricFactory->createOne(5, $raw, Carbon::now());
    }

    public function test_metric_value_not_numeric_throws_exception()
    {
        $raw = ['key' => 'test', 'value' => 'abcd', 'type' => 'int'];

        $this->expectException(MetricException::class);

        $this->metricFactory->createOne(5, $raw, Carbon::now());
    }
}
