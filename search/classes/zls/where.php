<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * Описание
 *
 * @package    project
 * @copyright  15web.ru
 */
abstract class ZLS_Where {

    private $_term = NULL;
    protected $_query = NULL;

    public function reset()
    {
        $this->_query = NULL;
        $this->_term = array();
    }

    protected function __construct()
    {
        $this->_current_term = $this->_term = new ZLS_Term();
    }

    public function where($field, $value, $operator = 'AND')
    {
        $this->_term->set($field, $value, $operator);
        return $this;
    }

    public function where_and($field, $value)
    {
        $this->where($field, $value, 'AND');
        return $this;
    }

    public function where_or($field, $value)
    {
        $this->where($field, $value, 'OR');
        return $this;
    }

    public function where_begin()
    {
        $this->_term->begin();
        return $this;
    }

    public function where_end()
    {
        $this->_term->end();
        return $this;
    }

    protected function _query($query = NULL)
    {
        if (!is_null($query))
        {
            $this->_query = $query;
        }
        return $this->query();
    }

    public function query()
    {
        return $this->_term->query();
    }

}

