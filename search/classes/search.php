<?php

defined('SYSPATH') or die('No direct script access.');

class Search {

    public static function factory($name = NULL, $config = 'default', $merge_config = array())
    {
        if (is_null($name))
        {
            return ZLS_Search::factory($config, $merge_config);
        }
        // Add the service prefix
        $class = self::name_class($name);

        $search = Model::factory($class);

        return $search;
    }

    /**
     * название класса
     * @param type $name
     * @return string
     */
    public static function name_class($name)
    {
        $name = Inflector::humanize($name);
        $name = 'Search '.UTF8::ucwords($name);
        $name = Inflector::underscore($name);
        return $name;
    }

    /**
     * Проверка на существование модели для поиска
     * @param string $name
     * @return boolean
     */
    public static function class_exists($name)
    {
        $name = self::name_class($name);
        $file = str_replace('_', '/', UTF8::strtolower($name));

        return Kohana::find_file('classes/model', $file) ? TRUE : FALSE;
    }

}

