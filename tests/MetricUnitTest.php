<?php

use App\Exceptions\MetricException;
use App\MetricFactory;
use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;

class MetricUnitTest extends TestCase
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

    public function test_wrong_metric_type_throws_exception()
    {
        $raw = ['key' => 'test', 'value' => 234, 'type' => 'array'];

        $this->expectException(MetricException::class);

        $this->metricFactory->createOne(5, $raw, Carbon::now());
    }

    public function test_missing_metric_key_throws_exception()
    {
        $raw = ['value' => 234, 'type' => 'int'];

        $this->expectException(MetricException::class);

        $this->metricFactory->createOne(5, $raw, Carbon::now());
    }

    public function test_empty_metric_key_throws_exception()
    {
        $raw = ['key' => '', 'value' => 234, 'type' => 'int'];

        $this->expectException(MetricException::class);

        $this->metricFactory->createOne(5, $raw, Carbon::now());
    }

    public function test_too_long_metric_key_throws_exception()
    {
        $raw = ['key' => str_repeat('x', 256), 'value' => 234, 'type' => 'int'];

        $this->expectException(MetricException::class);

        $this->metricFactory->createOne(5, $raw, Carbon::now());
    }

    public function test_non_numeric_metric_value_throws_exception()
    {
        $raw = ['key' => 'test', 'value' => 'abcd', 'type' => 'int'];

        $this->expectException(MetricException::class);

        $this->metricFactory->createOne(5, $raw, Carbon::now());
    }

    public function test_too_long_metric_value_throws_exception()
    {
        $raw = ['key' => 'test', 'value' => INF, 'type' => 'int'];

        $this->expectException(MetricException::class);

        $this->metricFactory->createOne(5, $raw, Carbon::now());
    }

    public function test_too_long_metric_unit_throws_exception()
    {
        $raw = ['key' => 'test', 'value' => 1, 'type' => 'int', 'unit' => str_repeat('x', 11)];

        $this->expectException(MetricException::class);

        $this->metricFactory->createOne(5, $raw, Carbon::now());
    }
}
