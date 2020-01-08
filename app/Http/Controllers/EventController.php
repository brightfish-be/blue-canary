<?php

namespace App\Http\Controllers;

use App\Event;
use App\Exceptions\EventException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Data point storing controller.
 *
 * @copyright 2019 Brightfish
 * @author Arnaud Coolsaet <a.coolsaet@brightfish.be>
 */
class EventController extends Controller
{
    /**
     * Handle incoming data point events.
     * @param Event $event
     * @param Request $request
     * @param string $appUuid
     * @param string $counter
     * @return Response
     * @throws EventException
     */
    public function store(Event $event, Request $request, string $appUuid, string $counter): Response
    {
        if (!($appId = $this->appExists($appUuid))) {
            throw new EventException('This application does not exist.', 404);
        }

        if (!($counterId = $this->counterExists($appId, $counter))) {
            throw new EventException('This counter does not exist.', 404);
        }

        $data = $request->all() ?: $request->json()->all();

        if (!$data) {
            throw new EventException('There is no data for this event.', 404);
        }

        $eventId = $event->setCounterId($counterId)->create($data)->save();

        $result = $eventId ? $event->addMetrics($eventId, $data['metrics'] ?? []) : 0;

        return $request->header('Accept') === 'text/plain'
            ? $this->respondWithText((string) $result)
            : $this->respond($result);
    }

    /**
     * Check if we have an app for the given uuid.
     * @param string $uuid
     * @return int
     */
    protected function appExists(string $uuid): int
    {
        $app = app('db')->selectOne('select id from apps where uuid = :uuid', ['uuid' => $uuid]);

        return $app ? $app->id : 0;
    }

    /**
     * Check if we have a counter for the given app uuid and counter name.
     * @param int $appId
     * @param string $name
     * @return int
     */
    protected function counterExists(int $appId, string $name): int
    {
        $counter = app('db')->selectOne(
            'select id from counters where app_id = :id and name = :name',
            ['id' => $appId, 'name' => $name]
        );

        return $counter ? $counter->id : 0;
    }
}
