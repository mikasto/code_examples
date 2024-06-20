<?php

declare(strict_types=1);

namespace FpDbTest;

use FpDbTest\Specifiers\SpecifiersFactory;
use FpDbTest\Specifiers\SpecifiersMapInterface;
use InvalidArgumentException;
use mysqli;

final class SpecifiersReplacer implements ReplacerInterface
{
    private string $specifiers_masks_regex;
    private array $specifiers_masks_to_types;

    public function __construct(private mysqli $mysqli, SpecifiersMapInterface $specifiers_map)
    {
        $this->specifiers_masks_to_types = $specifiers_map::getMasksToTypesByRegexPriority();
    }

    public function countQueryReplaces(string $query): int
    {
        if (!preg_match_all($this->getSpecifiersMasksRegex(), $query, $matches)) {
            return 0;
        }
        return count($matches[0]);
    }

    private function getSpecifiersMasksRegex(): string
    {
        if (empty($this->specifiers_masks_regex)) {
            $this->specifiers_masks_regex = $this->getSpecifiersMasksRegexNoCache();
        }
        return $this->specifiers_masks_regex;
    }

    private function getSpecifiersMasksRegexNoCache(): string
    {
        $specifiers_slashed_masks = [];
        foreach ($this->specifiers_masks_to_types as $mask => $type) {
            $specifiers_slashed_masks[] = addcslashes($mask, '?');
        }
        return '/' . join('|', $specifiers_slashed_masks) . '/';
    }

    public function replaceQueryArgs(string $query, array $args = []): string
    {
        $specifier_factory = new SpecifiersFactory($this->mysqli);
        $specifier_masks_to_types = $this->specifiers_masks_to_types;
        return preg_replace_callback(
            pattern: $this->getSpecifiersMasksRegex(),
            callback: static function ($specifier_matches) use (
                &$args,
                $query,
                $specifier_factory,
                $specifier_masks_to_types
            ) {
                $specifier_mask = $specifier_matches[0];
                self::validateQueryArgumentsCount($query, $args);
                $arg = array_shift($args);
                $specifier_type = $specifier_masks_to_types[$specifier_mask];
                $specifier = $specifier_factory->getInstanceByType($specifier_type);
                return $specifier->getValue($arg);
            },
            subject: $query
        );
    }

    private static function validateQueryArgumentsCount(string $query, array $args): void
    {
        if (!count($args)) {
            throw new InvalidArgumentException("Arguments for query '$query' not found");
        }
    }
}
