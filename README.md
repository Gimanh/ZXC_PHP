<p align="center">
  <img width="300" height="300" src="Logo.png">
</p>
<h2 align="center">ZXC_PHP</h2>  


##Contribute 
Everyone is welcome to contribute!

An open source project is:
* Live emotions, communication with people around the world.  
* Access to accumulated knowledge and previous experience.  
* The maximum required approach to writing code, documentation and tests.
* Teamwork on the task.
* Openness to end users.  

## How to use

* Create your index.php with the following contents

```php
//require your configuration, this variable must have name $config
$config = require '../config/config.php';
//require zxc framework
$zxc = require '../server/ZXC_PHP/core/index.php';
//run you application
$zxc->go();
```

See [example here](https://github.com/Gimanh/ZXC_PHP/tree/examples)  


## Web server settings  
See [documentation here](https://github.com/Gimanh/ZXC_PHP/wiki/Web-server-settings)


#### Configuration  
Configuration for ZXC_PHP is simple php file which returns array with the following structure
```php

return [
    //All details for config see below
    'ZXC' => [
        'Modules' => [
            'ModuleName' => [
                'class' => '\Class\With\Full\Namespace\ClassName',
                'options' => [
                    //any options for module
                ]
            ]
        ],
        'Autoload' => [
            /**
             * root is ZXC_ROOT (index directories)
             */
            '../../' => true,
            '' => true
        ],
        'Router' => [
           //here is router config
        ]
    ]
];

```

#### Routing  
```php
'ZXC' => [
    'Router' => [
        
    ],
 ]
]
```

###Web server settings

####IIS
1. Activate URL Rewrite  
2. Create web.config file in your application root directory.
   ```xml 
   <?xml version="1.0" encoding="UTF-8"?>
   <configuration>  
     <system.webServer>
       <rewrite>
         <rules>
           <rule name="Application" stopProcessing="true">
             <match url=".*" ignoreCase="false" />
             <conditions logicalGrouping="MatchAll">
               <add input="{REQUEST_FILENAME}" matchType="IsFile" ignoreCase="false" negate="true" />
               <add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />
             </conditions>
             <action type="Rewrite" url="index.php" appendQueryString="true" />
           </rule>
         </rules>
       </rewrite>
     </system.webServer>
   </configuration>
    ```
2. Restart IIS.