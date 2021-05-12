<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Selective Recall',
    'description' => 'Remember settings from a different request using a hash. E.g. recall settings or data used in the main request within an eID request.',
    'version' => '1.0.0-beta',
    'category' => 'services',
    'constraints' => [
        'depends' => [
            'php' => '7.2.0-7.4.99',
            'typo3' => '9.5.0-10.99.99',
        ],
    ],
    'state' => 'beta',
    'uploadfolder' => false,
    'createDirs' => '',
    'author' => 'Pixelant.net',
    'author_email' => 'info@pixelant.net',
    'author_company' => 'Pixelant.net',
    'autoload' => [
        'psr-4' => [
            'Pixelant\\Recall\\' => 'Classes/',
        ],
    ],
    'autoload-dev' => [
        'psr-4' => [
            'Pixelant\\Recall\\Tests\\' => 'Tests/',
        ],
    ],
];
