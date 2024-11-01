<?php
/**
 * @license MIT
 *
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

declare(strict_types=1);

namespace SimpleSecureWP\SimpleSecureStripe\DI\Definition;

use SimpleSecureWP\SimpleSecureStripe\DI\Definition\Exception\InvalidDefinition;

/**
 * Extends an array definition by adding new elements into it.
 *
 * @since 5.0
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ArrayDefinitionExtension extends ArrayDefinition implements ExtendsPreviousDefinition
{
    /**
     * @var ArrayDefinition
     */
    private $subDefinition;

    public function getValues() : array
    {
        if (! $this->subDefinition) {
            return parent::getValues();
        }

        return array_merge($this->subDefinition->getValues(), parent::getValues());
    }

    public function setExtendedDefinition(Definition $definition)
    {
        if (! $definition instanceof ArrayDefinition) {
            throw new InvalidDefinition(sprintf(
                'Definition %s tries to add array entries but the previous definition is not an array',
                $this->getName()
            ));
        }

        $this->subDefinition = $definition;
    }
}
