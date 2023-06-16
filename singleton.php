<?php

class Singleton
{
    public static function instance()
    {
        static $instance=[];
        if (!isset($instance[static::class]))
        {
            $instance[static::class] = new static();
        }
        return $instance[static::class];
    }
    private function __construct() {}
    private function __clone() {}
    private function __sleep() {}
    private function __wakeup() {}
}

?>