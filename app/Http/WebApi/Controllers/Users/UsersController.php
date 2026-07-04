<?php

namespace App\Http\WebApi\Controllers\Users;

use App\Domains\User\Models\UserModel;
use App\Http\WebApi\Resources\Users\UserOverviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UsersController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->query('search');

        $query = UserModel::query()->orderBy('name', 'asc');

        if (is_string($search) && $search !== '') {
            $normalized = '%'.strtolower(trim($search)).'%';
            $query->where(function ($q) use ($normalized) {
                $q->whereRaw('LOWER(name) LIKE ?', [$normalized])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$normalized]);
            });
        }

        return UserOverviewResource::collection($query->limit(50)->get());
    }
}
