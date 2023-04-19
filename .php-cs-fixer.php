<?php

$header = <<<EOF
Akismet
(c) Omines Internetbureau B.V. - https://omines.nl/

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

$finder = PhpCsFixer\Finder::create()
    ->files()
    ->name('*.php')
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')->exclude('Fixtures/var')
;

$config = new PhpCsFixer\Config();
return $config
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,

        'declare_strict_types' => true,
        'strict_param' => true,
        'strict_comparison' => true,
        'header_comment' => ['header' => $header, 'location' => 'after_open'],

        'mb_str_functions' => true,
        'ordered_imports' => true,
    ])
    ->setFinder($finder)
    ;
