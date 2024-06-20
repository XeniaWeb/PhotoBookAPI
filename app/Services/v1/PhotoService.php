<?php

namespace App\Services\v1;

use App\Models\Photo;
use Illuminate\Support\Facades\Validator;

class PhotoService extends ResourceService
{
    protected $includes = ['author', 'album', 'comments','likes'];

    protected $queryFields = [
        'authorid' => 'author_id',
        'albumid' => 'album_id',
        'createdat' => 'created_at',
        'updatedat' => 'updated_at',
        'id' => 'id'
    ];

    protected $sortFields = [
        'authorid' => 'author_id',
        'albumid' => 'album_id',
        'commentscount' => 'comments_count',
        'likescount' => 'likes_count',
        'createdat' => 'created_at',
        'updatedat' => 'updated_at',
        'id' => 'id'
    ];

    protected $columnMap = [
        'title' => 'title',
        'authorId' => 'author_id',
        'description' => 'description',
        'albumId' => 'album_id',
        'photo' => 'photo',
        'commentsCount' => 'comments_count',
        'likesCount' => 'likes_count',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
        'id' => 'id'
    ];

    public function all($input)
    {
        $parms = $this->buildParameters($input);

        $query = Photo::withCount('likes', 'comments')->offset($parms['offset'])->limit($parms['limit']);

        if (!empty($parms['sort'])) {
            $query = $query->orderBy($parms['sort'][0], $parms['sort'][1]);
        }

        if (!empty($parms['include'])) {
            $query->with($parms['include']);
        }

        if (!empty($parms['where'])) {
            $query->where($parms['where']);
        }

        return $query->get()->map(function ($photo) use ($parms) {
            return $this->formatToJson($photo, $parms['include']);
        });
    }

    public function singlePhoto($model, $input)
    {
        $photo = Photo::withCount('comments', 'likes')->where('id', $model->id)->first();

        $parms = $this->buildParameters($input);

        if (!empty($parms['include'])) {
            $photo->load($parms['include']);
        }

        return $this->formatToJson($photo, $parms['include']);
    }

    public function patch($photo, $payload)
    {
        $this->validateSome($payload);

        return $this->update($photo, $payload);
    }

    public function put($photo, $payload)
    {
        $this->validateAll($payload);

        return $this->update($photo, $payload);
    }

    private function update($photo, $payload)
    {
        $actual = $this->convertToActual($payload);
        $photo->update($actual);

        return $this->formatToJson($photo);
    }

    private function validateAll($payload)
    {
        Validator::make($payload, [
            'title' => 'required|string|min:5|max:255',
            'authorId' => 'required|integer',
            'description' => 'required|min:60|max:1000',
            'photo' => 'present|nullable',
            'albumId' => 'required|integer',
        ])->validate();
    }

    private function validateSome($payload)
    {
        Validator::make($payload, [
            'title' => 'nullable|string|min:5|max:255',
            'authorId' => 'nullable|integer',
            'description' => 'nullable|min:60|max:1000',
            'photo' => 'unique:photos|file|nullable',
            'albumId' => 'nullable|integer',
        ])->validate();
    }

    public function formatToJson($photo, $includes = []): array
    {
        $item = [
            'id' => $photo->id,
            'title' => $photo->title,
            'description' => $photo->description,
            'author' => [
                'id' => $photo->author_id,
            ],
            'album' => [
                'id' => $photo->album_id,
            ],
            'photo' => $photo->photo,
            'commentsCount' => $photo->comments_count,
            'likesCount' => $photo->likes_count,
            'createdAt' => $photo->created_at,
            'updatedAt' => $photo->updated_at,
        ];

        if (in_array('author', $includes)) {
            $item['author'] = array_merge($item['author'], [
                'name' => $photo->author->name,
                'email' => $photo->author->email,
                'avatar' => $photo->author->avatar,
                'resourceUrl' => route('authors.show', $photo->author->id),
            ]);
        }

        if (in_array('album', $includes)) {
            $item['album'] = array_merge($item['album'], [
                'title' => $photo->album->title,
                'description' => $photo->album->description,
                'preview' => $photo->album->preview,
                'createdAt' => $photo->album->created_at,
                'updatedAt' => $photo->album->updated_at,
                'resourceUrl' => route('albums.show', $photo->album->id),
            ]);
        }

        if (in_array('comments', $includes)) {
            $item['comments'] = $photo->comments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'authorId' => $comment->author_id,
                    'photoId' => $comment->photo_id,
                    'commentText' => $comment->comment_text,
                    'resourceUrl' => route('comments.show', $comment->id),
                ];
            });
        }

        if (in_array('likes', $includes)) {
            $item['likes'] = $photo->likes->map(function ($authors) {
                return  $authors->id;
            });
        }

        return $item;
    }

    public function formatFromJson($data): array
    {
        return [
            'title' => $data['title'],
            'description' => $data['description'],
            'author_id' => $data['authorId'],
            'album_id' => $data['albumId'],
            'photo' => $data['photo'],
        ];
    }
}
