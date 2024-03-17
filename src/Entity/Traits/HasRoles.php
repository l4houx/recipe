<?php

namespace App\Entity\Traits;

/**
 * Contains all roles started in the HasRoles.
 */
final class HasRoles
{
    // Role Admin Application
    public const ADMINAPPLICATION = 'ROLE_ADMIN_APPLICATION';

    // Role Application
    public const APPLICATION = 'ROLE_APPLICATION';

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
