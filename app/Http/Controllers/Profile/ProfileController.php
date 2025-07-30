<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\EditProfileRequest;
use App\Repositories\UserRepository;

class ProfileController extends Controller
{
    public function show(UserRepository $userRepository,
                                        $identifyNumber)
    {
        $user = $userRepository->getUserByIdentifyNumber($identifyNumber);
        $projectsTeamMember = $userRepository->countOfProjects($user->id);
        $bulkOfProjectsId = $projectsTeamMember->pluck('project_id')->toArray();

        $countOfActiveIssues = $userRepository->activeIssues($bulkOfProjectsId);

        $bulkOfTeamMemberId = $projectsTeamMember->pluck('id')->toArray();
        $issuesAssignToMe = $userRepository->issuesAssignToMe($bulkOfTeamMemberId);
        if ($user) {
            return response(
                [
                    'message' => 'Profile information was retrieved successfully.',
                    'user' => $user,
                    'Projects' => $projectsTeamMember->count(),
                    'ActiveIssues' => $countOfActiveIssues->count(),
                    'issuesAssignToMe' => $issuesAssignToMe->count(),
                    'code' => 200
                ], 200);
        }
        return response([
            'message' => 'User does not exist.',
            'code' => 400
        ], 400);
    }

    public function update(EditProfileRequest $request,
                           UserRepository     $userRepository)
    {
        $data = $request->validated();
        $userRepository->updateProfile($data);
        return response(
            [
                'message' => 'Profile updated successfully.',
                'code' => 200
            ], 200);
    }

}
