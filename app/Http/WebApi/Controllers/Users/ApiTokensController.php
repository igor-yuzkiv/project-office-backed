<?php

namespace App\Http\WebApi\Controllers\Users;

use App\Domains\User\Actions\CreateApiToken\CreateApiTokenCommand;
use App\Domains\User\Actions\CreateApiToken\CreateApiTokenHandler;
use App\Domains\User\Actions\DeleteApiToken\DeleteApiTokenCommand;
use App\Domains\User\Actions\DeleteApiToken\DeleteApiTokenHandler;
use App\Domains\User\Models\UserModel;
use App\Http\WebApi\Requests\Users\StoreApiTokenRequest;
use App\Http\WebApi\Resources\Users\ApiTokenResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ApiTokensController
{
    public function __construct(
        private readonly CreateApiTokenHandler $createHandler,
        private readonly DeleteApiTokenHandler $deleteHandler,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $tokens = $this->authenticatedUser()->tokens()->orderByDesc('created_at')->get();

        return ApiTokenResource::collection($tokens);
    }

    public function store(StoreApiTokenRequest $request): JsonResponse
    {
        $newToken = $this->createHandler->handle(new CreateApiTokenCommand(
            user: $this->authenticatedUser(),
            name: $request->validated('name'),
            expiresAt: Carbon::parse($request->validated('expires_at')),
        ));

        return response()->json([
            'token'            => new ApiTokenResource($newToken->accessToken),
            'plain_text_token' => $newToken->plainTextToken,
        ], 201);
    }

    public function destroy(int $token): JsonResponse
    {
        $this->deleteHandler->handle(new DeleteApiTokenCommand(
            user: $this->authenticatedUser(),
            tokenId: $token,
        ));

        return response()->json(['message' => 'Token revoked.']);
    }

    private function authenticatedUser(): UserModel
    {
        $user = Auth::user();

        abort_unless($user instanceof UserModel, 401);

        return $user;
    }
}
