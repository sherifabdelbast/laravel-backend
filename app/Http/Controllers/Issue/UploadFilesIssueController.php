<?php

namespace App\Http\Controllers\Issue;

use App\Http\Controllers\Controller;
use App\Http\Requests\Issue\DeleteFilesForIssueRequest;
use App\Http\Requests\Issue\UploadFilesIssuRequest;
use App\Models\Issue;
use App\Repositories\IssueRepository;

class UploadFilesIssueController extends Controller
{
    public function store(UploadFilesIssuRequest $request,
                          IssueRepository        $issueRepository,
                          Issue                  $issue)
    {
        $data = $request->validated();
        $this->authorize('update', [$issue, $data['project_id']]);

        if (array_key_exists('files', $data)) {
            $files = $data['files'];
             foreach ($files as $file) {
                $issueRepository->uploadIssueFiles($data, $file);
             }
            return response([
                'message' => 'Issue Files uploaded successfully',
                'code' => 200,
            ], 200);
        }
    }

    public function destroy(DeleteFilesForIssueRequest $request,
                            IssueRepository            $issueRepository,
                            Issue                      $issue)
    {
        $data = $request->validated();
        $this->authorize('update', [$issue, $data['project_id']]);
        $fileId = $data['file_id'];
        $issueRepository->deleteIssueFile($fileId);

        return response([
            'message' => 'Issue Files deleted successfully',
            'code' => 200,
        ], 200);

    }

}
