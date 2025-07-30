<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'status' => $this->status,
            'type' => $this->type,
            'action' => $this->action,
            'new_data' => $this->new_data,
            'old_data' => $this->old_data,
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        if (strpos($this->status, 'project')) {
            $data ['project_id'] = $this->project_id;
            $data['project'] = $this->whenLoaded('project');

        } else if (strpos($this->status, 'sprint')) {
            $data['sprint_received_action'] = $this->sprint_received_action;
            $data['sprint_which_received_action'] = $this->whenLoaded('sprintWhichReceivedAction');

        } else if (strpos($this->status, 'issue')) {
            $data['issue_id'] = $this->issue_id;
            $data['issue'] = $this->whenLoaded('issue');

        } else if (strpos($this->status, 'status')) {
            $data['status_received_action'] = $this->status_received_action;
            $data['status_which_received_action'] = $this->whenLoaded('statusWhichReceivedAction');

        } else if (strpos($this->status, 'team')) {
            $data['user_received_action'] = $this->user_received_action;
            $data['user_who_received_action'] = $this->whenLoaded('userWhoReceivedAction');

        } else if (strpos($this->status, 'role')) {
            $data['role_id'] = $this->role_id;
            $data['role'] = $this->whenLoaded('role');
        }

        return $data;
    }
}
