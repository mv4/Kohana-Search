<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Analysis
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: LowerCaseUtf8.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/** Zend_Search_Lucene_Analysis_TokenFilter */
require_once 'Zend/Search/Lucene/Analysis/TokenFilter.php';


/**
 * Lower case Token filter.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Analysis
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

class Zend_Search_Lucene_Analysis_TokenFilter_Morphy extends Zend_Search_Lucene_Analysis_TokenFilter
{
    protected $_defaultMorphyConfig;
    protected $_morphy;
    protected function getDefaultMorphyConfig(){
    	return array(
	      'options' => array(
          // storage type, follow types supported
          // PHPMORPHY_STORAGE_FILE - use file operations(fread, fseek) for dictionary access, this is very slow...
          // PHPMORPHY_STORAGE_SHM - load dictionary in shared memory(using shmop php extension), this is preferred mode
          // PHPMORPHY_STORAGE_MEM - load dict to memory each time when phpMorphy intialized, this useful when shmop ext. not activated. Speed same as for PHPMORPHY_STORAGE_SHM type
         'storage' => PHPMORPHY_STORAGE_FILE,
         //Enable prediction by suffix
         'predict_by_suffix' => true,
         // Enable prediction by prefix
         'predict_by_db' => true,
         'graminfo_as_text' => true,
        ),
        'lang' => 'ru_ru',// язык, определяет файлы словарей
        'dir' =>  Kohana::$config->load('search')->get('dir_morphy_dicts') // тут должны быть файлы словаря
	  );
    }
    /**
     * Object constructor
     */
    public function __construct()
    {
        $this->_defaultMorphyConfig = $this->getDefaultMorphyConfig();
        $this->_morphy = new phpMorphy($this->_defaultMorphyConfig['dir'], $this->_defaultMorphyConfig['lang'], $this->_defaultMorphyConfig['options']);
    }

    /**
     * Normalize Token or remove it (if null is returned)
     *
     * @param Zend_Search_Lucene_Analysis_Token $srcToken
     * @return Zend_Search_Lucene_Analysis_Token
     */
    public function normalize(Zend_Search_Lucene_Analysis_Token $srcToken)
    {

        $token = $this->_morphy->getPseudoRoot(UTF8::strtoupper($srcToken->getTermText()));
        if(empty($token) OR empty($token[0])){
        	$token = array($srcToken->getTermText());
        }
        $newToken = new Zend_Search_Lucene_Analysis_Token(
                                     mb_strtolower($token[0], 'UTF-8'),
                                     $srcToken->getStartOffset(),
                                     $srcToken->getEndOffset());

        $newToken->setPositionIncrement($srcToken->getPositionIncrement());

        return $newToken;
    }
}

