<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Support;

use Closure;
use ReflectionException;
use ReflectionFunction;

class Onceable
{
    /**
     * Create a new onceable instance.
     *
     * @param callable $callable
     */
    public function __construct(
        public string $hash,
        public ?object $object,
        public mixed $callable
    ) {
    }

    /**
     * Tries to create a new onceable instance from the given trace.
     *
     * @param array<int, array<string, mixed>> $trace
     */
    public static function tryFromTrace(array $trace, callable $callable): ?static
    {
        if (! is_null($hash = static::hashFromTrace($trace, $callable))) {
            $object = static::objectFromTrace($trace);

            return new static($hash, $object, $callable);
        }

        return null;
    }

    /**
     * Computes the object of the onceable from the given trace, if any.
     *
     * @param array<int, array<string, mixed>> $trace
     * @return null|object
     */
    protected static function objectFromTrace(array $trace)
    {
        return $trace[1]['object'] ?? null;
    }

    /**
     * Computes the hash of the onceable from the given trace.
     *
     * @param array<int, array<string, mixed>> $trace
     * @throws ReflectionException
     */
    protected static function hashFromTrace(array $trace, callable $callable): ?string
    {
        if (str_contains($trace[0]['file'] ?? '', 'eval()\'d code')) {
            return null;
        }

        $uses = array_map(
            fn (mixed $argument) => is_object($argument) ? spl_object_hash($argument) : $argument,
            $callable instanceof Closure ? (new ReflectionFunction($callable))->getStaticVariables() : [],
        );

        return md5(sprintf(
            '%s@%s%s:%s (%s)',
            $trace[0]['file'],
            isset($trace[1]['class']) ? ($trace[1]['class'] . '@') : '',
            $trace[1]['function'],
            $trace[0]['line'],
            serialize($uses),
        ));
    }
}
