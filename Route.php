<?php
namespace Macro\Route;
class Route 
{
    /**
     * 路由
     * @var array
     */
    public static $uri = [];
    
    /**
     * 路由别名
     * @var array
     */
    public static $alias = [];
    /**
     * 模块
     * @var string
     */
    public static $module = '';
    
    /**
     * 路由表
     * @var array
     */
    public static $routeMap = [];
    
    /**
     * 路由外观者，监听路由请求
     * @param string $method 路由方法名
     * @param array $parameter 路由参数
     */
    public static function __callstatic($method, Array $parameter)
    {   
        $uri = rtrim($_SERVER['SCRIPT_NAME'], '/').$parameter[0];
        self::$uri[$parameter[0]] = $uri;
        self::$routeMap[$parameter[0]]['uri'] = $uri;
        self::$routeMap[$parameter[0]]['method'] = $method;
        if (isset($parameter[1]['as'])){
            self::$alias[$parameter[0]] = $parameter[1]['as'];
            self::$routeMap[$parameter[0]]['alias'] = $parameter[1]['as'];   
        }
        if (!is_null(self::$module)){
            $namespace = '\\App\\Http\\' . ucwords(self::$module) . '\Controller\\';
            self::$routeMap[$parameter[0]]['module'] = self::$module;
            self::$routeMap[$parameter[0]]['namespace'] = $namespace;
        }
        if (isset($parameter[1]['uses'])){
            self::$routeMap[$parameter[0]]['uses'] = $parameter[1]['uses'];
        }else {
            T("路由[$uri]配置错误，缺少uses配置项！");
        }
        if (isset($parameter[1]['require'])){
            self::$routeMap[$parameter[0]]['require'] = $parameter[1]['require'];
        }
    }
    
    /**
     * 路由组
     * @param array $parameter 请求方法条件配置实参
     * @param \Closure $callback 路由组所包含的路由请求闭包对象
     */
    public static function group(Array $parameter, \Closure $callback)
    {
        if (!is_array($parameter)){
            T('参数parameter的类型必须是数组！');
        }
        if (!$callback instanceof \Closure){
            T('参数callback的类型必须是闭包对象！');
        }
        $module = isset($parameter['module']) ? $parameter['module'] : '';
        self::$module = $module;
        $callback();
        self::$module = '';
    }
    

}