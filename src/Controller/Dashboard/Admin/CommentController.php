<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Traits\HasLimit;
use App\Entity\Traits\HasRoles;
use App\Service\SettingService;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/%website_dashboard_path%/admin/manage-comments', name: 'dashboard_admin_comment_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class CommentController extends AdminBaseController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly SettingService $settingService,
        private readonly CommentRepository $commentRepository
    ) {
    }

    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(Request $request, AuthorizationCheckerInterface $authChecker, PaginatorInterface $paginator): Response
    {
        $keyword = '' == $request->query->get('keyword') ? 'all' : $request->query->get('keyword');
        $post = '' == $request->query->get('post') ? 'all' : $request->query->get('post');
        $venue = '' == $request->query->get('venue') ? 'all' : $request->query->get('venue');
        $isApproved = '' == $request->query->get('isApproved') ? 'all' : $request->query->get('isApproved');
        $isRGPD = '' == $request->query->get('isRGPD') ? 'all' : $request->query->get('isRGPD');
        $ip = '' == $request->query->get('ip') ? 'all' : $request->query->get('ip');
        $id = '' == $request->query->get('id') ? 'all' : $request->query->get('id');

        $user = 'all';
        if ($authChecker->isGranted(HasRoles::CREATOR)) {
            $user = $this->getUser()->getSlug();
        }

        $rows = $paginator->paginate(
            $this->settingService->getComments(['keyword' => $keyword, 'post' => $post, 'venue' => $venue, 'isApproved' => $isApproved, 'isRGPD' => $isRGPD, 'ip' => $ip, 'id' => $id, 'user' => $user])->getQuery(), 
            $request->query->getInt('page', 1), 
            HasLimit::COMMENT_LIMIT, 
            ['wrap-queries' => true]
        );

        /*
        $keyword = '' == $request->query->get('keyword') ? 'all' : $request->query->get('keyword');

        $rows = $paginator->paginate($this->settingService->getComments(['keyword' => $keyword, 'isApproved' => 'all']), $request->query->getInt('page', 1), 10, ['wrap-queries' => true]);
        */

        return $this->render('dashboard/admin/comment/index.html.twig', compact('rows'));
    }
}
