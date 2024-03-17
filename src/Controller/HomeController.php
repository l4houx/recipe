<?php

namespace App\Controller;

use App\Entity\Traits\HasRoles;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(/*EntityManagerInterface $em, UserPasswordHasherInterface $hasher*/): Response
    {
        /*
        $user = new User();
        $user
            ->setRoles([HasRoles::APPLICATION])
            ->setEmail('john-doe@example.com')
            ->setUsername('JohnDoe')
            ->setPassword($hasher->hashPassword($user, 'password'))
        ;
        $em->persist($user);
        $em->flush();
        */

        return $this->render('home/index.html.twig');
    }
}
