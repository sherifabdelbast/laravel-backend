<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\StoreUserPlayerIdRequest;
use App\Repositories\UserRepository;

class UserPlayerIdsController extends Controller
{
    public function store(StoreUserPlayerIdRequest $request,
                          UserRepository           $userRepository)
    {
        $data = $request->validated();
        $user = $userRepository->getUserById($data['user_id']);
        $user->player_ids = $data['player_id'];
        $user->save();

        return response([
            'message' => 'playerId store successfully',
            'code' => 200
        ], 200);
    }
}
