<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
            $data = [
                'id' => $this->id,
                'title' => $this->title,
                'content' => $this->content,
                'type' => $this->type,
                'action' => $this->action,
            ];

            if (strpos($this->title, 'issue')) {
                $data['issue_id'] = $this->issue_id;
                $data['issue'] = $this->whenLoaded('issue');

            } elseif (strpos($this->title, 'invitation')) {
                $data['invitation_id'] = $this->invitation_id;
                $data['invitation'] = $this->whenLoaded('invitation');
            }
            return $data;
    }
}
