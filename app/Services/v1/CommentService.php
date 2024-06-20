<?php

namespace App\Services\v1;

use App\Models\Comment;
use Illuminate\Support\Facades\Validator;

class CommentService extends ResourceService
{
    protected $includes = ['author', 'photo'];

    protected $queryFields = [
        'authorid' => 'author_id',
        'photoid' => 'photo_id',
        'commenttext' => 'comment_text',
        'createdat' => 'created_at',
        'updatedat' => 'updated_at',
        'id' => 'id'
    ];

    protected $sortFields = [
        'authorid' => 'author_id',
        'photoid' => 'photo_id',
        'createdat' => 'created_at',
        'updatedat' => 'updated_at',
        'id' => 'id'
    ];

    protected $columnMap = [
        'authorId' => 'author_id',
        'photoId' => 'photo_id',
        'commentText' => 'comment_text',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
        'id' => 'id'
    ];

    public function all($input)
    {
        $parms = $this->buildParameters($input);

        $query = Comment::offset($parms['offset'])->limit($parms['limit']);

        if (!empty($parms['sort'])) {
            $query = $query->orderBy($parms['sort'][0], $parms['sort'][1]);
        }

        if (!empty($parms['include'])) {
            $query->with($parms['include']);
        }

        if (!empty($parms['where'])) {
            $query->where($parms['where']);
        }

        return $query->get()->map(function ($comment) use ($parms) {
            return $this->formatToJson($comment, $parms['include']);
        });
    }

    public function createNewComment($payload)
    {
        $this->validateAll($payload);

        $actual = $this->convertToActual($payload);
        $comment = Comment::create($actual);

        return $this->formatToJson($comment);
    }

    public function patch($comment, $payload)
    {
        $this->validateSome($payload);

        return $this->update($comment, $payload);
    }

    public function put($comment, $payload)
    {
        $this->validateAll($payload);

        return $this->update($comment, $payload);
    }

    private function update($comment, $payload)
    {
        $actual = $this->convertToActual($payload);
        $comment->update($actual);

        return $this->formatToJson($comment);
    }

    private function validateAll($payload)
    {
        Validator::make($payload, [
            'authorId' => 'required|integer',
            'photoId' => 'required|integer',
            'commentText' => 'required|string|min:10',
        ])->validate();
    }

    private function validateSome($payload)
    {
        Validator::make($payload, [
            'authorId' => 'nullable|integer',
            'photoId' => 'nullable|integer',
            'commentText' => 'nullable|string|min:10',
        ])->validate();
    }

    public function formatToJson($comment, $includes = [])
    {
        $item = [
            'id' => $comment->id,
            'commentText' => $comment->comment_text,
            'author' => [
                'id' => $comment->author_id,
            ],
            'photo' => [
                'id' => $comment->photo_id,
            ],
            'createdAt' => $comment->created_at,
            'updatedAt' => $comment->updated_at,
        ];

        if (in_array('author', $includes)) {
            $item['author'] = array_merge($item['author'], [
                'name' => $comment->author->name,
                'email' => $comment->author->email,
                'avatar' => $comment->author->avatar,
                'resourceUrl' => route('authors.show', $comment->author->id),
            ]);
        }

        if (in_array('photo', $includes)) {
            $item['photo'] = array_merge($item['photo'], [
                'title' => $comment->photo->title,
                'description' => $comment->photo->description,
                'preview' => $comment->photo->preview,
                'createdAt' => $comment->photo->created_at,
                'updatedAt' => $comment->photo->updated_at,
                'resourceUrl' => route('photos.show', $comment->photo->id),
            ]);
        }

        return $item;
    }
}
