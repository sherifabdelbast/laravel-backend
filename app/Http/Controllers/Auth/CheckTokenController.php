<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CheckIdentifyNumberRequest;
use App\Http\Requests\Auth\CheckTokenRequest;
use App\Repositories\UserRepository;
use Carbon\Carbon;

class CheckTokenController extends Controller
{
    public function __invoke(CheckTokenRequest $request,
                             UserRepository    $userRepository)
    {
        $data = $request->validated();
        $request = $userRepository->getIdentifyNumber($data['token']);
        $user = $userRepository->checkUserByIdentifyNumber($request->identify_number);
        $identifyNumber = $user->identify_number;

        if ($user) {
            $lastRequest = $userRepository->lastRequest($identifyNumber);
            if ($lastRequest) {
                $checkExpiresTime = Carbon::parse($lastRequest->expired_at)->isFuture();
                if ($checkExpiresTime) {
                    return response([
                        'message' => 'The Token is valid',
                        'identify_number' => $identifyNumber,
                        'code' => 200,
                    ], 200);
                }
            }
            return response([
                'message' => 'The request time has expired. Try again',
                'code' => 1400
            ], 400);
        }
        return response([
            'message' => 'User not found',
            'code' => 400
        ], 400);
    }
}
