<?php
/**
 * @license MIT
 *
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

declare(strict_types=1);

namespace SimpleSecureWP\SimpleSecureStripe\DI\Definition\Resolver;

use SimpleSecureWP\SimpleSecureStripe\DI\Definition\Definition;
use SimpleSecureWP\SimpleSecureStripe\DI\Definition\EnvironmentVariableDefinition;
use SimpleSecureWP\SimpleSecureStripe\DI\Definition\Exception\InvalidDefinition;

/**
 * Resolves a environment variable definition to a value.
 *
 * @author James Harris <james.harris@icecave.com.au>
 */
class EnvironmentVariableResolver implements DefinitionResolver
{
    /**
     * @var DefinitionResolver
     */
    private $definitionResolver;

    /**
     * @var callable
     */
    private $variableReader;

    public function __construct(DefinitionResolver $definitionResolver, $variableReader = 'getenv')
    {
        $this->definitionResolver = $definitionResolver;
        $this->variableReader = $variableReader;
    }

    /**
     * Resolve an environment variable definition to a value.
     *
     * @param EnvironmentVariableDefinition $definition
     */
    public function resolve(Definition $definition, array $parameters = [])
    {
        $value = call_user_func($this->variableReader, $definition->getVariableName());

        if (false !== $value) {
            return $value;
        }

        if (!$definition->isOptional()) {
            throw new InvalidDefinition(sprintf(
                "The environment variable '%s' has not been defined",
                $definition->getVariableName()
            ));
        }

        $value = $definition->getDefaultValue();

        // Nested definition
        if ($value instanceof Definition) {
            return $this->definitionResolver->resolve($value);
        }

        return $value;
    }

    public function isResolvable(Definition $definition, array $parameters = []) : bool
    {
        return true;
    }
}
