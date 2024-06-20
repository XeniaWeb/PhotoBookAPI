<?php

namespace App\Services\v1;

use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AlbumService extends ResourceService
{
    protected $includes = ['author', 'photos'];

    protected $queryFields = [
        'title' => 'title',
        'author.id' => 'author_id',
        'createdat' => 'created_at',
        'updatedat' => 'updated_at',
        'id' => 'id'
    ];

    protected $sortFields = [
        'title' => 'title',
        'author.id' => 'author_id',
        'createdat' => 'created_at',
        'updatedat' => 'updated_at',
        'id' => 'id'
    ];
    protected $columnMap = [
        'title' => 'title',
        'authorId' => 'author_id',
        'description' => 'description',
        'preview' => 'preview',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
        'id' => 'id'
    ];

    public function all($input)
    {
        $parms = $this->buildParameters($input);

        $query = Album::offset($parms['offset'])->limit($parms['limit']);

        if (!empty($parms['sort'])) {
            $query = $query->orderBy($parms['sort'][0], $parms['sort'][1]);
        }

        if (!empty($parms['include'])) {
            $query->with($parms['include']);
        }

        if (!empty($parms['where'])) {
            $query->where($parms['where']);
        }

        return $query->get()->map(function ($album) use ($parms) {
            return $this->formatToJson($album, $parms['include']);
        });
    }

    public function postUpdate($request)
    {
        $data = $request->input();
        $this->validateSome($data);

        $album = Album::query()->where('id', $request->id)->first();

        if ($request->file()) {
            $album = $this->uploadFile($request, 'preview', 'photos', $album);
        }

        $data = $this->convertToActual($request->input());
        $album->forceFill($data)->save();

        return $this->formatToJson($album);
    }

    public function patch($album, $payload)
    {
        $this->validateSome($payload);

        return $this->update($album, $payload);
    }

    public function put($album, $payload)
    {
        $this->validateAll($payload);

        return $this->update($album, $payload);
    }

    private function update($album, $payload)
    {
        $actual = $this->convertToActual($payload);
        $album->update($actual);

        return $this->formatToJson($album);
    }

    private function validateAll($payload)
    {
        Validator::make($payload, [
            'title' => 'required|string|min:5|max:255',
            'authorId' => 'required|integer',
            'description' => 'required|min:60|max:1000',
            'preview' => 'present|unique:albums|file|nullable',
        ])->validate();
    }

    private function validateSome($payload)
    {
        Validator::make($payload, [
            'title' => 'nullable|string|min:5|max:255',
            'authorId' => 'nullable|integer',
            'description' => 'nullable|min:60|max:1000',
            'preview' => 'unique:photos|file|nullable',
        ])->validate();
    }

    public function formatToJson($album, $includes = [])
    {
        $item = [
            'id' => $album->id,
            'title' => $album->title,
            'description' => $album->description,
            'author' => [
                'id' => $album->author_id,
            ],
            'preview' => $album->preview,
            'createdAt' => $album->created_at,
            'updatedAt' => $album->updated_at,
            'resourceUrl' => route('albums.show', $album->id),
        ];

        if (in_array('author', $includes)) {
            $item['author'] = array_merge($item['author'], [
                'name' => $album->author->name,
                'email' => $album->author->email,
                'avatar' => $album->author->avatar,
                'resourceUrl' => route('authors.show', $album->author->id),
            ]);
        }

        if (in_array('photos', $includes)) {
            $item['photos'] = $album->photos->map(function ($card) {
                return [
                    'id' => $card->id,
                    'title' => $card->title,
                    'description' => $card->description,
                    'photo' => $card->photo,
                    'commentCount' => $card->comment_count,
                    'likeCount' => $card->like_count,
                    'isLikedByMe' => $card->is_liked_by_me,
                    'resourceUrl' => route('photos.show', $card->id),
                ];
            });
        }

        return $item;
    }

    public function formatFromJson($data)
    {
        return [
            'title' => $data['title'],
            'description' => $data['description'],
            'author_id' => $data['authorId'],
            'preview' => $data['preview'],
        ];
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function uploadPreview(Request $request)
    {
        $album = $this->uploadFile($request, 'preview', 'photos', 'album');

        if ($album) {
            $album = $this->formatToJson($album);
            return response(['preview' => $album['avatar'], 'album' => $album, 'message' => 'File is uploaded successfully!'], 201);
        } else {
            return response(['message' => 'No files for uploading!'], 422);
        }
    }
}
