<?php

use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/controllers',
        __DIR__ . '/database',
        __DIR__ . '/models',
        __DIR__ . '/index.php',
        __DIR__ . '/settings.php',
        __DIR__ . '/supla-scripts-manager.php',
        __DIR__ . '/../public',
        __DIR__ . '/../supla-scripts',
    ])
    ->withSkip([__DIR__ . '/../public/v1.0/vendor'])
    ->withPhpVersion(PhpVersion::PHP_82)
    // Compatibility fixes only: no constructor promotion, enums, readonly,
    // arrow functions, type additions, or other optional modernizations.
    ->withRules([
        Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector::class,
        Rector\CodeQuality\Rector\ClassMethod\OptionalParametersAfterRequiredRector::class,
        Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector::class,
        Rector\Php72\Rector\Assign\ListEachRector::class,
        Rector\Php72\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector::class,
        Rector\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector::class,
        Rector\Php72\Rector\FuncCall\GetClassOnNullRector::class,
        Rector\Php72\Rector\FuncCall\ParseStrWithResultArgumentRector::class,
        Rector\Php72\Rector\FuncCall\StringsAssertNakedRector::class,
        Rector\Php72\Rector\Unset_\UnsetCastRector::class,
        Rector\Php72\Rector\While_\WhileEachToForeachRector::class,
        Rector\Php73\Rector\FuncCall\RegexDashEscapeRector::class,
        Rector\Php74\Rector\ArrayDimFetch\CurlyToSquareBracketArrayStringRector::class,
        Rector\Php74\Rector\FuncCall\ArrayKeyExistsOnPropertyRector::class,
        Rector\Php74\Rector\FuncCall\FilterVarToAddSlashesRector::class,
        Rector\Php74\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector::class,
        Rector\Php74\Rector\Ternary\ParenthesizeNestedTernaryRector::class,
        Rector\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector::class,
        Rector\Php80\Rector\ClassMethod\FinalPrivateToPrivateVisibilityRector::class,
        Rector\Php80\Rector\ClassMethod\SetStateToStaticRector::class,
        Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector::class,
        Rector\Php81\Rector\FuncCall\NullToStrictIntPregSlitFuncCallLimitArgRector::class,
        Rector\Php82\Rector\Encapsed\VariableInStringInterpolationFixerRector::class,
        Rector\Php82\Rector\FuncCall\Utf8DecodeEncodeToMbConvertEncodingRector::class,
        Rector\Php83\Rector\FuncCall\RemoveGetClassGetParentClassNoArgsRector::class,
    ]);
