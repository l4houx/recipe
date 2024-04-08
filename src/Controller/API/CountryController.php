<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Entity\Country;
use App\Entity\Traits\HasRoles;
use App\Service\SettingService;
use App\Controller\BaseController;
use Symfony\Component\Intl\Countries;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(HasRoles::DEFAULT)]
class CountryController extends BaseController
{
    public function __construct(
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/api/country', name: 'country', methods: ['GET'])]
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

    #[Route(path: '/get-countries', name: 'get_countries', methods: ['GET'])]
    public function getCountries(Request $request): Response
    {
        $q = '' == $request->query->get('q') ? 'all' : $request->query->get('q');
        $limit = '' == $request->query->get('limit') ? 10 : $request->query->get('limit');

        $countries = $this->settingService->getCountries(['keyword' => $q, 'limit' => $limit])->getQuery()->getResult();

        $results = [];

        /** @var Country $country */
        foreach ($countries as $country) {
            $result = ['id' => $country->getSlug(), 'text' => $country->getName()];
            array_push($results, $result);
        }

        return $this->json($results);
    }

    #[Route(path: '/get-country/{slug}', name: 'get_country', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function getCountry(?string $slug = null): Response
    {
        /** @var Country $country */
        $country = $this->settingService->getCountries(['slug' => $slug])->getQuery()->getOneOrNullResult();

        return $this->json(['slug' => $country->getSlug(), 'text' => $country->getName()]);
    }
}
