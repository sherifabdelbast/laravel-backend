<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    public function update(ChangePasswordRequest $request,
                           UserRepository        $userRepository)
    {
        $data = $request->validated();
        $user = $userRepository->getUserById($data['user_id']);

        if (Hash::check($data['old_password'], $user['password'])) {
            if (Hash::check($data['password'], $user['password'])) {
                return response(
                    [
                        'message' => 'The new password cannot be the same as the current password.',
                        'code' => 1400
                    ], 400);
            }
            $userRepository->updateChangePassword($user, $data);
            return response([
                'message' => 'Password changed successfully.',
                'code' => 200
            ], 200);
        }
        return response(
            [
                'message' => 'Current password is incorrect.',
                'code' => 400
            ], 400);
    }
}
