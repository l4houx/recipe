<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Country;
use App\Entity\Traits\HasLimit;
use App\Entity\Traits\HasRoles;
use App\Form\CountryFormType;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/%website_dashboard_path%/admin/manage-countries', name: 'dashboard_admin_country_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class CountryController extends AdminBaseController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $keyword = '' == $request->query->get('keyword') ? 'all' : $request->query->get('keyword');

        $rows = $paginator->paginate($this->settingService->getCountries(['keyword' => $keyword, 'isOnline' => 'all', 'sort' => 'c.createdAt', 'order' => 'DESC']), $request->query->getInt('page', 1), HasLimit::COUNTRY_LIMIT, ['wrap-queries' => true]);

        return $this->render('dashboard/admin/country/index.html.twig', compact('rows'));
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    #[Route(path: '/{slug}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function newedit(Request $request, ?string $slug = null): Response
    {
        if (!$slug) {
            $country = new Country();
        } else {
            /** @var Country $country */
            $country = $this->settingService->getCountries(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
            if (!$country) {
                $this->addFlash('danger', $this->translator->trans('The country can not be found'));

                return $this->redirectToRoute('dashboard_admin_country_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        $form = $this->createForm(CountryFormType::class, $country)->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->em->persist($country);
                $this->em->flush();
                if (!$slug) {
                    $this->addFlash('success', $this->translator->trans('Content was created successfully.'));
                } else {
                    $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));
                }

                return $this->redirectToRoute('dashboard_admin_country_index', [], Response::HTTP_SEE_OTHER);
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        }

        return $this->render('dashboard/admin/country/new-edit.html.twig', compact('form', 'country'));
    }

    #[Route(path: '/{slug}/disable', name: 'disable', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/{slug}/delete', name: 'delete', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function delete(string $slug): Response
    {
        /** @var Country $country */
        $country = $this->settingService->getCountries(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$country) {
            $this->addFlash('danger', $this->translator->trans('The country can not be found'));

            return $this->redirectToRoute('dashboard_admin_country_index', [], Response::HTTP_SEE_OTHER);
        }

        if (\count($country->getRecipes()) > 0) {
            $this->addFlash('danger', $this->translator->trans('The country can not be deleted because it is linked with one or more recipes'));

            return $this->redirectToRoute('dashboard_admin_country_index', [], Response::HTTP_SEE_OTHER);
        }

        if (null !== $country->getDeletedAt()) {
            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        } else {
            $this->addFlash('danger', $this->translator->trans('Content was disabled successfully.'));
        }

        $country->setIsOnline(true);

        $this->em->persist($country);
        $this->em->flush();
        $this->em->remove($country);
        $this->em->flush();

        return $this->redirectToRoute('dashboard_admin_country_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/{slug}/restore', name: 'restore', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function restore(string $slug): Response
    {
        /** @var Country $country */
        $country = $this->settingService->getCountries(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$country) {
            $this->addFlash('danger', $this->translator->trans('The country can not be found'));

            return $this->redirectToRoute('dashboard_admin_country_index', [], Response::HTTP_SEE_OTHER);
        }
        $country->setDeletedAt(null);

        $this->em->persist($country);
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('Content was restored successfully.'));

        return $this->redirectToRoute('dashboard_admin_country_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/{slug}/show', name: 'show', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/{slug}/hide', name: 'hide', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function showhide(string $slug): Response
    {
        /** @var Country $country */
        $country = $this->settingService->getCountries(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$country) {
            $this->addFlash('danger', $this->translator->trans('The country can not be found'));

            return $this->redirectToRoute('dashboard_admin_country_index', [], Response::HTTP_SEE_OTHER);
        }

        if (false === $country->getIsOnline()) {
            $country->setIsOnline(true);
            $this->addFlash('success', $this->translator->trans('Content is online'));
        } else {
            $country->setIsOnline(false);
            $this->addFlash('danger', $this->translator->trans('Content is offline'));
        }

        $this->em->persist($country);
        $this->em->flush();

        return $this->redirectToRoute('dashboard_admin_country_index', [], Response::HTTP_SEE_OTHER);
    }
}
