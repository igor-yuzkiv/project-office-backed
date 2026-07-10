<?php

namespace App\Domains\User\Actions\UpdateUserAvatar;

use App\Domains\Attachment\Actions\DeleteAttachment\DeleteAttachmentCommand;
use App\Domains\Attachment\Actions\DeleteAttachment\DeleteAttachmentHandler;
use App\Domains\Attachment\Actions\UploadAttachment\UploadAttachmentCommand;
use App\Domains\Attachment\Actions\UploadAttachment\UploadAttachmentHandler;
use App\Domains\Attachment\Models\AttachmentModel;
use App\Domains\User\Models\UserModel;
use Illuminate\Support\Facades\DB;

class UpdateUserAvatarHandler
{
    public function __construct(
        private readonly UploadAttachmentHandler $uploadHandler,
        private readonly DeleteAttachmentHandler $deleteHandler,
    ) {}

    public function handle(UpdateUserAvatarCommand $command): UserModel
    {
        $user = $command->user;
        $previousAvatarId = $user->current_avatar_attachment_id;

        $avatar = DB::transaction(function () use ($command, $user): AttachmentModel {
            $avatar = $this->uploadHandler->handle(new UploadAttachmentCommand(
                file: $command->file,
                attachable: $user,
                role: 'avatar',
            ));

            $user->current_avatar_attachment_id = $avatar->id;
            $user->save();

            return $avatar;
        });

        if ($previousAvatarId !== null) {
            $previousAvatar = AttachmentModel::find($previousAvatarId);
            if ($previousAvatar !== null) {
                $this->deleteHandler->handle(new DeleteAttachmentCommand($previousAvatar));
            }
        }

        $user->setRelation('currentAvatar', $avatar);

        return $user;
    }
}
