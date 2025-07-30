<?php

use Illuminate\Support\Facades\Http;

function pushNotifications($playerIds, $title, $content, $url, $countOfNotifications)
{
    $headers = [
        'Authorization' => 'Basic ' . env('ONE_SIGNAL_REST_API_KEY'),
        'accept' => 'application/json',
        'content-type' => 'application/json',
    ];

    $response = Http::withHeaders($headers)
        ->post('https://onesignal.com/api/v1/notifications', [
            'app_id' => env('ONE_SIGNAL_APP_ID'),
//        "included_segments" => ["Active Users"],
            "include_player_ids" => $playerIds,
            "headings" => [
                'en' => $title
            ],
            "contents" => [
                'en' => $content
            ],
            "url" => $url,
            'count_of_notifications' => $countOfNotifications,
            'name' => 'crm.ps'
        ]);

    return $response->body();
}
