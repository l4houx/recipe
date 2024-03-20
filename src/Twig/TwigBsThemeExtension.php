<?php

namespace App\Twig;

use App\Entity\User;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class TwigBsThemeExtension extends AbstractExtension
{
    public function __construct(
        private readonly Security $security, 
        private readonly RequestStack $requestStack
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('bs_theme', $this->getUserBsTheme(...)),
        ];
    }

    public function getUserBsTheme(): string
    {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            $theme = $user->getTheme();
        } else {
            $request = $this->requestStack->getCurrentRequest();
            $theme = $request ? $request->cookies->get('theme') : null;
        }

        if ($theme) {
            return "theme-$theme";
        }

        return '';
    }
}
