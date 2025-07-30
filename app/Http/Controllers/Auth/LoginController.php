<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Jobs\SendVerifyEmail;
use App\Notifications\VerifyEmailNotification;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(LoginRequest   $request,
                          UserRepository $userRepository)
    {
        $data = $request->validated();
        $email = $data['email'];
        $checkExistEmail = $userRepository->checkUserByEmail($email);
        if ($checkExistEmail) {
            $checkVerifyEmail = $checkExistEmail->email_verified_at === null;
            if ($checkVerifyEmail) {
                $lastRequest = $userRepository->lastRequest($checkExistEmail->identify_number);
                if ($lastRequest) {
                    $checkExpiresTime = Carbon::parse($lastRequest->expired_at)->isFuture();
                    if ($checkExpiresTime) {
                        return response([
                            'message' => 'A verification email was sent earlier and is still valid.
                             Please check your email.',
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
                $lastRequest = $userRepository->lastRequest($identifyNumber);

                return response([
                    'message' => 'Verification email sent successfully. Please check your email.',
                    "identify_number" => $identifyNumber,
                    "code" => 1200,
                    "countOfRequest" => $countOfRequestInOneDay,
                    "expired_at" => $lastRequest->expired_at,
                ], 200);

            } else if (Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
                $user = Auth::user();
                Auth::login($user, $remember = true);
                $defaultProject = $userRepository->defaultProject($user->id);
                return response([
                    'message' => 'Welcome back to Taskat! Your projects are waiting for you.',
                    'code' => 200,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'identify_number' => $user->identify_number,
                        'photo' => $user->photo,
                        'email_verified_at' => $user->email_verified_at,
                        'project_id' => $defaultProject?->project_id ?? null,
                        'project_identify' => $defaultProject->project->project_identify ?? null,
                    ],
                ], 200);
            }
        }
        return response(
            [
                'message' => 'Incorrect email or password. Please verify your credentials.',
                'code' => 400
            ], 400);
    }
}
