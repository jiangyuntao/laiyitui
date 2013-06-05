<?php
return array(
    'APP_STATUS' => 'debug',

    'SHOW_RUN_TIME'=>true,          // 运行时间显示
    'SHOW_ADV_TIME'=>true,          // 显示详细的运行时间
    'SHOW_DB_TIMES'=>true,          // 显示数据库查询和写入次数
    'SHOW_CACHE_TIMES'=>true,       // 显示缓存操作次数
    'SHOW_USE_MEM'=>true,           // 显示内存开销
    'SHOW_LOAD_FILE' =>true,   // 显示加载文件数
    'SHOW_FUN_TIMES'=>true ,  // 显示函数调用次数

    /**
    'APP_GROUP_LIST' => 'Home,Admin', //项目分组设定
    'DEFAULT_GROUP'  => 'Home', //默认分组
    /**/

    'URL_MODEL' => 2,
    'URL_CASE_INSENSITIVE' => true,
    'URL_ROUTER_ON' => true,
    'URL_ROUTE_RULES' => array(
    ),

    'TMPL_STRIP_SPACE' => false,

    'DB_TYPE' => 'mysql',
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'laiyitui_com',
    'DB_USER' => 'root',
    'DB_PWD' => '',
    'DB_PORT' => 3306,
    'DB_PREFIX' => '',
    'DB_CHARSET' => 'utf8',
    'DB_FIELDS_CACHE' => false,
    'DB_FIELDTYPE_CHECK' => true,

    'SESSION_AUTO_START' => false,
    'SALT' => 'o>r*f4JW3]/4_>w', // 切勿修改
    'COOKIE_EXPIRE' => 0, // 默认 Cookie 生命周期为浏览器进程
);
