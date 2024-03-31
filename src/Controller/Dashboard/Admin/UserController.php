<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Traits\HasRoles;
use App\Entity\User;
use App\Form\UserFormType;
use App\Repository\UserRepository;
use App\Security\Exception\PremiumNotBanException;
use App\Service\AccountSuspendedService;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/%website_dashboard_path%/main-panel/manage-users', name: 'dashboard_admin_user_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class UserController extends AdminBaseController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly SettingService $settingService,
        private readonly UserRepository $userRepository,
    ) {
    }

    #[Route(path: '/', name: 'index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        // $page = $request->query->getInt('page', 1);
        // $rows = $this->userRepository->findForPagination($page);

        $role = '' == $request->query->get('role') ? 'all' : $request->query->get('role');
        $createdbyrestaurantslug = '' == $request->query->get('createdbyrestaurantslug') ? 'all' : $request->query->get('createdbyrestaurantslug');
        $restaurantname = '' == $request->query->get('restaurantname') ? 'all' : $request->query->get('restaurantname');
        $username = '' == $request->query->get('username') ? 'all' : $request->query->get('username');
        $email = '' == $request->query->get('email') ? 'all' : $request->query->get('email');
        $firstname = '' == $request->query->get('firstname') ? 'all' : $request->query->get('firstname');
        $lastname = '' == $request->query->get('lastname') ? 'all' : $request->query->get('lastname');
        $isVerified = '' == $request->query->get('isVerified') ? 'all' : $request->query->get('isVerified');
        $isSuspended = '' == $request->query->get('isSuspended') ? 'all' : $request->query->get('isSuspended');
        $countryslug = '' == $request->query->get('countryslug') ? 'all' : $request->query->get('countryslug');
        $rows = $paginator->paginate($this->settingService->getUsers(['role' => $role, 'createdbyrestaurantslug' => $createdbyrestaurantslug, 'restaurantname' => $restaurantname, 'username' => $username, 'email' => $email, 'firstname' => $firstname, 'lastname' => $lastname, 'isVerified' => $isVerified, 'isSuspended' => $isSuspended, 'countryslug' => $countryslug]), $request->query->getInt('page', 1), 10, ['wrap-queries' => true]);

        return $this->render('dashboard/admin/user/index.html.twig', compact('rows'));
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserFormType::class, $user)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $form->has('plainPassword') ? $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                ) : ''
            );
            $user->setLastLoginIp($request->getClientIp());

            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('Content was created successfully.'));

            return $this->redirectToRoute('dashboard_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/user/new.html.twig', compact('user', 'form'));
    }

    #[Route(path: '/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => Requirement::DIGITS])]
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('user_deletion_'.$user->getId(), $request->request->get('_token'))) {
            $this->em->remove($user);
            $this->em->flush();

            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        }

        return $this->redirectToRoute('dashboard_admin_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/{id}/suspended', name: 'suspended', methods: ['POST', 'DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function suspended(Request $request, User $user, AccountSuspendedService $accountSuspendedService): Response
    {
        $username = $user->getUsername();

        try {
            $accountSuspendedService->suspended($user);
            $this->em->flush();
        } catch (PremiumNotBanException) {
            $this->addFlash('danger', $this->translator->trans('Unable to suspended a premium user.'));

            return $this->redirectToRoute('dashboard_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->json([]);
        }

        $this->addFlash('success', $this->translator->trans("User $username has been suspended"));

        return $this->redirectToRoute('dashboard_admin_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/{id}/verified', name: 'verified', methods: ['POST'], requirements: ['id' => Requirement::DIGITS])]
    public function verified(User $user): RedirectResponse
    {
        $user->setIsVerified(true);
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('The account has been verified successfully.'));

        return $this->redirectToRoute('dashboard_admin_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/{slug}/more-information', name: 'user_information', methods: ['GET'], requirements: ['id' => Requirement::ASCII_SLUG])]
    public function details(Request $request, string $slug): Response
    {
        /** @var User $user */
        $user = $this->settingService->getUsers(['slug' => $slug, 'isVerified' => 'all'])->getQuery()->getOneOrNullResult();
        if (!$user) {
            $this->addFlash('danger', $this->translator->trans('The user can not be found'));

            return $this->redirectToRoute('dashboard_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/user/information.html.twig', compact('user'));
    }

    #[Route(path: '/search', name: 'autocomplete')]
    public function search(Request $request): JsonResponse
    {
        $this->userRepository = $this->em->getRepository(User::class);

        $q = strtolower($request->query->get('q') ?: '');
        if ('moi' === $q) {
            return new JsonResponse([
                [
                    'id' => $this->getUser()->getId(),
                    'username' => $this->getUser()->getUsername(),
                ],
            ]);
        }

        $users = $this->userRepository
            ->createQueryBuilder('u')
            ->select('u.id', 'u.username')
            ->where('LOWER(u.username) LIKE :username')
            ->setParameter('username', "%$q%")
            ->setMaxResults(25)
            ->getQuery()
            ->getResult()
        ;

        return new JsonResponse($users);
    }
}
