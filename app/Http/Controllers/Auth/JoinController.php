<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\JoinRequest;
use App\Jobs\SendVerifyEmail;
use App\Notifications\VerifyEmailNotification;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Str;


class JoinController extends Controller
{
    public function store(JoinRequest    $request,
                          UserRepository $userRepository)
    {
        $data = $request->validated();
        $checkExistEmail = $userRepository->checkUserByEmail($data['email']);
        if ($checkExistEmail) {
            $checkVerifyEmail = $checkExistEmail->email_verified_at === null;
            if ($checkVerifyEmail) {
                $lastRequest = $userRepository->lastRequest($checkExistEmail->identify_number);
                if ($lastRequest) {
                    $checkExpiresTime = Carbon::parse($lastRequest->expired_at)->isFuture();
                    if ($checkExpiresTime) {
                        return response([
                            'message' => 'A previous email was sent that has not expired yet. Please check your email.',
                            'code' => 1400
                        ], 400);
                    } else {
                        $countOfRequestInOneDay = $userRepository
                            ->countOfRequestInOneDay($checkExistEmail->identify_number);
                        if ($countOfRequestInOneDay === 3) {
                            return response([
                                'message' => 'You have exceeded the maximum number of verification attempts.
                                 Please try again later.',
                                'code' => 429
                            ], 429);
                        }
                    }
                }
                $identifyNumber = $checkExistEmail->identify_number;
                SendVerifyEmail::dispatch($checkExistEmail);

                $countOfRequestInOneDay = $userRepository
                    ->countOfRequestInOneDay($identifyNumber);
                $lastRequest = $userRepository->lastRequest($checkExistEmail->identify_number);

                return response([
                    'message' => 'Finalize your account setup by following the instructions sent to your inbox.',
                    "identify_number" => $identifyNumber,
                    "code" => 200,
                    "countOfRequest" => $countOfRequestInOneDay,
                    "expired_at" => $lastRequest->expired_at,
                ], 200);
            } else {
                return response(
                    [
                        'message' => 'Email address is already in use. Please choose another email.',
                        'code' => 400
                    ], 400);
            }
        } else {
            $user = $userRepository->createUser($data);
            $identifyNumber = $user->identify_number;
            SendVerifyEmail::dispatch($user);
            return response([
                'message' => 'Welcome aboard! Please check your inbox to finalize your account setup',
                "identify_number" => $identifyNumber,
                'code' => 201
            ], 201);
        }
    }
}
