<?php

class FeatureTest extends TestCase
{
    public function test_wrong_uuid_returns_404()
    {
        $status = $this->call('GET', '/api/v1/event/app-uuid/default.counter')
            ->getStatusCode();

        $this->assertEquals($status, 404);
    }

    public function test_wrong_counter_name_returns_404()
    {
        $status = $this->call('GET', '/api/v1/event/b4E5b34b-d68f-4019-8a94-a38abc9c7e40/default*counter')
            ->getStatusCode();

        $this->assertEquals($status, 404);
    }

    public function test_missing_counter_name_returns_404()
    {
        $status = $this->call('GET', '/api/v1/event/b4E5b34b-d68f-4019-8a94-a38abc9c7e40')
            ->getStatusCode();

        $this->assertEquals($status, 404);
    }
}
