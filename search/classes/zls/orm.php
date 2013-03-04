<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * Описание
 *
 * @package    project
 * @copyright  15web.ru
 */
abstract class ZLS_Orm extends ZLS_Where {

    protected $_tag = NULL;
    protected $_pk = NULL;
    protected $_orm = NULL;
    protected $_orm_loaded = FALSE;
    protected $_fields = array();
    protected $_loaded = FALSE;
    protected $_config_name = 'default';
    protected $_config = array();
    protected $_old_values = array();

    protected function _initialize()
    {

    }

    /**
     * Сериализация модели
     * @return type
     */
    public function serialize()
    {
        foreach (array('_tag', '_pk', '_fields', '_loaded', '_config', '_config_name') as $var)
        {
            $data[$var] = $this->{$var};
        }

        return serialize($data);
    }

    public function unserialize($data)
    {
        // Initialize model
        $this->_initialize();

        foreach (unserialize($data) as $name => $var)
        {
            $this->{$name} = $var;
        }

        return $this;
    }

    public function __construct()
    {

        foreach ($this->fields() as $field => $type)
        {
            $this->_fields[$field] = array(
                'value'    => NULL,
                'type'     => $type,
                'callback' => Arr::get($this->callback(), $field, NULL)
            );
        }

        $this->_config = Arr::merge(Kohana::$config->load('search.'.$this->_config_name), $this->_config);
    }

    public function __get($field)
    {
        if (Arr::get($this->_fields, $field, FALSE))
        {
            return $this->_fields[$field]['value'];
        }
        else
        {
            throw new Kohana_Exception('The :property property does not exist in the :class class',
                    array(':property' => $field, ':class'    => get_class($this)));
        }
    }

    public function __set($field, $value)
    {
        if (Arr::get($this->_fields, $field, FALSE))
        {
            $this->_fields[$field]['value'] = $value;
        }
        else
        {
            throw new Kohana_Exception('The :property property does not exist in the :class class',
                    array(':property' => $field, ':class'    => get_class($this)));
        }
    }

    /**
     * array(
     *   'field_name' => type // ZLS::Keyword, ZLS::Text...
     * );
     * @return type
     */
    public function fields()
    {
        return array();
    }

    public function callback()
    {
        return array();
    }

    public function events()
    {
        return array(
            'delete' => FALSE
        );
    }

    public function get($field)
    {
        return Arr::get($this->_values, $field, NULL);
    }

    public function tag()
    {
        return $this->_tag;
    }

    protected function _find_from_model()
    {
        $query = $this->where_begin();
        if (!is_null($this->tag()))
        {
            $query->where('_tag', $this->tag());
        }
        if ($this->_orm_loaded)
        {
            $query->where('_pk', $this->_orm->pk());
        }

        $query->where_end();

        if (trim($query->_query(), '()') != '')
        {
            return $query;
        }

        return $this;
    }

    /**
     * получаем модель
     * @param type $model
     * @return \ZLS_Orm
     */
    public function orm($model = NULL)
    {
        if (is_null($model))
        {
            if (!$this->_orm_loaded)
            {
                $this->_orm = ORM::factory($this->tag(), $this->_pk);
            }
            return $this->_orm;
        }

        $this->_orm = $model;

        $this->_pk = $this->_orm->id;

        if (is_null($this->tag()))
        {
            $this->_tag = $this->_orm->object_name();
        }

        if ($this->_orm->loaded())
        {
            $result = ZLS_Search::factory($this->_config_name, $this->_config)
                    ->where('_tag', $this->tag())
                    ->where('_pk', $this->_orm->pk())
                    ->find()
                    ->hits();
            if (count($result) > 0)
            {
                foreach ($this->_fields as $field => $value)
                {
                    $this->{$field} = $result[0]->{$field};
                    //$this->_old_values[$field] = $result[0]->{$field};
                }
                $this->_loaded = TRUE;
            }
            $this->_orm_loaded = TRUE;
        }


        foreach ($this->_orm->list_columns() as $field => $v)
        {
            if (Arr::get($this->_fields, $field, FALSE))
            {
                $this->$field = $this->_orm->$field;
            }
        }
        return $this;
    }

    /**
     * Передать значения
     * @param ORM $values OR array
     * @return \ZLS_Orm
     */
    public function values($values)
    {

        if ($values instanceof ORM)
        {

            return $this->orm($values);
        }
        else
        {
            foreach ($values as $field => $value)
            {
                if (Arr::get($this->_fields, $field, FALSE))
                {
                    $this->$field = $value;
                }
            }
        }
        return $this;
    }

    protected function _get_params($value, $params)
    {
        foreach ($params as $param => $v)
        {
            if ($v == ':value')
            {
                $params[$param] = $value;
            }
        }

        return $params;
    }

