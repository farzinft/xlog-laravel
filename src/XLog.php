<?php
namespace Keraken\Log;


use Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Exception;

class XLog
{
    const LOG_LEVELS = ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'];

    public function __call($name, $arguments)
    {
        if (!in_array($name, self::LOG_LEVELS)) {
            $name = 'debug';
        }

        return call_user_func_array(['Illuminate\Support\Facades\Log', $name], $arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        $arguments[1]['sid'] = session_id();
        $arguments[1]['uip'] = Request::ip();

        if (!Auth::guest()) {
            // add user id to all logs
            $arguments[1]['uid'] = 'us'.Auth::user()->id.'er';
        }

        $trackIdKey = env('TRACK_ID_KEY', 'tid');

        // get request tag from service container
        if (!isset($arguments[1][$trackIdKey])) {
            // $arguments[1][$trackIdKey] = App::make($trackIdKey);
        }

        return call_user_func_array(['Illuminate\Support\Facades\Log', $name], $arguments);
    }

    public static function exception(Exception $e, $level = 'error')
    {
        $arguments = [];
        $arguments [0] = 'exception' . $e->getMessage();
        $arguments [1] = [
            'code' => $e->getCode(),
            'file' => basename($e->getFile()),
            'line' => $e->getLine(),
        ];

        return call_user_func_array(['App\XLog', $level], $arguments);
    }
}