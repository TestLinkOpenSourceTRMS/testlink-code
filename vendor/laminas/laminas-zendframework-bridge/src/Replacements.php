<?php

/**
 * @see       https://github.com/laminas/laminas-zendframework-bridge for the canonical source repository
 * @copyright https://github.com/laminas/laminas-zendframework-bridge/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-zendframework-bridge/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ZendFrameworkBridge;

class Replacements
{
    /** @var string[] */
    private $replacements;

    public function __construct(array $additionalReplacements = [])
    {
        $this->replacements = array_merge(
            require __DIR__ . '/../config/replacements.php',
            $additionalReplacements
        );
    }
    
    /**
     * @param string $value
     * @return string
     */
    public function replace($value)
    {
        return strtr($value, $this->replacements);
    }
}
