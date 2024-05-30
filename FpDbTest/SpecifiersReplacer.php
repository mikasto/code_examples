<?php

declare(strict_types=1);

namespace FpDbTest;

use Exception;
use FpDbTest\Specifiers\SpecifierInterface;
use FpDbTest\Specifiers\SpecifiersConfigInterface;

final class SpecifiersReplacer implements ReplacerInterface
{
    public function __construct(private SpecifiersConfigInterface $specifiers_config)
    {
    }

    public function countReplaces(string $query): int
    {
        if (!preg_match_all($this->specifiers_config->getRegex(), $query, $matches)) {
            // part have no specifiers
            return 0;
        }
        return count($matches[0]);
    }

    public function replace(string $query, array $args = []): string
    {
        $arg_cnt = 0;
        return preg_replace_callback(
            pattern: $this->specifiers_config->getRegex(),
            callback: function ($matches) use ($args, &$arg_cnt) {
                $specifier_mask = $matches[0];
                return $this->getSpecifierByMask($specifier_mask)->getValue($args[$arg_cnt++]);
            },
            subject: $query
        );
    }

    private function getSpecifierByMask(string $mask): SpecifierInterface
    {
        foreach ($this->specifiers_config->getList() as $specifier) {
            if ($specifier->getMask() === $mask) {
                return $specifier;
            }
        }

        throw new Exception('Mask not found');
    }
}
