<?php

namespace App\Controller\Dashboard\Shared;

use App\Controller\BaseController;
use App\Entity\Testimonial;
use App\Entity\Traits\HasRoles;
use App\Form\TestimonialFormType;
use App\Repository\TestimonialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/%website_dashboard_path%/account/my-testimonials', name: 'dashboard_account_testimonial_')]
#[IsGranted(HasRoles::DEFAULT)]
class AccountTestimonialController extends BaseController
{
    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(Request $request, TestimonialRepository $testimonialRepository, PaginatorInterface $paginator): Response
    {
        $page = $request->query->getInt('page', 1);
        $testimonials = $testimonialRepository->findForPagination($page);

        return $this->render('dashboard/shared/testimonials/index.html.twig', compact('testimonials'));
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $testimonial = new Testimonial();

        $form = $this->createForm(TestimonialFormType::class, $testimonial)->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $testimonial->setAuthor($this->getUser());
                $testimonial->setIsOnline(false);

                $em->persist($testimonial);
                $em->flush();

                $this->addFlash(
                    'success',
                    sprintf(
                        $translator->trans('Content %s was created successfully.'),
                        $testimonial->getAuthor()->getFullName()
                    )
                );

                return $this->redirectToRoute('dashboard_account_testimonial_index', [], Response::HTTP_SEE_OTHER);
            } else {
                $this->addFlash('danger', $translator->trans('The form contains invalid data'));
            }
        }

        return $this->render('dashboard/shared/testimonials/new.html.twig', compact('form', 'testimonial'));
    }
}
