#Индексация
фывфы

    $search = ZLS::factory(INSTANCE_NAME, MERGE_CONFIG)
##Сохранение данных в индексе

    $search->index(ARRAY_PARAMS);



##Поиск по идексу
    $search->find(STRING_SEARCH);
STRING_SEARCH
:ключивые
##Удаление данных в индексе
    $search->delete();

##Обновление данных в индексе
    $search->reindex((ARRAY_PARAMS);

ARRAY_PARAMS
: array(array(field_name, value, type),array(field_name, value, type)...)

field_name - имя поля в индексе

value - значение

type - тип поля


