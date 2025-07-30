<?php

namespace App\Http\Controllers\Issue;

use App\Http\Controllers\Controller;
use App\Http\Requests\Issue\AddLabelIssueRequest;
use App\Http\Requests\Issue\DeleteLabelRequest;
use App\Http\Requests\Issue\ListLabelRequest;
use App\Models\Issue;
use App\Repositories\IssueRepository;
use Illuminate\Http\Request;

class LabelController extends Controller
{

    public function index(ListLabelRequest $request,
                           IssueRepository $issueRepository,
                         )
    {
        $data= $request->validated();
        return $issueRepository->listOfLabel($data);
    }
    public function store(AddLabelIssueRequest $request,
                          IssueRepository $issueRepository,
                          Issue                  $issue)
   {
       $data= $request->validated();
       $this->authorize('update', [$issue, $data['project_id']]);
       $issueRepository->createLabel($data);
        return response([
            'message' => 'Label created successfully',
            'code' => 201,
        ], 201);

    }

    public function destroy(DeleteLabelRequest $request,
                            IssueRepository $issueRepository,
                            Issue                  $issue)
     {
         $data= $request->validated();
        $this->authorize('update', [$issue, $data['project_id']]);

        $issueRepository->deleteLabel($data);
        return response([
            'message' => 'Label deleted successfully',
            'code' => 200
        ], 200);
    }
}
