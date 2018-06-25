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
Add base route by HTTP POST method. This route will run class method "create"
```
'ZXC' => [
        ...
        'Router' => [
            [
                'route' => 'POST|/|MyClases\BestClass:create'
            ]
        ],
        ...
    ]
```

**Q**

