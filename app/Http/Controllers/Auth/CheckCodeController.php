<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CheckCodeRequest;
use App\Repositories\UserRepository;

class CheckCodeController extends Controller
{
    public function index(CheckCodeRequest $request,
                          UserRepository   $userRepository)
    {
        $data = $request->validated();
        $code = $data['code'];
        $identifyNumber = $data['identify_number'];
        $checkCode = $userRepository->checkLastRequestForForgetPassword($identifyNumber);
        if ($checkCode->code == $code) {
            if ($checkCode->previously_used != 0) {
                return response([
                    'message' => 'code is already used',
                    'code' => 1400,
                ], 400);
            }
            return response([
                'message' => 'reset your password',
                'code' => 200,
            ], 200);
        }
        return response([
            'message' => 'code not true',
            'code' => 400,
        ], 400);
    }
}
