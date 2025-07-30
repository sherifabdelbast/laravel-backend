<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CompleteRegisterRequest;
use App\Repositories\UserRepository;
use Carbon\Carbon;

class CompleteRegisterController extends Controller
{
    public function update(CompleteRegisterRequest $request,
                           UserRepository          $userRepository)
    {
        $data = $request->validated();
        $identifyNumber = $data['identify_number'];
        $checkUser = $userRepository->checkUserByIdentifyNumber($identifyNumber);

        if ($checkUser) {
            $checkVerifyEmail = $checkUser->email_verified_at === null;
            if (!$checkVerifyEmail) {
                return response([
                    'message' => 'Your account information has already been completed.',
                    'code' => 400
                ], 400);
            }

            $lastRequest = $userRepository->lastRequest($identifyNumber);
            $checkExpiresTime = Carbon::parse($lastRequest->expired_at)->isFuture();
            if ($checkExpiresTime) {
                $userRepository->completeUserInformation($data, $checkUser);
                auth('customer')->loginUsingId($checkUser->id);
                if (auth('customer')->check()) {
                    return response([
                        'message' => "Congratulations! You've completed your profile on Taskat",
                        'code' => 200,
                        'data' => auth('customer')->user()
                    ], 200);
                } else {
                    return response([
                        'message' => 'User not found',
                        'code' => 400
                    ], 404);
                }
            }
            return response([
                'message' => 'The request has expired. Please initiate a new request.',
                'code' => 400
            ], 400);
        }
        return response([
            'message' => 'User not found',
            'code' => 400
        ], 400);
    }
}
