<?php

namespace App\Services\v1;

use App\Models\Social;
use Illuminate\Support\Facades\Validator;

class SocialService extends ResourceService
{
    protected $columnMap = [
        'name' => 'name',
        'icon' => 'icon',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
        'id' => 'id'
    ];

    public function createNewSocial($payload)
    {
        Validator::make($payload, [
            'name' => 'required|string',
            'icon' => 'required|string',
        ])->validate();

        $actual = $this->convertToActual($payload);
        $social = Social::create($actual);

        return $this->formatToJson($social);
    }

    private function validateAddSocialToProfile($payload)
    {
        Validator::make($payload, [
            'authorId' => 'required|integer',
            'socialId' => 'required|integer',
            'link' => 'required|string|min:10',
        ])->validate();
    }

    private function validateSomeSocialInProfile($payload)
    {
        Validator::make($payload, [
            'authorId' => 'nullable|integer',
            'socialId' => 'nullable|integer',
            'link' => 'nullable|string',
        ])->validate();
    }

    public function formatToJson($social)
    {
        return [
            'id' => $social->id,
            'name' => $social->name,
            'icon' => $social->icon,
            'createdAt' => $social->created_at,
            'updatedAt' => $social->updated_at,
        ];
    }
}
