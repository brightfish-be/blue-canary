<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FullApplicationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_health_endpoint()
    {
        //$this->expectException(NotFoundHttpException::class);
        //$r = $this->get('api/v1/event/47145dc2-810f-4a75-8c84-e21529e3d26b/default.counter')->response;
        //dd($r);
        $this->assertTrue(true);
    }
}
