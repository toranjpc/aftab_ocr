<?php

namespace Modules\Sse\Controllers;

use Modules\Sse\Models\SSE;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SSEController extends Controller
{

    function handleSSEClose()
    {
        set_time_limit(0);
        ignore_user_abort(true);
        return $this->runInPeriod(5, function () {
            $this->printSSE('heart');
            if (connection_aborted()) {
                exit ();
            }
        });
    }

    function printSSE($message)
    {
        echo 'data: ' . json_encode($message) . "\n\n";
        ob_flush();
        flush();
    }

    function runInPeriod($delay, $fn)
    {
        $start = time();

        return function () use (&$start, $fn, $delay) {
            if ((time() - $start) >= $delay) {
                $fn();
                $start = time();
            }
        };
    }

    public function sse($event)
    {
        $response = new StreamedResponse(
            function () use ($event) {
                $this->printSSE('ADD');

                $handleSSECloseCallback = $this->handleSSEClose();

                while (true) {
                    $handleSSECloseCallback();

                    $fromDate = now()->subSeconds(6);

                    $tillDate = now();

                    $notifs = SSE::where(['event' => $event])->whereBetween('created_at', [$fromDate, $tillDate]);

                    if (request('receiver_id')) {
                        $notifs->where('receiver_id', request('receiver_id'));
                    }

                    $notifs = $notifs->get();

                    if ($notifs->count()) {
                        foreach ($notifs as $notif) {
                            $this->printSSE($notif->message);
                            sleep(1);
                        }
                    }

                    sleep(3);

                    SSE::destroy($notifs->pluck('id')->toArray());
                }
            }
        );
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST, HEAD, PATCH, DELETE');
        $response->headers->set('Access-Control-Allow-Headers', '*');
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Cache-Control', 'no-cache');


        return $response;
    }
}
