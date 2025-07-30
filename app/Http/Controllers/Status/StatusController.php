<?php

namespace App\Http\Controllers\Status;

use App\Http\Controllers\Controller;
use App\Http\Requests\Status\DeleteStatusRequest;
use App\Http\Requests\Status\ListOfStatusRequest;
use App\Http\Requests\Status\StoreStatusRequest;
use App\Http\Requests\Status\UpdateStatusRequest;
use App\Models\Status;
use App\Repositories\ProjectRepository;
use App\Repositories\StatusRepository;
use Illuminate\Support\Facades\DB;

class StatusController extends Controller
{
    public function index(StatusRepository $statusRepository)
    {
        $userId = auth()->id();
        $allStatuses = $statusRepository->getAllStatusesOfAllProjects($userId);
        return response([
            'message' => 'all statuses of all projects',
            'code' => 200,
            'statuses' => $allStatuses
        ], 200);
    }

    public function store(StoreStatusRequest $request,
                          StatusRepository   $statusRepository,
                          Status             $status)
    {
        $data = $request->validated();

        $this->authorize('create', [$status, $data['project_id']]);
        DB::beginTransaction();
        try {
            $checkName = $statusRepository->checkNameStatusIsUnique($data);
            if ($checkName) {
                return response([
                    'message' => 'The name already exists.',
                    'code' => 400
                ], 400);
            }
            $statusRepository->createNewStatus($data);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return response([
            'message' => 'Status created successfully',
            'code' => 201
        ], 201);
    }

    public function show(ListOfStatusRequest $request,
                         StatusRepository    $statusRepository)
    {
        $data = $request->validated();

        $allStatuses = $statusRepository->listOfStatusesInProject($data['project_id']);
        return response([
            'message' => 'list of statuses',
            'code' => 200,
            'statuses' => $allStatuses
        ], 200);
    }

    public function update(UpdateStatusRequest $request,
                           StatusRepository    $statusRepository,
                           Status              $status)
    {
        $data = $request->validated();

        $this->authorize('update', [$status, $data['project_id']]);
        $statusRepository->updateStatus($data);

        return response([
            'message' => 'Status Updated successfully',
            'code' => 200
        ], 200);
    }

    public function destroy(DeleteStatusRequest $request,
                            StatusRepository    $statusRepository,
                            Status              $status)
    {
        $data = $request->validated();
        $this->authorize('delete', [$status, $data['project_id']]);
        DB::beginTransaction();
        try {
            $status = $statusRepository->getStatusById($data);
            $contOfStatus = $statusRepository->countOfStatus($data, $status);
            if ($contOfStatus == 1) {
                return response([
                    'message' => 'You cannot delete this status because it is the only one of its type',
                    'code' => 400
                ], 400);
            }
            $statusRepository->deleteStatus($data, $status);
            $statusRepository->storeStatusDeletedInProjectHistory($data, $status->id);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return response([
            'message' => 'Status deleted successfully',
            'code' => 200
        ], 200);
    }
}
