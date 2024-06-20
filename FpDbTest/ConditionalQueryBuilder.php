<?php

declare(strict_types=1);

namespace FpDbTest;

use BadMethodCallException;
use InvalidArgumentException;

final class ConditionalQueryBuilder implements QueryBuilderInterface
{
    /**
     * If has conditions like "..{...}.." cut it to parts by separators "{" and "}"
     */
    private const CONDITIONAL_PARTS_REGEX = '/[^{}]+|{[^}]+}/';

    public function __construct(
        private ReplacerInterface $query_replacer,
        private mixed $arg_value_to_skip_condition_part = null,
    ) {
    }

    public function buildQuery(string $query, mixed ...$args): string
    {
        return preg_replace_callback(
            pattern: self::CONDITIONAL_PARTS_REGEX,
            callback: function ($query_parts_matches) use (&$args) {
                $query_part = $query_parts_matches[0];
                $args_part = $this->getPartOfArgsByQuery(query_part: $query_part, args: $args);
                $args = array_slice(array: $args, offset: count($args_part));
                return $this->buildQueryPart(query_part: $query_part, args_part: $args_part);
            },
            subject: $query
        ) ?? throw new BadMethodCallException("Regex string is bad");
    }

    private function getPartOfArgsByQuery(string $query_part, array $args): array
    {
        $part_args_cnt = $this->query_replacer->countQueryReplaces($query_part);
        if ($part_args_cnt > count($args)) {
            throw new InvalidArgumentException(
                "Not valid arguments count " . count($args) . " to: $query_part"
            );
        }

        return array_slice($args, 0, $part_args_cnt);
    }

    private function buildQueryPart(string $query_part, array $args_part): string
    {
        if ($this->isConditionalQueryPart($query_part)) {
            return $this->buildConditionalQueryPart(query_part: $query_part, args_part: $args_part);
        }
        return $this->query_replacer->replaceQueryArgs(query: $query_part, args: $args_part);
    }

    private function isConditionalQueryPart(string $query_part): bool
    {
        return str_starts_with($query_part, '{') && str_ends_with($query_part, '}');
    }

    private function buildConditionalQueryPart(string $query_part, array $args_part): string
    {
        if (in_array($this->arg_value_to_skip_condition_part, $args_part)) {
            return '';
        }
        if (!count($args_part)) {
            return $query_part;
        }
        $query_part = str_replace(['{', '}'], '', $query_part);
        return $this->query_replacer->replaceQueryArgs(query: $query_part, args: $args_part);
    }
}
