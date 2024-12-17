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

use WeakMap;

class Once
{
    /**
     * The current globally used instance.
     *
     * @var null|static
     */
    protected static ?self $instance = null;

    /**
     * Indicates if the once instance is enabled.
     */
    protected static bool $enabled = true;

    /**
     * Create a new once instance.
     *
     * @param WeakMap<object, array<string, mixed>> $values
     */
    protected function __construct(protected WeakMap $values)
    {
    }

    /**
     * Create a new once instance.
     */
    public static function instance(): static
    {
        return static::$instance ??= new static(new WeakMap());
    }

    /**
     * Get the value of the given onceable.
     */
    public function value(Onceable $onceable): mixed
    {
        if (! static::$enabled) {
            return call_user_func($onceable->callable);
        }

        $object = $onceable->object ?: $this;

        $hash = $onceable->hash;

        if (isset($this->values[$object][$hash])) {
            return $this->values[$object][$hash];
        }

        if (! isset($this->values[$object])) {
            $this->values[$object] = [];
        }

        return $this->values[$object][$hash] = call_user_func($onceable->callable);
    }

    /**
     * Re-enable the once instance if it was disabled.
     */
    public static function enable(): void
    {
        static::$enabled = true;
    }

    /**
     * Disable the once instance.
     */
    public static function disable(): void
    {
        static::$enabled = false;
    }

    /**
     * Flush the once instance.
     */
    public static function flush(): void
    {
        static::$instance = null;
    }
}
