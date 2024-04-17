<?php

namespace App\Controller;

use App\Entity\Setting\HomepageHeroSetting;
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
    public function home(EntityManagerInterface $em): Response
    {
        $herosettings = $em->getRepository(HomepageHeroSetting::class)->find(1);

        return $this->render('home/home.html.twig', compact('herosettings'));
    }
}
