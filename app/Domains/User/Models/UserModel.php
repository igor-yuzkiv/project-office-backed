<?php

namespace App\Domains\User\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Domains\Attachment\Models\AttachmentModel;
use Database\Factories\UserModelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property string|null $current_avatar_attachment_id
 * @property-read AttachmentModel|null $currentAvatar
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class UserModel extends Authenticatable
{
    /** @use HasFactory<UserModelFactory> */
    use HasApiTokens, HasFactory, HasUlids, Notifiable;

    protected $table = 'users';

    public $incrementing = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function currentAvatar(): BelongsTo
    {
        return $this->belongsTo(AttachmentModel::class, 'current_avatar_attachment_id');
    }

    public function initials(): string
    {
        return collect(explode(' ', $this->name))
            ->take(2)
            ->map(fn (string $part): string => $part === '' ? '' : mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }

    public static function newFactory(): UserModelFactory
    {
        return UserModelFactory::new();
    }
}
