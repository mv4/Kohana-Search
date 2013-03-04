<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * Описание
 *
 */
class Kohana_ZLS extends ZLS_Where {

    static $singleton = array();
    protected $_analyzer;
    protected $_config;
    protected $_index;
    protected $_query = NULL;
    protected $_last_hits = array();
    protected $_sortField = NULL;
    protected $_sortType = SORT_REGULAR;
    protected $_sortOrder = SORT_ASC;

    public function reset()
    {
        parent::reset();
        $this->_last_hits = array();
        $this->_sortField = NULL;
        $this->_sortOrder = SORT_ASC;
        $this->_sortType = SORT_REGULAR;
    }

    /**
     * Данные поля не разбиваются на лексемы,
     * но индексируются и полностью сохраняются в индексе.
     * Сохраненные данные поля могут быть получены из индекса.
     */

    const Keyword = 1;

    /**
     * Данные поля разбиваются на лексемы, индексируются
     * и полностью сохраняются в индексе.
     */
    const Text = 2;

    /**
     * Данные поля разбиваются на лексемы и индексируются,
     * но не сохраняются в индексе.
     */
    const unStored = 3;

    /**
     * Бинарное поле, данные которого не разбиваются на лексемы и не индексируются,
     * но сохраняются в индексе.
     */
    const binary = 4;

    /**
     * Данные поля не разбиваются на лексемы и не индексируются,
     * но полностью сохраняются в индексе.
     */
    const UnIndexed = 5;

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

    public function config_diff($config)
    {
        return !(bool) count(array_diff($this->_config, $config));
    }

    protected function __construct($config, $name = 'default')
    {
        if (Arr::is_array($config))
        {
            $this->_config = $config;
        }
        else
        {
            throw new Kohana_Exception('Не верные данные конфига');
        }

        $analyzer = new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num();

        //подключение фильтров
        foreach (Arr::get($this->_config, 'filters', array()) as $filter)
        {
            $class = 'Zend_Search_Lucene_Analysis_TokenFilter_'.Text::ucfirst($filter);
            $analyzer->addFilter(new $class());
        }

        Zend_Search_Lucene::setResultSetLimit(100);

        Zend_Search_Lucene_Analysis_Analyzer::setDefault($analyzer);
        //кодировка
        Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding(Kohana::$charset);
        try
        {
            $this->_index = Zend_Search_Lucene::open(Arr::get($this->_config, 'directory').DIRECTORY_SEPARATOR.$name);
        }
        catch (Exception $e)
        {
            $this->_index = Zend_Search_Lucene::create(Arr::get($this->_config, 'directory').DIRECTORY_SEPARATOR.$name);
        }
        parent::__construct();
    }

    /**
     * array(
     *   array(field_name, value, type),
     *   array(field_name, value, type),
     *   ...
     *   array(field_name, value, type),
     * )
     * @param type $params
     */
    public function index($params = array())
    {
        $doc = new Zend_Search_Lucene_Document();
        foreach ($params as $param)
        {
            list($filed, $value, $type) = $param;
            switch ($type)
            {
                case self::Keyword:
                    $doc->addField(Zend_Search_Lucene_Field::Keyword($filed, $value));
                    break;
                case self::Text:
                    $doc->addField(Zend_Search_Lucene_Field::Text($filed, $value, Kohana::$charset));
                    break;
                case self::unStored:
                    $doc->addField(Zend_Search_Lucene_Field::UnStored($filed, $value, Kohana::$charset));
                    break;
                case self::UnIndexed:
                    $doc->addField(Zend_Search_Lucene_Field::UnIndexed($filed, $value, Kohana::$charset));
                    break;
                case NULL:break;
                default:
                    $doc->addField(Zend_Search_Lucene_Field::UnStored($filed, $value, Kohana::$charset));
                    break;
            }
        }

        $this->_index->addDocument($doc);
        $this->_index->commit();

        return $this;
    }

    protected function _find($query, $sortField, $sortType, $sortOrder)
    {


        //делаем запрос
        if (is_null($sortField))
        {

            $r = $this->_index->find(Zend_Search_Lucene_Search_QueryParser::parse($query));
           // die('12');
            return $r;
        }
        else
        {
            return $this->_index->find(Zend_Search_Lucene_Search_QueryParser::parse($query), $sortField, $sortType, $sortOrder);
        }
    }

    public function find($query = NULL)
    {


        if (!is_null($query))
        {
            $query = $query;
        }
        else
        {
            $query = $this->_query();
        }

        $this->_last_hits = $this->_find($query, $this->_sortField, $this->_sortType, $this->_sortOrder);

        return $this;
    }

    public function hits()
    {
        return $this->_last_hits;
    }

    /**
     *   удаление из индекса найденых результатов;
     */
    public function delete()
    {
        if (count($this->_last_hits) == 0 AND !is_null($this->_query()))
        {
            $hits = $this->_find($this->_query(), $this->_sortField, $this->_sortType, $this->_sortOrder);
            foreach ($hits as $hit)
            {
                $this->_index->delete($hit->id);
            }
        }
        elseif (count($this->_last_hits) > 0)
        {
            foreach ($this->_last_hits as $hit)
            {
                $this->_index->delete($hit->id);
            }
        }
        return $this;
    }

    public function reindex($hit, $param)
    {
        $this->_index->delete($hit->id);
        $this->index($param);

        return $this;
    }

}

