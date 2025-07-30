<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    public function update(ResetPasswordRequest $request,
                            UserRepository $userRepository)
    {
        $data= $request->validated();
        $identifyNumber = $data['identify_number'];
        $userRepository->updatePassword($data, $identifyNumber);

        return response([
            'message' => 'Password reset successfully.',
            'code' => 200
        ], 200);





    }
}
