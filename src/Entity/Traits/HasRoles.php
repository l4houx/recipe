<?php

namespace App\Entity\Traits;

/**
 * Contains all roles started in the HasRoles.
 */
final class HasRoles
{
    // Role Super Admin
    public const SUPERADMIN = 'ROLE_SUPER_ADMIN';

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

    // Role Restaurant
    public const RESTAURANT = 'ROLE_RESTAURANT';

    // Role PointOfSale
    public const POINTOFSALE = 'ROLE_POINTOFSALE';

    // Role Creator
    public const CREATOR = 'ROLE_CREATOR';

    // Role Scanner
    public const SCANNER = 'ROLE_SCANNER';
}
