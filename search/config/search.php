<?php

defined('SYSPATH') or die('No direct access allowed.');
return array(
    'default' => array(
        'enabled'   => FALSE,
        'directory' => APPPATH.'lucene', //директория куда будут писаться индексы
        'lang'      => 'ru', //язык текста
        'filters'   => array(
            'morphy'
        ),
    ),
    'dir_morphy_dicts' => MODPATH.'search/vendor/phpmorphy-0.3.7/dicts'//путь до словарей PhpMorphy
);
