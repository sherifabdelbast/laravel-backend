<?php

namespace App\Http\Resources;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
//        return [
//            'id' => $this->id,
//            'read_at' => $this->read_at,
//            'created_at' => $this->created_at,
//            'updated_at' => $this->updated_at,
//            'notification' => [
//                'title' => $this->notification->title,
//                'content' => $this->notification->content,
//                'type' => $this->notification->type,
//                'action' => $this->notification->action,
////                if (strpos($this->notification->title, 'issue')) {
////                    'issue' => [
////                        'key' => $this->notification?->issue?->key,
////                        'title' => $this->notification?->issue?->title,
////                    ],
////                }
////            ]
//        ];

//        if ($this instanceof Notification) {
//            $data['title'] = $this->title;
//            $data['content'] = $this->content;
//            $data['type'] = $this->type;
//            $data['action'] = $this->action;
//
//            if (strpos($this->title, 'issue')) {
//                $data['issue_id'] = $this->issue_id;
//                $data['issue'] = $this->whenLoaded('issue');
//            } elseif (strpos($this->title, 'invitation')) {
//                $data['invitation_id'] = $this->invitation_id;
//                $data['invitation'] = $this->whenLoaded('invitation');
//            }
//
//            $data['project_id'] = $this->project_id;
//            $data['project'] = $this->whenLoaded('project');
//
//        }

//        return $data;
    }
}
