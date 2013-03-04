<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * Описание
 *
 * @package    project
 * @copyright  15web.ru
 */
class ZLS_Term {

    private $_term = array();
    private $_begin_operator = NULL;
    private $_next = NULL;
    private $_first = TRUE;

    public function set($field, $value, $operator = 'AND')
    {
        if (is_numeric($this->_next))
        {
            $this->_term[$this->_next]->set($field, $value, $operator);
            return $this;
        }
        $a = array_pop($this->_term);
        if ($a instanceof self)
        {
            $this->_term[] = $a->set($field, $value, $operator);
        }
        else
        {
            if (is_array($a))
            {
                $this->_term[] = $a;
            }
            $this->_term[] = array(
                $field, $value, $operator
            );
            if ($this->_first)
            {
                $this->_begin_operator = $operator;
                $this->_first = FALSE;
            }
        }
        return $this;
    }

    private function _query($term)
    {
        return $query = $term[0].':'.$term[1];
    }

    public function query()
    {
        $query = '';
        foreach ($this->_term as $term)
        {
            if ($term instanceof self)
            {
                $query .= ' '.$term->_begin_operator.' '.$term->query();
            }
            elseif (is_array($term))
            {
                if ($query == '')
                {
                    $query .= ' '.$this->_query($term);
                }
                else
                {
                    $query .= ' '.$term[2].' '.$this->_query($term);
                }
            }
        }

        if (is_null($this->_begin_operator))
        {
            return trim($query);
        }
        else
        {
            return '('.trim($query).')';
        }
    }

    public function begin()
    {
        if (is_numeric($this->_next))
        {
            $this->_term[$this->_next]->begin();
            return $this;
        }
        $this->_term[] = new self;
        $this->_next = count($this->_term) - 1;
        return $this;
    }

    public function end()
    {
        if (is_numeric($this->_next))
        {
            if ($this->_term[$this->_next]->end())
            {
                return FALSE;
            }
            else
            {
                $this->_term[] = NULL;
                $this->_next = NULL;
                return TRUE;
            }
        }
    }

}

