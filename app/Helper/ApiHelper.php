<?php

namespace App\Helper;

class ApiHelper
{
    static function apiResponse($data = [], $code = 200,$status='success')
    {
        $data['status']=$status;
        $data['code']=$code;
        return response()->json($data, $code);
    }
}
