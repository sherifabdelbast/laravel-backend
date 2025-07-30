<?php

namespace App\Http\Controllers\History;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\ListProjectRequest;
use App\Http\Resources\ProjectHistoryResource;
use App\Models\ProjectHistory;
use App\Repositories\ProjectRepository;

class ProjectHistoryController extends Controller
{
    public function index(ListProjectRequest $request,
                          ProjectRepository  $projectRepository,
                          ProjectHistory     $projectHistory)
    {
        $data = $request->validated();
        $this->authorize('view', [$projectHistory, $data['project_id']]);

        $projectHistory = $projectRepository->getProjectHistory($data);
        $projectHistoryResource = ProjectHistoryResource::collection($projectHistory);
        return response([
            'message' => 'list of Project history',
            'code' => 200,
            'projectHistory' => $projectHistoryResource
        ], 200);
    }
}
