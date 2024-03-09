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

    // Role Team
    public const TEAM = 'ROLE_TEAM';

    // Role Moderator editor the article
    public const MODERATOR = 'ROLE_MODERATOR';

    // Role User
    public const DEFAULT = 'ROLE_USER';

    // Role isVerified
    public const VERIFIED = 'ROLE_VERIFIED';
}
