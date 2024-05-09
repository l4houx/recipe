<?php

namespace App\Controller\Dashboard\Shared;

use App\Controller\BaseController;
use App\Entity\Testimonial;
use App\Entity\Traits\HasLimit;
use App\Entity\Traits\HasRoles;
use App\Form\TestimonialFormType;
use App\Repository\TestimonialRepository;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/%website_dashboard_path%')]
#[IsGranted(HasRoles::DEFAULT)]
class TestimonialController extends BaseController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
        private readonly SluggerInterface $slugger,
        private readonly Security $security,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/creator/my-testimonials', name: 'dashboard_creator_testimonial_index', methods: ['GET'])]
    #[Route(path: '/admin/manage-testimonials', name: 'dashboard_admin_testimonial_index', methods: ['GET'])]
    public function index(Request $request, AuthorizationCheckerInterface $authChecker, PaginatorInterface $paginator): Response
    {
        $keyword = '' == $request->query->get('keyword') ? 'all' : $request->query->get('keyword');
        $isOnline = '' == $request->query->get('isOnline') ? 'all' : $request->query->get('isOnline');
        $rating = '' == $request->query->get('rating') ? 'all' : $request->query->get('rating');
        $slug = '' == $request->query->get('slug') ? 'all' : $request->query->get('slug');

        $user = 'all';
        if ($authChecker->isGranted(HasRoles::CREATOR)) {
            $user = $this->getUser()->getSlug();
        }

        $rows = $paginator->paginate(
            $this->settingService->getTestimonials(['user' => $user, 'keyword' => $keyword, 'slug' => $slug, 'isOnline' => $isOnline, 'rating' => $rating])->getQuery(),
            $request->query->getInt('page', 1),
            HasLimit::TESTIMONIAL_LIMIT,
            ['wrap-queries' => true]
        );

        return $this->render('dashboard/shared/testimonials/index.html.twig', compact('rows', 'user'));
    }

    /*
    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(Request $request, TestimonialRepository $testimonialRepository, PaginatorInterface $paginator): Response
    {
        $user = $this->getUserOrThrow();
        $rows = $testimonialRepository->findLastByUser($user, 1);

        return $this->render('dashboard/shared/testimonials/index.html.twig', compact('rows', 'user'));
    }
    */

    #[Route(path: '/creator/my-testimonials/new', name: 'dashboard_creator_testimonial_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UrlGeneratorInterface $url): Response
    {
        $user = $this->getUserOrThrow();
        $testimonial = new Testimonial();

        $form = $this->createForm(TestimonialFormType::class, $testimonial)->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $testimonial->setAuthor($user);
                $testimonial->setIsOnline(false);
                $testimonial->setSlug($this->slugger->slug($testimonial->getHeadline())->lower());

                $this->em->persist($testimonial);
                $this->em->flush();

                $this->addFlash(
                    'success',
                    sprintf(
                        $this->translator->trans('Content %s was created successfully.'),
                        $testimonial->getAuthor()->getFullName()
                    )
                );

                if ($this->security->isGranted(HasRoles::ADMIN)) {
                    return $this->redirectToRoute('dashboard_admin_testimonial_index', [], Response::HTTP_SEE_OTHER);
                } else {
                    return $this->redirectToRoute('dashboard_creator_testimonial_index', [], Response::HTTP_SEE_OTHER);
                }
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        }

        return $this->render('dashboard/shared/testimonials/new.html.twig', compact('form', 'testimonial', 'user'));
    }

    #[Route(path: '/admin/manage-testimonials/{slug}/show', name: 'dashboard_admin_testimonial_show', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/admin/manage-testimonials/{slug}/hide', name: 'dashboard_admin_testimonial_hide', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function showhide(string $slug): Response
    {
        /** @var Testimonial $testimonial */
        $testimonial = $this->settingService->getTestimonials(['slug' => $slug, 'isOnline' => 'all'])->getQuery()->getOneOrNullResult();
        if (!$testimonial) {
            $this->addFlash('danger', $this->translator->trans('The testimonial can not be found'));

            return $this->redirectToRoute('dashboard_admin_testimonial_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($testimonial->getIsOnline()) {
            $testimonial->setIsOnline(false);
            $this->addFlash('success', $this->translator->trans('Content is online'));
        } else {
            $testimonial->setIsOnline(true);
            $this->addFlash('danger', $this->translator->trans('Content is offline'));
        }

        $this->em->persist($testimonial);
        $this->em->flush();

        return $this->redirectToRoute('dashboard_admin_testimonial_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/admin/manage-testimonials/{slug}/delete-permanently', name: 'dashboard_admin_testimonial_delete_permanently', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/admin/manage-testimonials/{slug}/delete', name: 'dashboard_admin_testimonial_delete', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function delete(string $slug): Response
    {
        /** @var Testimonial $testimonial */
        $testimonial = $this->settingService->getTestimonials(['slug' => $slug, 'isOnline' => 'all'])->getQuery()->getOneOrNullResult();
        if (!$testimonial) {
            $this->addFlash('danger', $this->translator->trans('The testimonial can not be found'));

            return $this->redirectToRoute('dashboard_admin_testimonial_index', [], Response::HTTP_SEE_OTHER);
        }

        if (null !== $testimonial->getDeletedAt()) {
            $this->addFlash('danger', $this->translator->trans('Content was deleted permanently successfully.'));
        } else {
            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        }

        $testimonial->setIsOnline(false);

        $this->em->persist($testimonial);
        $this->em->flush();
        $this->em->remove($testimonial);
        $this->em->flush();

        return $this->redirectToRoute('dashboard_admin_testimonial_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/admin/manage-testimonials/{slug}/restore', name: 'dashboard_admin_testimonial_restore', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function restore(string $slug): Response
    {
        /** @var Testimonial $testimonial */
        $testimonial = $this->settingService->getTestimonials(['slug' => $slug, 'isVisible' => 'all'])->getQuery()->getOneOrNullResult();
        if (!$testimonial) {
            $this->addFlash('danger', $this->translator->trans('The testimonial can not be found'));

            return $this->redirectToRoute('dashboard_admin_testimonial_index', [], Response::HTTP_SEE_OTHER);
        }

        $testimonial->setDeletedAt(null);

        $this->em->persist($testimonial);
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('Content was restored successfully.'));

        return $this->redirectToRoute('dashboard_admin_testimonial_index', [], Response::HTTP_SEE_OTHER);
    }
}
