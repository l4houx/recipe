<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Entity\Traits\HasRoles;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateTeamUserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $hasher
    ) {
    }

    public function create(
        // Role
        bool $isAdmin,

        // isVerified
        bool $isVerified,

        // Identify
        string $username,
        string $firstname,
        string $lastname,
        string $email,
        string $password,

        // Team
        bool $isTeam
    ): void {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        // create the user and hash its password
        if (!$user) {
            $user = new User();
            // isVerified
            $user->setIsVerified($isVerified);

            // Identify
            $user->setUsername($username);
            $user->setFirstname($firstname);
            $user->setLastname($lastname);
            $user->setEmail($email);

            $hashedPassword = $this->hasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);

            // Team
            $user->setIsTeam($isTeam);
        }

        // Role
        $user->setRoles([$isAdmin ? HasRoles::ADMIN : HasRoles::MODERATOR]);

        $this->userRepository->save($user, true);
    }
}
