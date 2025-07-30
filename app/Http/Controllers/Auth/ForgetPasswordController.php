<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgetPasswordRequest;
use App\Jobs\ForgetPasswordEmail;
use App\Repositories\UserRepository;
use Illuminate\Support\Carbon;

class ForgetPasswordController extends Controller
{
    public function store(ForgetPasswordRequest $request,
                          UserRepository        $userRepository)
    {
        $data = $request->validated();
        $checkExistEmail = $userRepository->checkUserByEmail($data['email']);
        if ($checkExistEmail) {
            $checkVerifyEmail = $checkExistEmail->email_verified_at != null;
            if ($checkVerifyEmail) {
                $lastRequest = $userRepository->lastRequestForForget($checkExistEmail->identify_number);
                if ($lastRequest) {
                    $checkExpiresTime = Carbon::parse($lastRequest->expired_at)->isFuture();
                    if ($checkExpiresTime) {
                        return response([
                            'message' => 'A previous email was sent that has not expired yet. Please check your email.',
                            'identify_number' => $checkExistEmail->identify_number,
                            'code' => 1400
                        ], 400);
                    }
                    $countOfRequestInOneDay = $userRepository
                        ->countOfRequestInOneDayForForget($checkExistEmail->identify_number);
                    if ($countOfRequestInOneDay === 3) {
                        return response([
                            'message' => 'You have exceeded the maximum number of verification attempts.Please try again later.',
                            'code' => 429
                        ], 429);
                    }
                }
                ForgetPasswordEmail::dispatch($checkExistEmail);
                return response([
                    'message' => ' Please check your inbox to reset password',
                    'identify_number' => $checkExistEmail->identify_number,
                    'code' => 200
                ]);
            }
        }
        return response([
            'message' => 'Email address not found. Please enter a valid email.',
            'code' => 400
        ], 400);

    }


}
