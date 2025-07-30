<?php

namespace App\Http\Controllers\Sprint;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sprint\DeleteSprintRequest;
use App\Http\Requests\Sprint\EditSprintRequest;
use App\Http\Requests\Sprint\StoreSprintRequest;
use App\Http\Requests\Status\ListOfStatusRequest;
use App\Models\Sprint;
use App\Repositories\ProjectRepository;
use App\Repositories\SprintRepository;
use Illuminate\Support\Facades\DB;

class SprintController extends Controller
{
    public function index(ListOfStatusRequest $request,
                          SprintRepository    $sprintRepository)
    {
        $data = $request->validated();
        $sprints = $sprintRepository->getAllSprintInThisProject($data['project_id'])
            ->get();
        return response([
            'message' => 'list of statuses.',
            'code' => 200,
            'sprints' => $sprints
        ], 200);
    }

    public function store(StoreSprintRequest $request,
                          SprintRepository   $sprintRepository,
                          Sprint             $sprint)
    {
        $data = $request->validated();
        $this->authorize('create', [$sprint, $data['project_id']]);
        DB::beginTransaction();
        try {
            $sprintRepository->storeNewSprint($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return response([
            'message' => 'Sprint has been created successfully.',
            'code' => 200
        ], 200);
    }

    public function update(EditSprintRequest $request,
                           SprintRepository  $sprintRepository,
                           Sprint            $sprint)
    {
        $data = $request->validated();

        $this->authorize('destroy', [$sprint, $data['project_id']]);
        $sprintId = $data['sprint_id'];
        $editSprint = $sprintRepository->editSprint($data, $sprintId);
        return response([
            "message" => "Sprint has been updated successfully.",
            'code' => 200,
            'data' => $editSprint
        ], 200);
    }

    public function destroy(DeleteSprintRequest $request,
                            SprintRepository    $sprintRepository,
                            Sprint              $sprint)
    {
        $data = $request->validated();

        $this->authorize('destroy', [$sprint, $data['project_id']]);
        DB::beginTransaction();
        try {
            $sprint = $sprintRepository->getBySprintId($data['sprint_id']);
            $sprint->delete();
            $sprintRepository->actionsThatOccurWhenDeletedSprint($data);
            $sprintRepository->storeSprintDeletedInProjectHistory($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return response([
            "message" => "Sprint has been deleted successfully.",
            'code' => 200,
        ], 200);
    }
}
