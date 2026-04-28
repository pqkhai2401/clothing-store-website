<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use App\Helpers\ResponseUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppBaseController extends Controller
{
    public function sendResponse($result, $message = "Thành Công!."): JsonResponse
    {
        return Response::json(ResponseUtil::success($result, $message));
    }

    public function sendError($error, $code = 404): JsonResponse
    {
        return Response::json(ResponseUtil::error($error), $code);
    }

    public function sendValidationError($message): JsonResponse
    {
        return Response::json(ResponseUtil::error($message), 422);
    }

    public function sendValidationErrorWithDetails($errors): JsonResponse
    {
        $errorMessage = 'Validation failed: '.implode(', ', $errors);
        return Response::json([
            'success' => false,
            'message' => $errorMessage,
            'errors' => $errors
        ], 422);
    }

    public function sendResponseRaw($data, $code = 200): JsonResponse
    {
        return Response::json($data, $code);
    }
}
