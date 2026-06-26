<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/packages/outbox-bundle/src',
    ]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony'        => true,
        '@Symfony:risky'  => true,
        'declare_strict_types'          => true,
        'strict_param'                  => true,
        'array_syntax'                  => ['syntax' => 'short'],
        'ordered_imports'               => ['sort_algorithm' => 'alpha'],
        'no_unused_imports'             => true,
        'native_function_invocation'    => ['include' => ['@compiler_optimized'], 'scope' => 'namespaced'],
        'phpdoc_to_comment'             => ['ignored_tags' => ['var', 'phpstan-var', 'phpstan-ignore']],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder);
