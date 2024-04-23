<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'recipes' => [
        'path' => './assets/js/recipes.js',
        'entrypoint' => true,
    ],
    'recipe' => [
        'path' => './assets/js/recipe.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
    'canvas-confetti' => [
        'version' => '1.9.2',
    ],
    '@symfony/ux-live-component' => [
        'path' => './vendor/symfony/ux-live-component/assets/dist/live_controller.js',
    ],
    'bootstrap' => [
        'version' => '5.3.3',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.3',
        'type' => 'css',
    ],
    'jquery' => [
        'version' => '3.7.1',
    ],
    '@fortawesome/fontawesome-free' => [
        'version' => '6.5.1',
    ],
    '@fortawesome/fontawesome-free/css/fontawesome.min.css' => [
        'version' => '6.5.1',
        'type' => 'css',
    ],
    'tom-select' => [
        'version' => '2.3.1',
    ],
    'tom-select/dist/css/tom-select.bootstrap5.css' => [
        'version' => '2.3.1',
        'type' => 'css',
    ],
    'simplebar' => [
        'version' => '6.2.5',
    ],
    'can-use-dom' => [
        'version' => '0.1.0',
    ],
    'simplebar-core' => [
        'version' => '1.2.4',
    ],
    'simplebar/dist/simplebar.min.css' => [
        'version' => '6.2.5',
        'type' => 'css',
    ],
    'lodash-es' => [
        'version' => '4.17.21',
    ],
    'simplebar-core/dist/simplebar.min.css' => [
        'version' => '1.2.4',
        'type' => 'css',
    ],
    'fontawesome-iconpicker' => [
        'version' => '3.2.0',
    ],
    'nouislider' => [
        'version' => '15.7.1',
    ],
    'nouislider/dist/nouislider.min.css' => [
        'version' => '15.7.1',
        'type' => 'css',
    ],
    'tiny-slider' => [
        'version' => '2.9.4',
    ],
    'feather-icons' => [
        'version' => '4.29.1',
    ],
    'typed.js' => [
        'version' => '2.1.0',
    ],
    'tippy.js' => [
        'version' => '6.3.7',
    ],
    'flatpickr' => [
        'version' => '4.6.13',
    ],
    'flatpickr/dist/flatpickr.min.css' => [
        'version' => '4.6.13',
        'type' => 'css',
    ],
    '@yaireo/tagify' => [
        'version' => '4.24.0',
    ],
];
