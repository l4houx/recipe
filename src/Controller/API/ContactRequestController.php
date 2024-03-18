<?php

namespace App\Controller\API;

use App\DTO\ContactFormDTO;
use App\Controller\Controller;
use App\Service\ContactRequestService;
use ApiPlatform\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Security\Exception\ContactRequestException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ContactRequestController extends Controller
{
    #[Route(path: '/contact', name: 'api_contact', methods: ['POST'])]
    public function contact(
        Request $request,
        ContactRequestService $contactRequestService,
        TranslatorInterface $translator,
        DenormalizerInterface $denormalizerInterface,
        ValidatorInterface $validatorInterface
    ): JsonResponse {
        $data = json_decode((string) $request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        /** @var ContactFormDTO $contactFormDTO */
        $contactFormDTO = $denormalizerInterface->denormalize($data, ContactFormDTO::class);

        $validatorInterface->validate($contactFormDTO);

        try {
            $contactRequestService->send($request, $contactFormDTO);
        } catch (ContactRequestException) {
            return new JsonResponse([
                'title' => $translator->trans('You have made too many contact requests in a row.'),
            ], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}