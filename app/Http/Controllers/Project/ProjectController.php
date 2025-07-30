<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\FavoriteProjectsRequest;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Project;
use App\Repositories\ProjectRepository;

## TODO:: This code needs review and improvement ^_^
class ProjectController extends Controller
{
    public function index(ProjectRepository $projectRepository)
    {
        $userId = auth()->id();
        $archiveProject = $projectRepository->getAllArchiveProjectToThisUser($userId);
        $bulkArchiveProject = $archiveProject->pluck('id')->unique()->toArray();

        $favoriteProject = $projectRepository->getAllFavoriteProjectToThisUser($userId, $bulkArchiveProject);
        $bulkFavoriteProject = $favoriteProject->pluck('id')->unique()->toArray();

        $projects = $projectRepository->getAllProjectsCreated($userId, $bulkArchiveProject);
        $bulkProjects = $projects->pluck('id')->unique()->toArray();

        $inviteProjects = $projectRepository->getAllInvitedProjects($userId, $bulkProjects, $bulkArchiveProject);

//        return [
//            $archiveProject,
//            $favoriteProject,
//            $projects,
//            $inviteProjects
//        ];

        $favoriteProjectsData = $favoriteProject->map(function ($item) use ($projectRepository) {
            $project = $item;

            $activeIssues = $projectRepository->getActiveIssues($project->id);
            $project->is_favorite = true;
            $project->activeIssues = $activeIssues->count();
            return $project;
        })->values()->toArray();

        $projectsData = $projects->map(function ($item) use ($bulkFavoriteProject, $projectRepository) {
            $project = $item;

            $activeIssues = $projectRepository->getActiveIssues($project->id);
            if (in_array($project->id, $bulkFavoriteProject)) {
                $project->is_favorite = true;
            } else {
                $project->is_favorite = false;
            }
            $project->activeIssues = $activeIssues->count();
            return $project;
        });

        $inviteProjectsData = $inviteProjects->map(function ($item) use ($bulkFavoriteProject, $projectRepository) {
            $project = $item;

            $activeIssues = $projectRepository->getActiveIssues($project->id);
            if (in_array($project->id, $bulkFavoriteProject)) {
                $project->is_favorite = true;
            } else {
                $project->is_favorite = false;
            }
            $project->activeIssues = $activeIssues->count();
            return $project;
        });

        return response([
            "message" => "Retrieve all projects successfully.",
            'code' => 200,
            'ArchiveProjects' => $archiveProject,
            'FavoriteProjects' => $favoriteProjectsData,
            'projects' => $projectsData,
            'inviteProjects' => $inviteProjectsData,
        ], 200);
    }

    public function store(StoreProjectRequest $request,
                          ProjectRepository   $projectRepository)
    {
        $data = $request->validated();
        $project = $projectRepository->createProject($data);
        return response([
            'message' => 'Your new project has been successfully created. Enjoy.',
            'code' => 201,
            'project_id' => $project->id,
            'project_identify' => $project->project_identify
        ], 201);
    }

    public function show(FavoriteProjectsRequest $request,
                         ProjectRepository       $projectRepository)
    {
        $data = $request->validated();
        $projectId = $data['project_id'];
        $userId = $data['user_id'];
        $project = $projectRepository->checkProjectExists($projectId, $userId);
        $projectRepository->getDetailsToProject($project);
        $projectRepository->lastProjectOpenDefault($projectId, $userId);
        return response([
            "message" => "Retrieve project data successfully.",
            'code' => 200,
            'project' => $project
        ], 200);
    }

    public function update(UpdateProjectRequest $request,
                           ProjectRepository    $projectRepository,
                           Project              $project)
    {
        $data = $request->validated();
        $this->authorize('update', [$project, $data['project_id']]);

        $updateProjectData = $projectRepository->updateProject($data);
        return response([
            "message" => "Project has been updated successfully.",
            'code' => 200,
            "data" => $updateProjectData
        ], 200);
    }
}
