<?php

namespace App\Controller;

use App\Service\SettingService;
use App\Repository\FaqRepository;
use App\Repository\UserRepository;
use App\Repository\PricingRepository;
use App\Repository\TestimonialRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: '/page')]
class PagesController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/pricing', name: 'pricing', methods: ['GET'])]
    public function pricing(PricingRepository $pricingRepository, FaqRepository $faqRepository): Response
    {
        $pricings = $pricingRepository->findAllPricing();

        if (!$pricings) {
            $this->addFlash('danger', $this->translator->trans('The plan can not be found'));

            return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
        }

        $faqs = $faqRepository->findAlls();

        if (!$faqs) {
            $this->addFlash('danger', $this->translator->trans('The faq can not be found'));

            return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pages/pricing-detail.html.twig', compact('pricings', 'faqs'));
    }

    #[Route(path: '/team', name: 'team', methods: ['GET'])]
    public function team(UserRepository $userRepository): Response
    {
        $rows = $userRepository->findTeam(6);

        if (!$rows) {
            $this->addFlash('danger', $this->translator->trans('The team can not be found'));

            return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pages/team-detail.html.twig', compact('rows'));
    }

    #[Route('/testimonial', name: 'testimonial', methods: ['GET'])]
    public function testimonial(TestimonialRepository $testimonialRepository): Response
    {
        $testimonials = $testimonialRepository->findLastRecent(12);

        if (!$testimonials) {
            $this->addFlash('danger', $this->translator->trans('The testimonial can not be found'));

            return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pages/testimonial-detail.html.twig', compact('testimonials'));
    }

    #[Route(path: '/access-denied', name: 'access_denied', methods: ['GET'])]
    public function accessDenied(): Response
    {
        return $this->render('pages/access-denied.html.twig');
    }

    #[Route(path: '', name: 'pages', methods: ['GET'])]
    public function pages(): Response
    {
        return $this->render('pages/pages.html.twig');
    }

    #[Route(path: '/{slug}', name: 'page', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function page(Request $request, string $slug): Response
    {
        $page = $this->settingService->getPages(['slug' => $slug])->getQuery()->getOneOrNullResult();

        if (!$page) {
            $this->addFlash('danger', $this->translator->trans('The page can not be found'));

            return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pages/page-detail.html.twig', compact('page'));
    }
}
