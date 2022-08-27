<?php

$header = <<<EOF
Gitlab OAuth2 Provider
(c) Omines Internetbureau B.V. - https://omines.nl/

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

$finder = PhpCsFixer\Finder::create()
    ->files()
    ->name('*.php')
    ->name('*.php')
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/test')
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'header_comment' => ['header' => $header],

        'blank_line_before_return' => false,
        'phpdoc_align' => false,
        'phpdoc_separation' => false,
        'phpdoc_var_without_name' => false,
    ])
    ->setFinder($finder)
    ;
