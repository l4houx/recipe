<?php

namespace App\Twig\Components\Design;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'btn_account', template: 'components/design/btn_account.html.twig')]
class BtnAccountComponent
{
    public array $routes = [
        [
            'current' => 'dashboard_account_profile',
            'path' => 'dashboard_creator_account_dashboard',
            'label' => 'My dashboard',
        ],
        [
            'current' => 'dashboard_creator_account_index',
            'path' => 'dashboard_creator_account_dashboard',
            'label' => 'My dashboard',
        ],
        [
            'current' => 'dashboard_creator_account_change_password',
            'path' => 'dashboard_creator_account_dashboard',
            'label' => 'My dashboard',
        ],
        [
            'current' => 'dashboard_account_recipe_index',
            'path' => 'dashboard_account_recipe_new',
            'label' => 'New recipe',
        ],
        [
            'current' => 'dashboard_creator_account_dashboard',
            'path' => 'dashboard_creator_account_index',
            'label' => 'Account Setting',
        ],
    ];
}
