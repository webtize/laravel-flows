<?php

namespace Webtize\Flows\Service;

use Webtize\Flows\Models\Flows;

class FlowAPI
{

    public static function insertFlow($title, $body)
    {
        if (is_array($body) || is_object($body)) {
            $body = json_encode($body);
        }
        Flows::insert([
            'title' => $title,
            'body' => $body,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function markPickedUp($id, $status = 2)
    {
        Flows::where('id', $id)->update([
            'status' => $status,
        ]);

    }

    public static function markInProcess($id, $status = 8)
    {
        Flows::where('id', $id)->update([
            'status' => $status,
        ]);

    }

    public static function markCompleteWithResponse($id, $response, $status = 1)
    {
        if (is_array($response) || is_object($response)) {
            $response = json_encode($response);
        }
        Flows::where('id', $id)->update([
            'status' => $status,
            'response' => $response,
        ]);
    }

    public static function markError($id, $error, $status = 9)
    {
        if (is_array($error) || is_object($error)) {
            $error = json_encode($error);
        }
        Flows::where('id', $id)->update([
            'status' => $status,
            'response' => $error,
        ]);
    }

    public static function markComplete($id, $status = 1)
    {
        Flows::where('id', $id)->update([
            'status' => $status,
        ]);
    }

    public static function getPendingFlow($title = null, $id = null, $status = 0)
    {
        if ($title) {
            if ($id) {
                return Flows::where('id', $id)->get();
            }
            return Flows::where('title', $title)->where('status', $status)->get();
        }
        return Flows::where('status', $status)->get();
    }
}
