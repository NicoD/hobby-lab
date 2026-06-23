<?php

declare(strict_types=1);

namespace Tools\PhpStan;

use App\Shared\Application\Bus\Command;
use App\Shared\Application\Bus\Query;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

/** @implements Rule<InClassNode> */
final readonly class HandlerReturnTypeRule implements Rule
{
    public function __construct(private ReflectionProvider $reflectionProvider)
    {
    }

    #[\Override]
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /** @return list<\PHPStan\Rules\IdentifierRuleError> */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $scope->getClassReflection();

        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection || $classReflection->isAbstract() || $classReflection->isInterface()) {
            return [];
        }

        if (!$classReflection->hasMethod('__invoke')) {
            return [];
        }

        $invokeMethod = $classReflection->getMethod('__invoke', $scope);
        $variant = $invokeMethod->getOnlyVariant();
        $parameters = $variant->getParameters();

        if ([] === $parameters) {
            return [];
        }

        $firstParamType = $parameters[0]->getType();
        $expectedReturnType = $this->resolveExpectedReturnType($firstParamType);

        if (!$expectedReturnType instanceof \PHPStan\Type\Type || $expectedReturnType instanceof MixedType) {
            return [];
        }

        $actualReturnType = $variant->getReturnType();

        if ($actualReturnType instanceof MixedType) {
            return [];
        }

        if ($expectedReturnType->isSuperTypeOf($actualReturnType)->yes()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(\sprintf(
                '%s::__invoke() returns %s but %s declares %s.',
                $classReflection->getDisplayName(),
                $actualReturnType->describe(VerbosityLevel::typeOnly()),
                $firstParamType->describe(VerbosityLevel::typeOnly()),
                $expectedReturnType->describe(VerbosityLevel::typeOnly()),
            ))->identifier('messenger.handlerReturnType')->build(),
        ];
    }

    private function resolveExpectedReturnType(Type $messageType): ?Type
    {
        foreach ($messageType->getObjectClassNames() as $className) {
            if (!$this->reflectionProvider->hasClass($className)) {
                continue;
            }

            $messageReflection = $this->reflectionProvider->getClass($className);

            foreach ([Command::class, Query::class] as $busInterface) {
                if (!$messageReflection->implementsInterface($busInterface)) {
                    continue;
                }

                $ancestor = $messageReflection->getAncestorWithClassName($busInterface);

                if (!$ancestor instanceof \PHPStan\Reflection\ClassReflection) {
                    continue;
                }

                $tResult = $ancestor->getActiveTemplateTypeMap()->getType('TResult');

                if ($tResult instanceof \PHPStan\Type\Type) {
                    return $tResult;
                }
            }
        }

        return null;
    }
}
