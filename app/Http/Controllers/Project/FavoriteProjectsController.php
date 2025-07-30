<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\FavoriteProjectsRequest;
use App\Repositories\ProjectRepository;

class FavoriteProjectsController extends Controller
{
    public function __invoke(FavoriteProjectsRequest $request,
                             ProjectRepository       $projectRepository)
    {
        $data = $request->validated();
        $checkFavorite = $projectRepository->checkIfProjectInFavorites($data);
        if (!$checkFavorite) {
            $projectRepository->storeProjectInFavorites($data);
            return response([
                'message' => 'Project has been added to your favorites.',
                'code' => 200
            ], 200);
        }
        $checkFavorite->delete();
        return response([
            'message' => 'Project has been removed from your favorites.',
            'code' => 200
        ], 200);
    }

}
