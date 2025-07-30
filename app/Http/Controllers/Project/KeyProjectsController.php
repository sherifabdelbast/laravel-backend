<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Repositories\ProjectRepository;

class KeyProjectsController extends Controller
{
    public function index(ProjectRepository $projectRepository)
    {
        $userId = auth()->id();
        $keyProjects = $projectRepository->keyOfProjects($userId);

        return response([
            'message' => 'get all key of projects',
            'code' => 200,
            'key' => $keyProjects
        ], 200);
    }
}
