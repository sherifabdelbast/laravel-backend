<?php

namespace App\Http\Controllers\Status;

use App\Http\Controllers\Controller;
use App\Http\Requests\Status\MoveStatusRequest;
use App\Models\Status;
use App\Repositories\ProjectRepository;
use App\Repositories\StatusRepository;
use Illuminate\Http\Request;

class MoveStatusController extends Controller
{

    public function update(MoveStatusRequest $request,
                           StatusRepository  $statusRepository,
                           Status            $status)
    {
        $data = $request->validated();

        $this->authorize('move', [$status, $data['project_id']]);

        $statusRepository->moveColumnStatus($data);

        return response([
            'message' => 'Status Column moved successfully',
            'code' => 200
        ], 200);
    }
}
