<?php
namespace App\Common\Traits;

trait StaticInstance
{

    /**
     * @param array $params
     * @param bool $refresh
     * @return static
     */
    public static function instance($params = [], $refresh = false)
    {
        $key = get_called_class();
        $instance = null;
        $context = context();
        if ($context->has($key)) {
            $instance = $context->get($key);
        }

        if ($refresh || is_null($instance) || ! $instance instanceof static) {
            $instance = new static(...$params);
            $context->set($key, $instance);
        }

        return $instance;
    }

}
