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
     * 
     * Route tanpa session middleware agar page lain tetap bisa diakses
     */
    public function stream(Request $request)
    {
        // Release session lock immediately - ini kunci nya!
        session_write_close();
        
        $lastModified = Cache::get('antrian_sse_modified', 0);
        
        // Log untuk development
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
                        // Named event: queue-update
                        echo 'event: queue-update' . PHP_EOL;
                        echo 'data: ' . $data . PHP_EOL;
                        echo PHP_EOL;
                        
                        ob_flush();
                        flush();
                    }
                }

                // Heartbeat untuk menjaga koneksi
                echo ': heartbeat' . PHP_EOL;
                echo PHP_EOL;
                ob_flush();
                flush();

                sleep(1);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
