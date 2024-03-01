<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AddOneUserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $hasher
    ) {
    }

    public function create(
        // Role
        array $roles,

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
            // create the user and hash its password
            $user = (new User());
            $user
                // isVerified
                ->setIsVerified($isVerified)

                // Identify
                ->setFirstname($firstname)
                ->setLastname($lastname)
                ->setUsername($username)
                ->setEmail($email)
                ->setPassword($this->hasher->hashPassword($user, $password))

                // Team
                ->setIsTeam($isTeam)
            ;
        }

        // Role
        $user->setRoles($roles);

        $this->userRepository->save($user, true);
    }
}
