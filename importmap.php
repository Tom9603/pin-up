<?php

return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],

    './js/menu.js' => ['path' => './assets/js/menu.js'],
    './js/back-to-top.js' => ['path' => './assets/js/back-to-top.js'],
    './js/stars.js' => ['path' => './assets/js/stars.js'],
    './js/blog.js' => ['path' => './assets/js/blog.js'],
    './js/article.js' => ['path' => './assets/js/article.js'],
    './js/carrousel.js' => ['path' => './assets/js/carrousel.js'],
    './js/bell.js' => ['path' => './assets/js/bell.js'],

    '@hotwired/stimulus' => ['version' => '3.2.2'],
    '@symfony/stimulus-bundle' => ['path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js'],
];
