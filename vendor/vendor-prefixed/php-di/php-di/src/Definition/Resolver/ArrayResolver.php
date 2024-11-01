<?php
/**
 * @license MIT
 *
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

declare(strict_types=1);

namespace SimpleSecureWP\SimpleSecureStripe\DI\Definition\Resolver;

use SimpleSecureWP\SimpleSecureStripe\DI\Definition\ArrayDefinition;
use SimpleSecureWP\SimpleSecureStripe\DI\Definition\Definition;
use SimpleSecureWP\SimpleSecureStripe\DI\DependencyException;
use Exception;

/**
 * Resolves an array definition to a value.
 *
 * @since 5.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ArrayResolver implements DefinitionResolver
{
    /**
     * @var DefinitionResolver
     */
    private $definitionResolver;

    /**
     * @param DefinitionResolver $definitionResolver Used to resolve nested definitions.
     */
    public function __construct(DefinitionResolver $definitionResolver)
    {
        $this->definitionResolver = $definitionResolver;
    }

    /**
     * Resolve an array definition to a value.
     *
     * An array definition can contain simple values or references to other entries.
     *
     * @param ArrayDefinition $definition
     */
    public function resolve(Definition $definition, array $parameters = []) : array
    {
        $values = $definition->getValues();

        // Resolve nested definitions
        array_walk_recursive($values, function (&$value, $key) use ($definition) {
            if ($value instanceof Definition) {
                $value = $this->resolveDefinition($value, $definition, $key);
            }
        });

        return $values;
    }

    public function isResolvable(Definition $definition, array $parameters = []) : bool
    {
        return true;
    }

    private function resolveDefinition(Definition $value, ArrayDefinition $definition, $key)
    {
        try {
            return $this->definitionResolver->resolve($value);
        } catch (DependencyException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new DependencyException(sprintf(
                'Error while resolving %s[%s]. %s',
                $definition->getName(),
                $key,
                $e->getMessage()
            ), 0, $e);
        }
    }
}
