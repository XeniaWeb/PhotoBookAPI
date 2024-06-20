<?php

namespace App\Services\v1;

use Illuminate\Http\Request;

class UploadFilesService extends ResourceService
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function uploadAvatar(Request $request)
    {
        $author = $this->uploadFile($request, 'avatar', 'avatars', 'author');

        if ($author) {
            $author = $this->formatToJson($author);
            return response(['avatar' => $author['avatar'], 'author' => $author, 'message' => 'File is uploaded successfully!'], 201);
        } else {
            return response(['message' => 'No files for uploading!'], 422);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function uploadCover(Request $request)
    {
        $author = $this->uploadFile($request, 'cover', 'photos', 'author');

        if ($author) {
            $author = $this->formatToJson($author);
            return response(['cover' => $author['cover'], 'author' => $author, 'message' => 'File is uploaded successfully!'], 201);
        } else {
            return response(['message' => 'No files for uploading!'], 422);
        }
    }

    /**
     * @param $author
     * @return array
     */
    public function formatToJson($author)
    {
        return [
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
    }
}
