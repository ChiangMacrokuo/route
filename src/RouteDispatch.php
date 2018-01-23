<?php
namespace Macro\Route;
use Macro\Http\Request;
class RouteDispatch 
{
    /**
     * 路由调度
     * @param Route $route 路由
     */
    public static function dispatcher(Route $route)
    {
        $uri = Request::getUri();
        $method = Request::getMethod();
        $uriArr = preg_replace('/\/+/', '/', $route::$uri);
        $controllerAction = $parameter = array();
        $controller = $action = '';
        if (in_array($uri, $uriArr)){
            $position = array_keys($uriArr,$uri);
            $router = $route::$routeMap[$position[0]];
            if (strtoupper($router['method']) == $method || $router['method'] == 'any'){
                if (is_string($router['uses'])){
                    $controllerAction = explode('@', $router['uses']);
                }elseif (is_array($router['uses'])){
                    $controllerAction = $router['uses'];
                }elseif (is_object($router['uses']) && is_callable($router['uses']) &&  $router['uses'] instanceof \Closure){
                    $controllerAction = $router['uses']();
                }else {
                    T('路由['.$uri.']的uses配置项错误！');
                }
            }else {
                T('非法请求，路由['.$uri.']的请求方式错误！');
            }
            $controller = $router['namespace'] . $controllerAction[0];
        }else {
            foreach ($uriArr as $key => $originUri){
                $require = (!isset($route::$routeMap[$key]['require'])) ? array() : $route::$routeMap[$key]['require'];
                list($uriPattern, $paramterName) = self::generateUriPattern($originUri, $require);
                if (preg_match($uriPattern, $uri, $matches)){
                    array_shift($matches);
                    $parameter = $matches;
                    if (strtoupper($route::$routeMap[$key]['method']) == $method || $route::$routeMap[$key]['method'] == 'any'){
                        if (is_string($route::$routeMap[$key]['uses'])){
                            $controllerAction = explode('@', $route::$routeMap[$key]['uses']);
                        }elseif (is_array($route::$routeMap[$key]['uses'])){
                            $controllerAction = $route::$routeMap[$key]['uses'];
                        }elseif (
                            is_object($route::$routeMap[$key]['uses']) 
                            && is_callable($route::$routeMap[$key]['uses']) 
                            && $route::$routeMap[$key]['uses'] instanceof \Closure
                        ){
                            $controllerAction = $route::$routeMap[$key]['uses']();
                        }else {
                            T('路由['.$uri.']的uses配置项错误！');
                        }
                    }else {
                        T('非法请求，路由['.$uri.']的请求方式错误！');
                    }
                    foreach ($paramterName as $nk => $name){
                        $_GET[$name] = $parameter[$nk];
                    }
                    $controller = $route::$routeMap[$key]['namespace'] . $controllerAction[0];
                    break;
                }
            }
        }
        if (empty($controller)){
            T('非法访问，路由['.$uri.']不存在！');
        }
        $action = $controllerAction[1];
        return array($controller ,$action ,$parameter);
    }
    
    /**
     * 路由正则匹配
     * @param string $originUri 路由URI路径
     * @param array $require 路由匹配规则
     * @return array
     */
    private static function generateUriPattern($originUri,Array $require)
    {
        $uriPattern = '';
        $paramterName = array();
        $originUri = str_replace(array('\{', '\}'), array('{', '}'), trim($originUri));
        if (preg_match_all('/{(\w+)}/', $originUri, $matches)) {
            $paramsNum = count($matches[1]);
            for ($i = 0; $i < $paramsNum; $i++) {
                $paramPattern  = (!empty($require)) ? '.+' : (!empty($require[$matches[1][$i]])) ? $require[$matches[1][$i]] : '.+';
                $replacePattern = '(' . $paramPattern . ')';
                $originUri = str_replace($matches[0][$i] , $replacePattern , $originUri);
            }
            $paramterName = $matches[1];
            $uriPattern = '#^' . $originUri . '$#';
        }else{
            $uriPattern = '#^' . $originUri . '$#';
        }
        return array($uriPattern, $paramterName);
    }
}