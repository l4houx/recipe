<?php

namespace App\Controller\Dashboard\Restaurant;

use App\Controller\BaseController;
use App\Entity\Scanner;
use App\Entity\Traits\HasRoles;
use App\Entity\User;
use App\Form\ScannerFormType;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/%website_dashboard_path%/restaurant/my-scanners', name: 'dashboard_restaurant_scanner_')]
#[IsGranted(HasRoles::RESTAURANT)]
class ScannerController extends BaseController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $username = '' == $request->query->get('username') ? 'all' : $request->query->get('username');
        $isVerified = '' == $request->query->get('isVerified') ? 'all' : $request->query->get('isVerified');

        $rows = $paginator->paginate($this->settingService->getUsers(['role' => 'scanner', 'createdbyrestaurantslug' => $this->getUser()->getRestaurant()->getSlug(), 'username' => $username, 'isVerified' => $isVerified])->getQuery(), $request->query->getInt('page', 1), 10);

        return $this->render('dashboard/restaurant/scanner/index.html.twig', compact('rows'));
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    #[Route(path: '/{slug}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function newedit(Request $request, ?string $slug = null): Response
    {
        if (!$slug) {
            $scanner = new Scanner();
            $form = $this->createForm(ScannerFormType::class, $scanner, ['validation_groups' => 'create'])->handleRequest($request);
        } else {
            /** @var User $scanner */
            $scanner = $this->settingService->getUsers(['role' => 'scanner', 'createdbyrestaurantslug' => $this->getUser()->getRestaurant()->getSlug(), 'isVerified' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
            if (!$scanner) {
                $this->addFlash('danger', $this->translator->trans('The scanner can not be found'));

                return $this->redirectToRoute('dashboard_restaurant_scanner_index');
            }
            $scanner = $scanner->getScanner();
            $form = $this->createForm(ScannerFormType::class, $scanner, ['validation_groups' => 'update'])->handleRequest($request);
        }

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $username = $request->request->get('scanner')['username'];
                $password = $request->request->get('scanner')['password']['first'];

                $usermanager = $request->get('fos_user.user_manager');

                if (!$slug) {
                    if ($usermanager->findUserByUsername($username)) {
                        $this->addFlash('danger', $this->translator->trans('The username already exists'));

                        return $this->redirect($request->headers->get('referer'));
                    }

                    $scanner->setRestaurant($this->getUser()->getRestaurant());

                    $this->em->persist($scanner);
                    $this->em->flush();

                    /** @var User $user */
                    $user = $usermanager->createUser();
                    $email = $this->settingService->generateReference(10).'@'.$this->settingService->getSettings('website_root_url');

                    $user->setUsername($username);
                    $user->setEmail($email);
                    $user->setIsVerified(true);
                    $user->setPassword($password);
                    $user->setScanner($scanner);
                    $user->addRole(HasRoles::SCANNER);

                    $scanner->setUser($user);
                    $usermanager->updateUser($user);

                    $this->em->persist($user);
                    $this->em->flush();

                    $this->addFlash('success', $this->translator->trans('Content was created successfully.'));
                } else {
                    if ($usermanager->findUserByUsername($username) && $username !== $scanner->getUser()->getUsername()) {
                        $this->addFlash('danger', $this->translator->trans('The username already exists'));

                        return $this->redirect($request->headers->get('referer'));
                    }

                    $scanner->getUser()->setUsername($username);
                    if (null != $password) {
                        $scanner->getUser()->setPassword($password);
                        $usermanager->updatePassword($scanner->getUser());
                    }

                    $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));
                }

                $this->em->persist($scanner);
                $this->em->flush();

                return $this->redirectToRoute('dashboard_restaurant_scanner_index');
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        }

        return $this->render('dashboard/restaurant/scanner/new-edit.html.twig', compact('scanner', 'form'));
    }

    #[Route(path: '/{slug}/delete-permanently', name: 'delete_permanently', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/{slug}/delete', name: 'delete', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function delete(?string $slug = null): RedirectResponse
    {
        /** @var User $scanner */
        $scanner = $this->settingService->getUsers(['role' => 'scanner', 'createdbyrestaurantslug' => $this->getUser()->getRestaurant()->getSlug(), 'isVerified' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$scanner) {
            $this->addFlash('danger', $this->translator->trans('The scanner can not be found'));

            return $this->redirectToRoute('dashboard_restaurant_scanner_index');
        }

        if (null !== $scanner->getDeletedAt()) {
            $this->addFlash('danger', $this->translator->trans('Content was deleted permenently successfully.'));
        } else {
            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        }

        $this->em->remove($scanner);
        $this->em->flush();

        return $this->redirectToRoute('dashboard_restaurant_scanner_index');
    }

    #[Route(path: '/{slug}/enable', name: 'enable', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/{slug}/disable', name: 'disable', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function enabledisable(Request $request, ?string $slug = null): RedirectResponse
    {
        /** @var User $scanner */
        $scanner = $this->settingService->getUsers(['role' => 'scanner', 'createdbyrestaurantslug' => $this->getUser()->getRestaurant()->getSlug(), 'isverified' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$scanner) {
            $this->addFlash('danger', $this->translator->trans('The scanner can not be found'));

            return $this->redirectToRoute('dashboard_restaurant_scanner_index');
        }

        if ($scanner->isVerified()) {
            $scanner->setIsVerified(false);
            $this->addFlash('success', $this->translator->trans('Content is disabled'));
        } else {
            $scanner->setIsVerified(true);
            $this->addFlash('danger', $this->translator->trans('Content is enabled'));
        }

        $this->em->persist($scanner);
        $this->em->flush();

        return $this->redirectToRoute('dashboard_restaurant_scanner_index');
    }
}
