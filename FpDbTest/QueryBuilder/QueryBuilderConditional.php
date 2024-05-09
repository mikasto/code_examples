<?php

declare(strict_types=1);

namespace FpDbTest\QueryBuilder;

use FpDbTest\QueryBuilder\Replacer\ReplacerInterface;
use InvalidArgumentException;

/**
 * Query build class
 * Uses conditional specifiers in the queries and other simple condifiers with assigned arguments
 */
class QueryBuilderConditional implements QueryBuilderInterface
{
    public function __construct(
        private ReplacerInterface $query_replacer,
        private mixed $skip_value = null,
    ) {
    }

    final public function buildQuery(string $query, array $args = []): string
    {
        // cut query to parts with conditional and not
        $arg_cnt = 0;
        return preg_replace_callback(
            pattern: '/[^{}]+|{[^}]+}/',
            callback: function ($matches) use ($args, &$arg_cnt) {
                $query_part = $matches[0];
                return $this->buildQueryPart($query_part, $args, $arg_cnt);
            },
            subject: $query
        );
    }

    private function buildQueryPart(string $query_part, array $args, int &$arg_cnt): string
    {
        $need_args_cnt = $this->query_replacer->countReplaces($query_part);


        $arg_cnt += $need_args_cnt;
        if ($arg_cnt > count($args)) {
            throw new InvalidArgumentException("Not valid arguments count " . count($args));
        }

        return $this->buildQueryConditional(
            query: $query_part,
            args: array_slice($args, $arg_cnt - $need_args_cnt, $need_args_cnt),
        );
    }

    private function buildQueryConditional(string $query, array $args): string
    {
        // filters for conditional query
        $is_conditional = str_starts_with($query, '{') && str_ends_with($query, '}');
        if ($is_conditional && in_array($this->skip_value, $args)) {
            return '';
        }
        if ($is_conditional) {
            $query = str_replace(['{', '}'], '', $query);
        }

        if (!count($args)) {
            return $query;
        }

        return $this->query_replacer->replace($query, $args);
    }
}
