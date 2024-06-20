<?php

namespace App\Services\v1;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthorService extends ResourceService
{
    protected $includes = ['albums','likes', 'socials'];

    protected $queryFields = [
        'name' => 'name',
        'description' => 'description',
        'createdat' => 'created_at',
        'updatedat' => 'updated_at',
        'email' => 'email',
        'id' => 'id'
    ];

    protected $sortFields = [
        'name' => 'name',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
        'id' => 'id'
    ];

    protected $columnMap = [
        'id' => 'id',
        'name' => 'name',
        'avatar' => 'avatar',
        'description' => 'description',
        'cover' => 'cover',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
        'email' => 'email'
    ];

    public function all($input)
    {
        $parms = $this->buildParameters($input);

        $query = User::offset($parms['offset'])->limit($parms['limit']);

        if (!empty($parms['sort'])) {
            $query = $query->orderBy($parms['sort'][0], $parms['sort'][1]);
        }

        if (!empty($parms['include'])) {
            $query->with($parms['include']);
        }

        if (!empty($parms['where'])) {
            $query->where($parms['where']);
        }

        return $query->get()->map(function ($author) use ($parms) {
            return $this->formatToJson($author, $parms['include']);
        });
    }

    public function postUpdate($request, $author)
    {
        $data = $request->input();
        $this->validateSome($data, $author);

        if ($request->file('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('/', 'avatars');
            if (!empty($author['avatar'])) {
                $this->deleteFileIfExists($author['avatar']);
            }
        }
        if ($request->file('cover')) {
            $data['cover'] = $request->file('cover')->store('/', 'photos');
            if (!empty($author['cover'])) {
                $this->deleteFileIfExists($author['cover']);
            }
        }
        $data = $this->convertToActual($data);
        $author->forceFill($data)->save();

        return $this->formatToJson($author);
    }

    public function patch($author, $payload)
    {
        $this->validateSome($payload, $author);

        return $this->update($author, $payload);
    }

    public function put($author, $payload)
    {
        $this->validateAll($payload, $author);

        return $this->update($author, $payload);
    }

    private function update($author, $payload)
    {
        $actual = $this->convertToActual($payload);
        $author->update($actual);

        return $this->formatToJson($author);
    }

    private function validateAll($payload, $author)
    {
        Validator::make($payload, [
            'name' => 'required|string|min:5|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($author->id)],
            'description' => 'required|min:60|max:1000',
        ])->validate();
    }

    private function validateSome($payload, $author)
    {
        Validator::make($payload, [
            'id' => 'nullable|integer',
            'name' => 'nullable|string|min:5|max:255',
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($author->id)],
            'description' => 'nullable|min:60|max:1000',
            'cover' => 'file|nullable',
            'avatar' => 'file|nullable',
        ])->validate();
    }

    public function formatToJson($author, $includes = [])
    {
        $item = [
            'id' => $author->id,
            'name' => $author->name,
            'email' => $author->email,
            'description' => $author->description,
            'avatar' => $author->avatar,
            'cover' => $author->cover,
            'createdAt' => $author->created_at,
            'updatedAt' => $author->updated_at,
            'resourceUrl' => route('authors.show', $author->id),
        ];

        if (in_array('albums', $includes)) {
            $item['albums'] = $author->albums->map(function ($album) {
                return [
                    'id' => $album->id,
                    'title' => $album->title,
                    'description' => $album->description,
                    'preview' => $album->preview,
                    'resourceUrl' => route('albums.show', $album->id),
                ];
            });
        }

        if (in_array('socials', $includes)) {
            $item['socials'] = $author->socials->map(function ($social) {
                return [
                    'social_id' => $social->pivot->social_id,
                    'link' => $social->pivot->link,
                ];
            });
        }

        if (in_array('likes', $includes)) {
            $item['likes'] = $author->likes->map(function ($photo) {
                return $photo->id;
            });
        }
        return $item;
    }
}
