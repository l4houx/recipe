<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Controller\Controller;
use App\Entity\Traits\HasRoles;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(HasRoles::DEFAULT)]
class CountryController extends Controller
{
    #[Route(path: '/api/country', name: 'country', methods: ["GET"])]
    public function index(): JsonResponse
    {
        $user = $this->getUserOrThrow();

        $country = $user->getCountry();
        $countries = Countries::getNames();

        if ($country) {
            $countries = array_merge([$country => $countries[$country]], $countries);
        }

        return $this->json($countries);
    }
}
