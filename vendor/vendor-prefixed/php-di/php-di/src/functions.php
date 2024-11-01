<?php
/**
 * @license MIT
 *
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

declare(strict_types=1);

namespace SimpleSecureWP\SimpleSecureStripe\DI;

use SimpleSecureWP\SimpleSecureStripe\DI\Definition\ArrayDefinitionExtension;
use SimpleSecureWP\SimpleSecureStripe\DI\Definition\EnvironmentVariableDefinition;
use SimpleSecureWP\SimpleSecureStripe\DI\Definition\Helper\AutowireDefinitionHelper;
use SimpleSecureWP\SimpleSecureStripe\DI\Definition\Helper\CreateDefinitionHelper;
use SimpleSecureWP\SimpleSecureStripe\DI\Definition\Helper\FactoryDefinitionHelper;
use SimpleSecureWP\SimpleSecureStripe\DI\Definition\Reference;
use SimpleSecureWP\SimpleSecureStripe\DI\Definition\StringDefinition;
use SimpleSecureWP\SimpleSecureStripe\DI\Definition\ValueDefinition;

if (! function_exists('SimpleSecureWP\SimpleSecureStripe\DI\value')) {
    /**
     * Helper for defining a value.
     *
     * @param mixed $value
     */
    function value($value) : ValueDefinition
    {
        return new ValueDefinition($value);
    }
}

if (! function_exists('SimpleSecureWP\SimpleSecureStripe\DI\create')) {
    /**
     * Helper for defining an object.
     *
     * @param string|null $className Class name of the object.
     *                               If null, the name of the entry (in the container) will be used as class name.
     */
    function create(string $className = null) : CreateDefinitionHelper
    {
        return new CreateDefinitionHelper($className);
    }
}

if (! function_exists('SimpleSecureWP\SimpleSecureStripe\DI\autowire')) {
    /**
     * Helper for autowiring an object.
     *
     * @param string|null $className Class name of the object.
     *                               If null, the name of the entry (in the container) will be used as class name.
     */
    function autowire(string $className = null) : AutowireDefinitionHelper
    {
        return new AutowireDefinitionHelper($className);
    }
}

if (! function_exists('SimpleSecureWP\SimpleSecureStripe\DI\factory')) {
    /**
     * Helper for defining a container entry using a factory function/callable.
     *
     * @param callable $factory The factory is a callable that takes the container as parameter
     *                          and returns the value to register in the container.
     */
    function factory($factory) : FactoryDefinitionHelper
    {
        return new FactoryDefinitionHelper($factory);
    }
}

if (! function_exists('SimpleSecureWP\SimpleSecureStripe\DI\decorate')) {
    /**
     * Decorate the previous definition using a callable.
     *
     * Example:
     *
     *     'foo' => decorate(function ($foo, $container) {
     *         return new CachedFoo($foo, $container->get('cache'));
     *     })
     *
     * @param callable $callable The callable takes the decorated object as first parameter and
     *                           the container as second.
     */
    function decorate($callable) : FactoryDefinitionHelper
    {
        return new FactoryDefinitionHelper($callable, true);
    }
}

if (! function_exists('SimpleSecureWP\SimpleSecureStripe\DI\get')) {
    /**
     * Helper for referencing another container entry in an object definition.
     */
    function get(string $entryName) : Reference
    {
        return new Reference($entryName);
    }
}

if (! function_exists('SimpleSecureWP\SimpleSecureStripe\DI\env')) {
    /**
     * Helper for referencing environment variables.
     *
     * @param string $variableName The name of the environment variable.
     * @param mixed $defaultValue The default value to be used if the environment variable is not defined.
     */
    function env(string $variableName, $defaultValue = null) : EnvironmentVariableDefinition
    {
        // Only mark as optional if the default value was *explicitly* provided.
        $isOptional = 2 === func_num_args();

        return new EnvironmentVariableDefinition($variableName, $isOptional, $defaultValue);
    }
}

if (! function_exists('SimpleSecureWP\SimpleSecureStripe\DI\add')) {
    /**
     * Helper for extending another definition.
     *
     * Example:
     *
     *     'log.backends' => SimpleSecureWP\SimpleSecureStripe\DI\add(SimpleSecureWP\SimpleSecureStripe\DI\get('My\Custom\LogBackend'))
     *
     * or:
     *
     *     'log.backends' => SimpleSecureWP\SimpleSecureStripe\DI\add([
     *         DI\get('My\Custom\LogBackend')
     *     ])
     *
     * @param mixed|array $values A value or an array of values to add to the array.
     *
     * @since 5.0
     */
    function add($values) : ArrayDefinitionExtension
    {
        if (! is_array($values)) {
            $values = [$values];
        }

        return new ArrayDefinitionExtension($values);
    }
}

if (! function_exists('SimpleSecureWP\SimpleSecureStripe\DI\string')) {
    /**
     * Helper for concatenating strings.
     *
     * Example:
     *
     *     'log.filename' => SimpleSecureWP\SimpleSecureStripe\DI\string('{app.path}/app.log')
     *
     * @param string $expression A string expression. Use the `{}` placeholders to reference other container entries.
     *
     * @since 5.0
     */
    function string(string $expression) : StringDefinition
    {
        return new StringDefinition($expression);
    }
}
