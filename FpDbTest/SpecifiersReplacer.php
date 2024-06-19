<?php

declare(strict_types=1);

namespace FpDbTest;

use FpDbTest\Specifiers\SpecifierInterface;
use FpDbTest\Specifiers\SpecifiersConfigInterface;
use InvalidArgumentException;

final class SpecifiersReplacer implements ReplacerInterface
{
    private string $specifiers_masks_regex_cache = '';

    public function __construct(private SpecifiersConfigInterface $specifiers_config)
    {
    }

    public function countQueryReplaces(string $query): int
    {
        if (!preg_match_all($this->getSpecifiersMasksRegexCached(), $query, $matches)) {
            return 0;
        }
        return count($matches[0]);
    }

    private function getSpecifiersMasksRegexCached()
    {
        if (empty($this->specifiers_masks_regex_cache)) {
            $this->specifiers_masks_regex_cache = $this->getSpecifiersMasksRegex();
        }
        return $this->specifiers_masks_regex_cache;
    }

    private function getSpecifiersMasksRegex()
    {
        $specifiers = $this->specifiers_config->getPrioritySortedSpecifiersList();
        $specifiers_slashed_masks = array_map(
            callback: static function (SpecifierInterface $specifier): string {
                return addcslashes($specifier::MASK, '?');
            },
            array: $specifiers
        );
        return '/' . join('|', $specifiers_slashed_masks) . '/';
    }

    public function replaceQueryArgs(string $query, array $args = []): string
    {
        return preg_replace_callback(
            pattern: $this->getSpecifiersMasksRegexCached(),
            callback: function ($specifier_matches) use (&$args, $query) {
                $specifier_mask = $specifier_matches[0];
                $this->validateQueryArgumentsCount($query, $args);
                $arg = array_shift($args);
                return $this->getSpecifierByMask($specifier_mask)->getValue($arg);
            },
            subject: $query
        );
    }

    private function validateQueryArgumentsCount(string $query, array $args): void
    {
        if (!count($args)) {
            throw new InvalidArgumentException("Arguments for query '$query' not found");
        }
    }

    private function getSpecifierByMask(string $mask): SpecifierInterface
    {
        foreach ($this->specifiers_config->getPrioritySortedSpecifiersList() as $specifier) {
            if ($specifier::MASK === $mask) {
                return $specifier;
            }
        }

        throw new InvalidArgumentException("Not found specifier by mask '$mask'");
    }
}
