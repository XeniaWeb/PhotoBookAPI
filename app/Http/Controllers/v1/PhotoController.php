<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\PhotoResource;
use App\Models\Photo;
use App\Models\User;
use App\Services\v1\PhotoService;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PhotoController extends Controller
{
    protected PhotoService $service;

    function __construct(PhotoService $service)
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
        $photos = $this->service->all($request->input());

        return response(['cards' => PhotoResource::collection($photos), 'message' => 'Retrieved successfully']);
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
                'album_id' => 'required',
                'description' => 'required|min:60|max:1000',
                'photo' => 'required|mimes:jpeg,png,bmp|unique:photos',
            ]);

            if ($validator->fails()) {
                return response(['error' => $validator->errors(), 'message' => 'Validation Error'], 418);
            }

            $card = $request->file('photo')->store('/', 'photos');

            if (!$card) {
                return response(['message' => 'Error file upload'], 500);
            }

            $photo = Photo::create($data);

            $photo->update(['photo' => $card]);
            $photo = $this->service->formatToJson($photo);

            return response(['card' => new PhotoResource($photo), 'message' => 'Created successfully'], 201);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Photo $photo
     * @return Response
     */
    public function show(Photo $photo)
    {
        $photo = $this->service->singlePhoto($photo, request()->input());

        return response(['card' => new PhotoResource($photo), 'message' => 'Retrieved successfully'], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Photo $photo
     * @return Response
     */
    public function update(Request $request, Photo $photo): Response
    {
        // PUT -- replace; validate
        // PATCH -- partial update

        try {
            if ($request->isMethod('patch')) {
                $photo = $this->service->patch($photo, $request->input());
            } else {
                $photo = $this->service->put($photo, $request->input());
            }

            return response(['запрос' => $request->input(), 'card' => new PhotoResource($photo), 'message' => 'Updated succesfully'], 201);
        } catch (ValidationException $ve) {
            return response(['errors' => $ve->validator->errors(), 'message' => 'Validation Error'], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Photo $photo
     * @return Response
     * @throws \Exception
     */
    public function destroy(Photo $photo): Response
    {
//   Этот код для локального компьютера
        if (file_exists(public_path('storage\\photos\\' . $photo->photo))) {
            unlink(public_path('storage\\photos\\' . $photo->photo));
            $photo->delete();
            return response(['message' => 'Deleted.'], 200);
        } else {
            return response(['message' => 'File not exist', 'file name' => $photo->photo], 404);
        }

//    Этот Код для хостинга

//        if (file_exists(env('LINK_IMG') . $photo->photo )) {
//            unlink(env('LINK_IMG') . $photo->photo );
//            $photo->delete();
//
//            return response(['message' => 'Deleted !'], 200);
//        } else {
//            return response(['message' => 'File not exist', 'file name' => env('LINK_IMG') . $photo->photo  ], 404);
//        }
    }

    public function toggleLike(Request $request): Application|Response
    {
        $photo = Photo::find($request->id);
        $author = User::find(Auth::id());

        $photo->likes()->toggle($author->id);

        $likes = $photo->loadCount('likes', 'comments');
        $likes_of_author = $author->likes->map(function ($photo) {
            return $photo->id;
        });

        $likesCount = $likes->likes_count;

        return response(['likesCount' => $likesCount, 'likesOfAuthor' => $likes_of_author, 'message' => 'Like обновлен!'], 200);
    }
}
