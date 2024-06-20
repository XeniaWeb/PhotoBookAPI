<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\AlbumResource;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Http\Request;
use App\Services\v1\AlbumService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AlbumController extends Controller
{
    protected AlbumService $service;

    function __construct(AlbumService $service)
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
        $albums = $this->service->all($request->input());

        return response(['albums' => AlbumResource::collection($albums), 'message' => 'Retrieved successfully']);
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
            $data = $this->service->formatFromJson($request->all());

            $validator = Validator::make($data, [
                'title' => 'required|max:255',
                'author_id' => 'required',
                'description' => 'required|min:60',
                'preview' => 'required',
            ]);

            if ($validator->fails()) {
                return response(['error' => $validator->errors(), 'message' => 'Validation Error'], 422);
            }

            $preview = $request->file('preview')->store('/', 'photos');

            if (!$preview) {
                return response(['message' => 'Error file upload'], 500);
            }

            $album = Album::create($data);
            $album->update(['preview' => $preview]);
            $album = $this->service->formatToJson($album);

            return response(['album' => new AlbumResource($album), 'message' => 'Created successfully'], 201);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Album $album
     * @return Response
     */
    public function show(Album $album): Response
    {
        $album = $this->service->single($album, request()->input());

        return response(['album' => new AlbumResource($album), 'message' => 'Retrieved successfully'], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Album $album
     * @return Response
     */
    public function update(Request $request, Album $album): Response
    {
        // POST -- full update with ?file
        // PUT -- replace; validate
        // PATCH -- partial update

        try {
            if ($request->isMethod('post')) {
                $album = $this->service->postUpdate($request);
            } elseif ($request->isMethod('patch')) {
                $album = $this->service->patch($album, $request->input());
            } else {
                $album = $this->service->put($album, $request->input());
            }

            return response(['album' => $album, 'message' => 'Updated successfully'], 201);
        } catch (ValidationException $ve) {
            return response(['errors' => $ve->validator->errors(), 'message' => 'Validation Error'], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Album $album
     * @return Response
     */
    public function destroy(Album $album): Response
    {
        $query = Photo::query()->where('album_id', $album->id)->first();
        if (!$query) {
            $album->delete();

            return response(['message' => 'Deleted.'], 200);
        }

        return response(['message' => 'Пытаемся удалять, но в альбоме есть фотки... Отмена!'], 418);
    }
}
