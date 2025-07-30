<?php

namespace App\Repositories;

use App\Models\Clipboard;
use App\Models\Issue;
use App\Models\RequestForgetPassword;
use App\Models\RequestHistory;
use App\Models\Sprint;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class UserRepository
{
    public function checkUserByEmail($email)
    {
        return User::query()
            ->email($email)
            ->first();
    }

    public function lastRequest($identifyNumber)
    {
        return RequestHistory::query()
            ->where('identify_number', '=', $identifyNumber)
            ->latest('id')
            ->first();
    }

    public function countOfRequestInOneDay($identifyNumber)
    {
        return RequestHistory::query()
            ->where('identify_number', '=', $identifyNumber)
            ->whereDate('created_at', '=', now()->format('y-m-d'))
            ->count();
    }

    public function createUser($data)
    {
        $identifyNumber = Str::uuid();
        while (User::query()
            ->where('identify_number', '=', $identifyNumber)
            ->exists()
        ) {
            $identifyNumber = Str::uuid();
        }
        return User::query()
            ->create([
                'email' => $data['email'],
                'identify_number' => $identifyNumber
            ]);
    }

    public function defaultProject($userId)
    {
        return Clipboard::query()
            ->where('user_id', '=', $userId)
            ->where('default', '=', 1)
            ->first();
    }

    public function checkUserByIdentifyNumber($identifyNumber)
    {
        return User::query()
            ->where('identify_number', '=', $identifyNumber)
            ->first();
    }

    public function completeUserInformation($data, $checkUser)
    {
        $uploadImage = null;
        if (isset($data['photo'])) {
            $personalImage = $data['photo'];
            $uploadImage = Str::random(10) . '-'
                . $personalImage->hashName();
            Storage::disk('public')->put('upload/personal_photo/' . $uploadImage, file_get_contents($personalImage));
        }
        Auth::login($checkUser, $remember = true);

        $checkUser->update([
            'name' => $data['name'],
            'photo' => $uploadImage,
            'password' => bcrypt($data['password']),
            'email_verified_at' => now()
        ]);
    }

    public function getUserById($userId)
    {
        return User::query()
            ->where('id', '=', $userId)
            ->first();
    }

    public function countOfProjects($userId)
    {
        return Team::query()
            ->where('user_id', '=', $userId)
            ->get();
    }

    public function openSprintForAllProjects($bulkOfProjectsId)
    {
        return Sprint::query()
            ->whereIn('project_id', $bulkOfProjectsId)
            ->where('is_open', '=', 1)
            ->get()
            ->pluck('id')
            ->toArray();
    }

    public function activeIssues($bulkOfProjectsId)
    {
        $sprintsId = $this->openSprintForAllProjects($bulkOfProjectsId);
        return Issue::query()
            ->whereIn('sprint_id', $sprintsId)
            ->get();
    }

    public function issuesAssignToMe($bulkOfTeamMemberId)
    {
        return Issue::query()
            ->whereIn('assign_to', $bulkOfTeamMemberId)
            ->get();
    }

    public function updateChangePassword($user, $data)
    {
        return $user->update([
            'password' => $data['password'],
        ]);
    }

    public function getUserByIdentifyNumber($userId)
    {
        return User::query()
            ->identifyNumber($userId)
            ->first();
    }

    public function updateProfile($data)
    {
        $user = $this->getUserById($data['user_id']);
        $skills = null;
        if (array_key_exists('photo', $data) && $data['photo'] != null) {
            $personalImage = $data['photo'];
            $uploadImage = Str::random(10) . '-'
                . $personalImage->hashName();
            Storage::disk('public')->put('upload/personal_photo/' . $uploadImage, file_get_contents($personalImage));
            $user->update([
                'photo' => $uploadImage,
            ]);
        }

        if (isset($data['skills']) && $data['skills'] != null) {
            $skills = array_unique($data['skills']);
        }
        return $user->update([
            'name' => $data['name'],
            'job_title' => $data['job_title'],
            'skills' => $skills,
            'phone' => $data['phone'] ?? null,
            'location' => $data['location'] ?? null,
        ]);
    }

    public function deleteAllOldCode($data)
    {
        RequestForgetPassword::query()
            ->where('email', '=', $data['email'])
            ->delete();
    }

    public function checkLastRequestForForgetPassword($identifyNumber)
    {
        return RequestForgetPassword::query()
            ->where('identify_number', '=', $identifyNumber)
            ->latest('id')
            ->first();
    }

    public function lastRequestForForget($identifyNumber)
    {
        return RequestForgetPassword::query()
            ->where('identify_number', '=', $identifyNumber)
            ->latest('id')
            ->first();
    }

    public function countOfRequestInOneDayForForget($identifyNumber)
    {
        return RequestForgetPassword::query()
            ->where('identify_number', '=', $identifyNumber)
            ->whereDate('created_at', '=', now()->format('y-m-d'))
            ->count();
    }

    public function updatePassword($data, $identifyNumber)
    {
        $LastRequest = $this->checkLastRequestForForgetPassword($identifyNumber);
        $LastRequest->update([
            'previously_used' => 1
        ]);
        return User::query()
            ->where('identify_number', '=', $identifyNumber)
            ->update([
                'password' => Hash::make($data['password']),
            ]);
    }

    public function getIdentifyNumber($token)
    {
        return RequestHistory::query()
            ->where('token', '=', $token)
            ->first();
    }

    public function storePlayerId($data, $user)
    {
        $user->update([
            'player_ids' => $data['player_id']
        ]);
    }
}
