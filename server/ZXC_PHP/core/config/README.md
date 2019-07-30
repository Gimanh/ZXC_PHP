#Config
This guide will describe the parameters based on the following structure of project directories  


    ├── MyProject
    │   ├── config
    |   |   └+──config.php
    │   ├── server
    |   |   ├──MyClases
    |   |   |   └+──-MyClases\BestClass.php # MyClases\ is namespace
    |   |   ├──ZXC_PHP
    |   |       └── core
    |   |          └+---index.php #This is ROOT dir for ZXC_PHP
    │   ├── web  
    |   |   +──index.php #
    │   └──        
    └── ...
    
**Autoloader**  
Add "server" directory to autoload  

```
'ZXC' => [
        'Autoload' => [
            '../../../server' => true
        ],
        ...
    ]
```

**Router**  
Данный параметр описывает сопоставление обработчиков с URI.  
* Параметр `Router/routes` `array` - массив доступных маршрутов.  
* Параметр `Router/routes/route` `string` - строка описывающая параметры маршрута с разделителем `|`. 
    * Первый элемент `POST ` - метод http запроса по которому можно обрабатывать данный маршрут.
    * Второй элемент `/` - указывает на URI который будет обрабатываться.  
    * Третий элемент `MyClases\BestClass:create` - указывает на сам обработчик который будет выполнять обработку запроса. 
    `MyClasesNamespace\BestClass` - имя класса включая пространство имен `create` метод класса который будет вызван. 
    Данные два пареметра разделяются двоеточием.
* Параметр `Router/routes` `array`
    
```
'ZXC' => [
        ...
        'Router' => [
            'notFound'=>function(){},
            'middleware' => [
                'CORS' => 'HS\CORS:handler',
            ],
            'methods' => ['POST' => true, 'GET' => true, 'OPTIONS' => true],
            'routes' => [
                [
                    'route' => 'POST|/|MyClases\BestClass:create'
                ]
            ]
        ],
        ...
    ]
```

**Structures**  
Раздел 'Structures' в конфигурационном файле описывает параметры для работы с файлами структур таблиц,
параметр 'dir' указывает с какой директории необходимо загружать файлы структур в случае если запрашиваемый 
файл структур не найден в зарегистрированых. Поиск файла осуществляется по имени структуры название переменной 
должно соответствовать названию струткры.
```
'ZXC' => [
        ...
        'Structures' => [
            [
                'dir' => '../../../server/Structures'
            ]
        ],
        ...
    ]
```
