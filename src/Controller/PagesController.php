<?php

namespace App\Controller;

use App\Repository\PageRepository;
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
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    #[Route(path: '/pricing', name: 'pricing', methods: ['GET'])]
    public function pricing(PricingRepository $pricingRepository): Response
    {
        $pricings = $pricingRepository->findAllPricing();

        if (!$pricings) {
            $this->addFlash('danger', $this->translator->trans('The plan can not be found'));

            return $this->redirectToRoute('home');
        }

        return $this->render('pages/pricing-detail.html.twig', compact('pricings'));
    }

    #[Route(path: '/terms-condition', name: 'terms_condition', methods: ['GET'])]
    public function termsCondition(): Response
    {
        return $this->render('pages/terms-condition-detail.html.twig');
    }

    #[Route(path: '/privacy-policy', name: 'privacy_policy', methods: ['GET'])]
    public function privacyPolicy(): Response
    {
        return $this->render('pages/privacy-policy-detail.html.twig');
    }

    #[Route(path: '/rgpd', name: 'rgpd', methods: ['GET'])]
    public function rgpd(): Response
    {
        return $this->render('pages/rgpd-detail.html.twig');
    }

    #[Route(path: '/team', name: 'team', methods: ['GET'])]
    public function team(UserRepository $userRepository): Response
    {
        $teams = $userRepository->findTeam(6);

        if (!$teams) {
            $this->addFlash('danger', $this->translator->trans('The team can not be found'));

            return $this->redirectToRoute('home');
        }

        return $this->render('pages/team-detail.html.twig', compact('teams'));
    }

    #[Route('/testimonial', name: 'testimonial', methods: ['GET'])]
    public function testimonial(TestimonialRepository $testimonialRepository): Response
    {
        $testimonials = $testimonialRepository->findLastRecent(12);

        if (!$testimonials) {
            $this->addFlash('danger', $this->translator->trans('The testimonial can not be found'));

            return $this->redirectToRoute('home');
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

    #[Route(path: '/{slug}-{id}', name: 'page', methods: ['GET'], requirements: ['id' => Requirement::POSITIVE_INT, 'slug' => Requirement::ASCII_SLUG])]
    public function page(Request $request, PageRepository $pageRepository, string $slug, int $id): Response
    {
        $page = $pageRepository->find($id);

        if ($page->getSlug() !== $slug) {
            return $this->redirectToRoute('page', [
                'id' => $page->getId(),
                'slug' => $page->getSlug(),
            ], 301);
        }

        if (!$page) {
            $this->addFlash('danger', $this->translator->trans('The page can not be found'));

            return $this->redirectToRoute('home');
        }

        return $this->render('pages/page-detail.html.twig', compact('page'));
    }
}
