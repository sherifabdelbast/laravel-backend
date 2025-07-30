<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\ArchiveProjectRequest;
use App\Models\Project;
use App\Repositories\ProjectRepository;

class ArchiveProjectController extends Controller
{
    public function destroy(ArchiveProjectRequest $request,
                            ProjectRepository     $projectRepository,
                            Project               $project)
    {
        $data = $request->validated();
        $this->authorize('delete', [$project, $data['project_id']]);

        $projectRepository->storeProjectInArchive($data);
        return response([
            'message' => 'Shutdown complete! Your project has been closed and archived. Thanks for your hard work!',
            'code' => 200
        ], 200);
    }
}