    protected function _parse_function($params, $value)
    {

        foreach ($params as $param)
        {

            $f = $param[0];
            $p = Arr::get($param, 1, array());
            if (is_array($f))
            {
                if ($f[0] == ':model')
                {
                    $value = call_user_func_array(array($this->_orm, $f[1]), $this->_get_params($value, $p));
                }
                else
                {
                    $value = call_user_func_array($f, $this->_get_params($value, $p));
                }
            }
            elseif (is_string($f) AND strpos($f, '::') === TRUE)
            {
                list($class, $method) = explode('::', $f, 2);
                $method = new ReflectionMethod($class, $method);
                $value = $method->invokeArgs(NULL, $this->_get_params($value, $p));
            }
        }
        return $value;
    }

    /**
     * Вызов функций обработки входящих данных
     * @param type $field
     * @param type $value
     * @param type $callback
     * @return type
     */
    public function run_callback($field, $value, $callback)
    {
        return $this->_parse_function($callback, $value);
    }

    /**
     * Выполнение событий
     * @param type $action
     * @return boolean
     */
    public function run_events($action)
    {

        if (Arr::get($this->events(), $action, FALSE))
        {
            return $this->_parse_function(Arr::get($this->events(), $action), TRUE);
        }

        return TRUE;
    }

    /**
     * Загружен ли объект из индекса
     * @return type
     */
    public function loaded()
    {
        return $this->_loaded;
    }

    /**
     * Подготовка данных к индексации
     * @return type
     */
    protected function _index_fields()
    {
        $params = array(
            array(
                '_tag',
                $this->tag(),
                ZLS::Keyword),
            array(
                '_pk',
                $this->_orm->pk(),
                ZLS::Keyword
            )
        );
        foreach ($this->fields() as $field => $type)
        {
            $value = $this->{$field};
            if (Arr::get($this->callback(), $field, FALSE))
            {
                $value = $this->run_callback($field, $value, Arr::get($this->callback(), $field));
            }
            $params[] = array(
                $field, $value, ZLS::Text
            );
            $this->{$field} = $value;
        }

        //Добавим в поиск модель
        $params[] = array(
            '__class', $this->serialize(), ZLS::UnIndexed
        );


        return $params;
    }

    public function create()
    {
        $params = $this->_index_fields();
        if ($this->run_events('create.before'))
        {
            ZLS::factory($this->_config_name, $this->_config)->index($params);
        }
        $this->run_events('create.after');
        return $this;
    }

    public function update()
    {
        if ($this->run_events('update.before'))
        {
            $this->delete(FALSE);
            $this->create();
        }
        $this->run_events('update.after');
    }

    public function delete($events = TRUE)
    {
        if (!$events OR $this->run_events('delete.before'))
        {
            ZLS::factory($this->_config_name, $this->_config)
                    ->find('_tag:'.$this->tag().' AND _pk:'.$this->_orm->id)
                    ->delete();
        }
        $this->run_events('delete.after');
    }

    public function save()
    {
        return $this->loaded() ? $this->update() : $this->create();
    }

    public function highlight($field, $search_query){

        $length = 400;
        $half_length = round($length / 2);
        $end_char = '&hellip;';
        $words = Text::stemm_words($search_query);

        $text = strip_tags($this->{$field});

        // ищем точное совпадение
            $pos = mb_stripos($text, $search_query);

            if ($pos !== FALSE) // точное попадание
            {
                $search_query = "/$search_query/ui";
            }
            else // ищем по словам
            {
                $pos = 999999;
                $search_query = array();
                // ищем ближайшую к началу текста позицию попадания одного из слов
                foreach ($words as $word)
                {
                    $search_query[] = "/$word/ui";
                    $temp_pos = mb_stripos($text, $word);
                    if ($temp_pos !== FALSE AND $temp_pos < $pos)
                    {
                        $pos = $temp_pos;
                    }
                }
            }

            $strlen = mb_strlen($text);
            // вычисляем стартовую позицию, с учетом непревышения левой и правой границы текста
            $start = ($pos - $half_length >= 0) ? $pos - $half_length : 0;
            $start = ($pos + $half_length > $strlen) ? $strlen - $length : $start;
            $start = ($start < 0) ? 0 : $start;

            // вырезаем текст
            $text = trim(mb_substr($text, $start, $length));

            // подсвечиваем результаты
            $text = preg_replace($search_query, '<b>\\0</b>', $text);

            $strlen = mb_strlen($text);
            // вычисляем стартовую и конечную позицию для обрезки по словам, с учетом непревышения левой и правой границы текста
            $start = ($start > 0) ? mb_stripos($text, ' ') : 0;
            $end = ($start + $length < $strlen) ? mb_strripos($text, ' ') : $strlen;

            $text = mb_substr($text, $start, $strlen - $start - ($strlen - $end));

            if ($start > 0)
            {
                $text = $end_char.$text;
            }

            if ($end < $strlen)
            {
                $text = $text.$end_char;
            }




        return $text;
    }

}

