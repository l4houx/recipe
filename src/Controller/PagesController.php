<?php

namespace App\Controller;

use App\Entity\Page;
use App\Repository\FaqRepository;
use App\Repository\UserRepository;
use App\Repository\PricingRepository;
use App\Repository\TestimonialRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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
            $this->addFlash('danger', $this->translator->trans('The pricing can not be found'));

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

    #[Route('/faq-rules', name: 'faq', methods: ['GET'])]
    public function faq(FaqRepository $faqRepository): Response
    {
        $faqs = $faqRepository->findAll();

        if (!$faqs) {
            $this->addFlash('danger', $this->translator->trans('The faq can not be found'));

            return $this->redirectToRoute('home');
        }

        return $this->render('pages/faq-detail.html.twig', compact('faqs'));
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

    #[Route(path: '/{slug}', name: 'page', methods: ['GET'])]
    public function pages(Page $page): Response
    {
        if (!$page) {
            $this->addFlash('danger', $this->translator->trans('The page can not be found'));

            return $this->redirectToRoute('home');
        }

        return $this->render('pages/page-detail.html.twig', compact('page'));
    }
}
