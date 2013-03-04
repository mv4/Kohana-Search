# Конфигурация
По умолчанию конфигурационный файл находится в `MODPATH/search/config/search.php`.
Вы можете скопировать этот файл на `APPPATH/config/search.php` и вносить изменения в конфигурацию, в соответствии с каскадной файловой системы.

## Файл конфигурации
Файл содержит массив групп конфигураци для каждого докумета индекса.


    string INSTANCE_NAME => array(
        'directory' => string ROOT_DIRECTORY
        'analyzer'  => string ANALYZER
        'filters'   => array FILTERS_ARRAY,
    )

INSTANCE_NAME
: Название конфигурации, может быть любое, кроме `default`.

ROOT_DIRECTORY
: Базовая (root) директория куда будут складываться индексы.

ANALYZER
: Анализатор текста, смотрим [Zend Lucene Search](http://framework.zend.com/manual/1.12/ru/zend.search.lucene.extending.html#zend.search.lucene.extending.analysis).

FILTERS_ARRAY
: Набор фильтров обработки текста.

## Директория хранения индексов
Для каждой конфигурации создается индивидуальная папка для хранения документов индекса.
Эта папка формируется автоматически, её путь формируется в виде ROOT_DIRECTORY/INSTANCE_NAME.

## Пример
    'ru' => array(
        'directory' => APPPATH.'lucene',
        'analyzer'  => 'Utf8Num',
        'filters'   => array('morphy')
    ),
    'en' => array(
        'directory' => APPPATH.'lucene',
        'analyzer'  => 'Utf8_CaseInsensitive',
        'filters'   => array('ShortWords')
    )

