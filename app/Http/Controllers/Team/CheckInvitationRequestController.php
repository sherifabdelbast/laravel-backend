<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\inviteIdentifyRequest;
use App\Repositories\InvitationRepository;
use Illuminate\Support\Facades\Auth;

class CheckInvitationRequestController extends Controller
{
    public function __invoke(inviteIdentifyRequest $request,
                             InvitationRepository  $invitationRepository)
    {
        $data = $request->validated();
        $inviteIdentify = $data['invite_identify'];
        $checkInvitationExists = $invitationRepository
            ->checkInvitationByinviteIdentify($inviteIdentify);
        if ($checkInvitationExists) {
            if (Auth::user()) {
                $userAuth = Auth::user();
                $checkUser = $invitationRepository
                    ->checkIfTheLoggedInUserIsTheInvitee($userAuth, $checkInvitationExists->member_id);
                if (!$checkUser) {
                    return response([
                        'message' => 'This invitation is not meant for you.',
                        'code' => 1404,
                    ], 400);
                }
            }
            $lastInvitationRequest = $invitationRepository
                ->lastInvitationRequest($checkInvitationExists->project_id, $checkInvitationExists->member_id);
            if ($lastInvitationRequest->invite_identify === $inviteIdentify) {
                $invitationIsAlreadyUsed = $invitationRepository->invitationIsAlreadyUsed($inviteIdentify);
                if ($invitationIsAlreadyUsed) {
                    return response([
                        "message" => "Invitation Expired:
                        This invitation has already been used and is no longer valid.",
                        "code" => 1400
                    ], 400);
                }
                $user = $invitationRepository->getUser($checkInvitationExists->member_id);
                $checkIfUserNotComplete = $user->email_verified_at === null;
                if ($checkIfUserNotComplete) {
                    $requestVerify = $invitationRepository->sendRequestToCompleteRegister($user->identify_number);
                    return response([
                        'message' => 'Please complete your account information.',
                        'code' => 400,
                        'identify_number' => $requestVerify->identify_number,
                        'token' => $requestVerify->token
                    ], 400);
                }
                $userLongedIn = Auth::user();
                if ($userLongedIn) {

                    return response([
                        'message' => 'Invitation information is correct and you are logged in.',
                        'code' => 200,
                        'data' => $checkInvitationExists::query()
                            ->where('invite_identify', '=', $inviteIdentify)
                            ->with('project')
                            ->with('user')
                            ->first()
                    ], 200);
                }
                return response([
                    'message' => 'Unauthorized',
                    'code' => 401,
                    'identify_number' => $user->identify_number
                ], 401);
            }
            return response([
                'message' => 'Invitation Expired: This invitation has already been used and is no longer valid.',
                'code' => 2400
            ], 400);
        }
        return response([
            'message' => 'This invitation does not exist.',
            'code' => 1404,
        ], 400);
    }

}
