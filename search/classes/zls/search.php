<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * Описание
 *
 * @package    project
 * @copyright  15web.ru
 */
class ZLS_Search extends ZLS {

    public static function factory($config = 'default', $merge_config = array())
    {
        $name = $config;
        $config = Arr::merge(Kohana::$config->load('search.'.$config), $merge_config);

        foreach (self::$singleton as $class)
        {
            if ($class->config_diff($config))
            {
                return $class;
            }
        }

        $class = new self($config, $name);
        $singleton[] = $class;
        return $class;
    }

    public function hits()
    {
        return $this->_last_hits_as_model(parent::hits());
    }

    /**
     * Результат поиска возвращаем в виде модели
     * @param array $hits
     * @return array
     */
    protected function _last_hits_as_model($hits)
    {

        $return = array();
        foreach ($hits as $hit)
        {
            $return[] = Search::factory($hit->_tag)->unserialize($hit->__class);
        }
        return $return;
    }

}

