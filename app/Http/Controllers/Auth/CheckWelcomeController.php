<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CheckIdentifyNumberRequest;
use App\Repositories\UserRepository;
use Carbon\Carbon;

class CheckWelcomeController extends Controller
{
    public function __invoke(CheckIdentifyNumberRequest $request,
                             UserRepository             $userRepository)
    {
        $data = $request->validated();
        $identifyNumber = $data['identify_number'];
        $user = $userRepository->checkUserByIdentifyNumber($identifyNumber);
        if ($user) {
            $lastRequest = $userRepository->lastRequest($identifyNumber);
            if ($lastRequest) {
                $checkExpiresTime = Carbon::parse($lastRequest->expired_at)->isFuture();
                if ($checkExpiresTime) {

                    $countOfRequestInOneDay = $userRepository->countOfRequestInOneDay($identifyNumber);

                    return response([
                        'message' => 'Please check your inbox to complete your account information.',
                        'code' => 200,
                        "data" => $user,
                        "countOfRequest" => $countOfRequestInOneDay,
                        "expired_at" => $lastRequest->expired_at,
                    ], 200);
                }
            }
        }
        return response([
            'message' => 'The user does not exist or the request has expired or the request does not exist.',
            'code' => 400
        ], 400);

    }
}
