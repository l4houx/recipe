<?php

namespace App\Entity\Traits;

/**
 * Contains all roles started in the HasRoles.
 */
final class HasRoles
{
    // Role SuperAdmin
    public const ADMINISTRATOR = 'ROLE_ADMINISTRATOR';

    // Role Admin
    public const ADMIN = 'ROLE_ADMIN';

    // Role Author
    public const BOOK = 'ROLE_AUTHOR_ADMIN';

    // Role Moderator editor the article
    public const MODERATOR = 'ROLE_MODERATOR';

    // Role User
    public const DEFAULT = 'ROLE_USER';
}
