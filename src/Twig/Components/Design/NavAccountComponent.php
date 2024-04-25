<?php

namespace App\Twig\Components\Design;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'nav_account', template: 'components/design/nav_account.html.twig')]
class NavAccountComponent
{
    public array $routes = [
        [
            'current' => '',
            'path' => '',
            'label' => '',
        ],
    ];
}
