<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SseAntrianController extends Controller
{
    /**
     * SSE Stream endpoint.
     * Client connect via EventSource di browser.
     * Menggunakan named event: queue-update
     */
    public function stream(Request $request)
    {
        // Release session lock untuk multi-tab support
        session_write_close();
        
        $lastModified = Cache::get('antrian_sse_modified', 0);
        
        \Log::info('SSE Client connected: ' . $request->ip());

        return response()->stream(function () use ($lastModified) {
            set_time_limit(0);
            ignore_user_abort(false);

            $lastModifiedCurrent = $lastModified;

            while (true) {
                if (connection_aborted()) {
                    break;
                }

                $currentModified = Cache::get('antrian_sse_modified', 0);

                if ($currentModified > $lastModifiedCurrent) {
                    $lastModifiedCurrent = $currentModified;
                    $data = Cache::get('antrian_sse_broadcast');

                    if ($data) {
                        echo 'event: queue-update' . PHP_EOL;
                        echo 'data: ' . $data . PHP_EOL;
                        echo PHP_EOL;
                        
                        ob_flush();
                        flush();
                    }
                }

                // Heartbeat
                echo ': heartbeat' . PHP_EOL;
                echo PHP_EOL;
                ob_flush();
                flush();

                sleep(2);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
