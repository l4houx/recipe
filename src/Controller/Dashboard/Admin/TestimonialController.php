<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Testimonial;
use App\Entity\Traits\HasLimit;
use App\Entity\Traits\HasRoles;
use App\Service\SettingService;
use App\Form\TestimonialFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TestimonialRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route('/%website_dashboard_path%/main-panel/manage-testimonials', name: 'dashboard_admin_testimonial_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class TestimonialController extends AdminBaseController
{
    public function __construct(
        private readonly TestimonialRepository $testimonialRepository,
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '', name: 'index', methods: ['GET'])]
    /*
    public function index(Request $request, AuthorizationCheckerInterface $authChecker, PaginatorInterface $paginator): Response
    {
        $keyword = '' == $request->query->get('keyword') ? 'all' : $request->query->get('keyword');
        $isOnline = '' == $request->query->get('isOnline') ? 'all' : $request->query->get('isOnline');
        $rating = '' == $request->query->get('rating') ? 'all' : $request->query->get('rating');
        $id = '' == $request->query->get('id') ? 'all' : $request->query->get('id');

        $user = 'all';
        if ($authChecker->isGranted(HasRoles::DEFAULT)) {
            $user = $this->getUser()->getId();
        }

        $testimonials = $paginator->paginate(
            $this->settingService->getTestimonials(['user' => $user, 'keyword' => $keyword, 'id' => $id, 'isOnline' => $isOnline, 'rating' => $rating])->getQuery(),
            $request->query->getInt('page', 1),
            HasLimit::TESTIMONIAL_LIMIT,
            ['wrap-queries' => true]
        );

        return $this->render('dashboard/admin/testimonials/index.html.twig', compact('testimonials'));
    }
    */

    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $page = $request->query->getInt('page', 1);
        $testimonials = $this->testimonialRepository->findForPagination($page);

        return $this->render('dashboard/admin/testimonials/index.html.twig', compact('testimonials'));
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    #[Route(path: '/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => Requirement::DIGITS])]
    public function newedit(Request $request, ?int $id = null)
    {
        if (!$id) {
            $testimonial = new Testimonial();
        } else {
            /** @var Testimonial $testimonial */
            $testimonial = $this->settingService->getTestimonials(['isOnline' => 'all', 'id' => $id])->getQuery()->getOneOrNullResult();
            if (!$testimonial) {
                $this->addFlash('danger', $this->translator->trans('The testimonial can not be found'));

                return $this->redirectToRoute('dashboard_admin_testimonial_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        $form = $this->createForm(TestimonialFormType::class, $testimonial)->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $testimonial->setAuthor($this->getUser());
                $testimonial->setIsOnline(true);

                $this->em->persist($testimonial);
                $this->em->flush();

                $this->addFlash(
                    'success',
                    sprintf(
                        $this->translator->trans('Content %s was created successfully.'),
                        $testimonial->getAuthor()->getFullName()
                    )
                );

                return $this->redirectToRoute('dashboard_admin_testimonial_index', [], Response::HTTP_SEE_OTHER);
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        }

        return $this->render('dashboard/admin/testimonials/new-edit.html.twig', compact('form', 'testimonial'));
    }
}
