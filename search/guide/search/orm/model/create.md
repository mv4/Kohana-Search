#Построение модели

Файл с классом модели должен находится в директории `classes/model/search`
и иметь следующий вид:

    class Model_Search_Name extends ZLS_Orm
    {
        ...
    }

Все методы описанные в классе будут доступны из результата поиска.

##Связь с ORM-моделью(relationship)
Название модели по умолчанию должно совпадать с названием ORM-модели, иначе необходимо указать связь с какой именно ORM-моделью должен работать класс

    protected $_tag = Name

##Описание полей модели(fields)
Поля перечисляются в массиве метода fields()

    public function fields()
    {
        return array(
            'field_1',
            'field_2 => FALSE',
            ...
            'field_n',
        );
    }

При совпадении названий полей с полями описанных в ORM-модели значения для индексации будут браться из ORM-модели,
остальные поля необходимо будет указывать принудительно.

Если указать флаг FALSE для поля `'field_2 => FALSE'`, то это поле не будет учавствовать в поиске, но будет доступно для чтения из поискового результата.

##Функции обработки данных(callback)

    public function callback()
    {
        return array(
            'field_1' => ARRAY_FUNCTION,
            'field_2' => ARRAY_FUNCTION,
            ...
            'field_n' => ARRAY_FUNCTION,
        );
    }

[!!] Можно использовать параметры `:model`, `:value` - ссылающиеся на ORM-модель и значение поля соответсвенно.

##События(events)

    public function events()
    {
        return array(
            'create.before' => ARRAY_FUNCTION,
            'create.after'  => ARRAY_FUNCTION,
            'update.before' => ARRAY_FUNCTION,
            'update.after'  => ARRAY_FUNCTION,
            'delete.before' => ARRAY_FUNCTION,
            'delete.after'  => ARRAY_FUNCTION
        );
    }

Перечисляется набор функция выполняющиеся до `before` или после `after` выполнения действий: индексации `$search->index()`,
переиндексации `$search->reindex()`, удалении `$search->delete()`.

[!!] Если для события `create.before` одна из функций вернет значение `FALSE`, то индексация значение не произойдет.

[!!] Можно использовать параметры `:model`, `:value` - ссылающиеся на ORM-модель и значение поля соответсвенно.