<?php

declare(strict_types=1);

namespace App\Twig\Components\Form;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('change_password', template: 'components/form/change_password.html.twig')]
class ChangePasswordComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp(fieldName: 'data')]
    public User $user;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(ChangePasswordFormType::class, $this->user);
    }
}
