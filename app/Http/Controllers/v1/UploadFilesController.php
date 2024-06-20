<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\v1\UploadFilesService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class UploadFilesController extends Controller
{
    protected UploadFilesService $service;

    function __construct(UploadFilesService $service)
    {
        $this->service = $service;
        // $this->middleware('auth:sanctum');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function uploadAvatar(Request $request): Response
    {
        return $this->service->uploadAvatar($request);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function uploadCover(Request $request): Response
    {
        return $this->service->uploadCover($request);
    }
}
