<?php

declare(strict_types=1);

namespace App\Twig\Components\Form;

use App\Entity\Comment;
use App\Form\CommentFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('comment-form', template: 'components/form/comment-form.html.twig')]
final class CommentComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp(fieldName: 'data')]
    public ?Comment $comment = null;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(CommentFormType::class, $this->comment);
    }
}
