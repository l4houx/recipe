<?php

namespace App\Controller;

use App\Entity\Venue;
use App\Form\VenueQuoteFormType;
use App\Service\SendMailService;
use App\Service\SettingService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;

class VenueController extends BaseController
{
    public function __construct(
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/venues', name: 'venues', methods: ['GET'])]
    public function venues(Request $request, PaginatorInterface $paginator): Response
    {
        $keyword = '' == $request->query->get('keyword') ? 'all' : $request->query->get('keyword');
        $country = '' == $request->query->get('country') ? 'all' : $request->query->get('country');
        $venuetypes = '' == $request->query->get('venuetypes') ? 'all' : $request->query->get('venuetypes');
        $minseatedguests = '' == $request->query->get('minseatedguests') ? 'all' : $request->query->get('minseatedguests');
        $maxseatedguests = '' == $request->query->get('maxseatedguests') ? 'all' : $request->query->get('maxseatedguests');
        $minstandingguests = '' == $request->query->get('minstandingguests') ? 'all' : $request->query->get('minstandingguests');
        $maxstandingguests = '' == $request->query->get('maxstandingguests') ? 'all' : $request->query->get('maxstandingguests');

        $rows = $paginator->paginate($this->settingService->getVenues(['directory' => true, 'keyword' => $keyword, 'country' => $country, 'venuetypes' => $venuetypes, 'minseatedguests' => $minseatedguests, 'maxseatedguests' => $maxseatedguests, 'minstandingguests' => $minstandingguests, 'maxstandingguests' => $maxstandingguests]), $request->query->getInt('page', 1), 4);

        return $this->render('venue/venues.html.twig', compact('rows'));
    }

    #[Route(path: '/venue/{slug}', name: 'venue', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function venue(Request $request, string $slug, TranslatorInterface $translator, SendMailService $mail): Response
    {
        /** @var Venue $venue */
        $venue = $this->settingService->getVenues(['directory' => true, 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$venue) {
            $this->addFlash('danger', $translator->trans('The venue can not be found'));

            return $this->redirectToRoute('venues', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(VenueQuoteFormType::class)->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $quoterequest = $form->getData();

                $mail->send(
                    $this->getParameter('website_no_reply_email'),
                    $venue->getContactemail(),
                    $translator->trans('New quote request'),
                    'venue',
                    compact('quoterequest', 'venue')
                );

                $this->addFlash('success', $translator->trans('Your quote request has been successfully sent'));

                $this->redirectToRoute('venue', ['slug' => $venue->getSlug()]);
            } else {
                $this->addFlash('danger', $translator->trans('The form contains invalid data'));
            }
        }

        return $this->render('venue/venue.html.twig', compact('form', 'venue'));
    }
}
