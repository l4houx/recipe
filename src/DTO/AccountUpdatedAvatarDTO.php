<?php

namespace App\DTO;

use App\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class AccountUpdatedAvatarDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[
            Assert\Image(
                mimeTypes: ['image/jpeg', 'image/jpg', 'image/gif', 'image/png'],
                minWidth: 110, maxHeight: 1400, maxWidth: 1400, minHeight: 110
            )
        ]
        public UploadedFile $file,
        public User $user
    ) {
    }
}
