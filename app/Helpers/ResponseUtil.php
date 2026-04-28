<?php

namespace App\Helpers;

class ResponseUtil
{
    public static function success($data, $message = 'Success')
    {
        return [
            'success' => true,
            'data' => $data,
            'message' => $message,
        ];
    }

    public static function error($message, array $data = [])
    {
        $res = [
            'success' => false,
            'message' => $message,
        ];

        if (! empty($data)) {
            $res['data'] = $data;
        }

        return $res;
    }
}
