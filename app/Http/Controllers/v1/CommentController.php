<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Services\v1\CommentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class CommentController extends Controller
{
    protected CommentService $service;

    function __construct(CommentService $service)
    {
        $this->service = $service;
        // $this->middleware('auth:sanctum', [
        //     'except' => ['index', 'show']
        // ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $comments = $this->service->all($request->input());

        return response(['comments' => $comments, 'message' => 'Retrieved successfully']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        try {

            $comment = $this->service->createNewComment($request->input());

            return response(['comment' => $comment, 'message' => 'New comment created successfully!'], 201);
        } catch (ValidationException $ve) {
            return response(['errors' => $ve->validator->errors(), 'message' => 'Validation Error'], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Comment $comment
     * @return Response
     */
    public function show(Comment $comment): Response
    {
        $comment = $this->service->single($comment, request()->input());

        return response(['comment' => $comment, 'message' => 'Retrieved successfully'], 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Comment $comment
     * @return Response
     */
    public function update(Request $request, Comment $comment): Response
    {
        // PUT -- replace; validate
        // PATCH -- partial update

        try {
            if ($request->isMethod('patch')) {
                $comment = $this->service->patch($comment, $request->input());
            } else {
                $comment = $this->service->put($comment, $request->input());
            }

            return response(['request' => $request->input(), 'comment' => $comment, 'message' => 'Updated successfully'], 201);
        } catch (ValidationException $ve) {
            return response(['errors' => $ve->validator->errors(), 'message' => 'Validation Error'], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Comment $comment
     * @return Response
     * @throws \Exception
     */
    public function destroy(Comment $comment): Response
    {
        $comment->delete();

        return response(['message' => 'Deleted.'], 200);
    }
}
