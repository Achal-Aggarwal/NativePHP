<?php

namespace NativePHP;

/**
 * Calls a method, function or closure. Parameters are supplied by their names instead of their position.
 * @param $call_arg like $callback in call_user_func_array()
 * @param array $params An array with the parameters
 * @return result of the method, function or closure
 * @throws \Exception when wrong arguments are given or required parameters are not given.
 */
function call_user_function_param($call_arg, array $params)
{
    list($Func, $Object) = get_callable_user_function($call_arg);

    if($Func instanceof \ReflectionFunction) return $Func->invokeArgs($params);
    if($Func->isStatic()) return $Func->invokeArgs(null, $params);
    else return $Func->invokeArgs($Object, $params);
}

/**
 * Calls a method, function or closure. Parameters are supplied by their names instead of their position.
 * @param $call_arg like $callback in call_user_func_array()
 * Case1: {object, method}
 * Case2: {class, function}
 * Case3: "class::function"
 * Case4: "function"
 * Case5: closure
 * @return array of \ReflectionMethod|\ReflectionFunction and Object callable function
 * @throws \Exception when wrong arguments are given or required parameters are not given.
 */
function get_callable_user_function($call_arg)
{
    $Func = null;
    $Method = null;
    $Object = null;
    $Class = null;
    // The cases. f means function name
    // Case1: f({object, method}, params)
    // Case2: f({class, function}, params)
    if(is_array($call_arg) && count($call_arg) == 2) {
        if(is_object($call_arg[0]))
        {
            $Object = $call_arg[0];
            $Class = get_class($Object);
        }
        else if(is_string($call_arg[0]))
        {
            $Class = $call_arg[0];
        }
        if(is_string($call_arg[1]))
        {
            $Method = $call_arg[1];
        }
    }
    // Case3: f("class::function", params)
    else if(is_string($call_arg) && strpos($call_arg, "::") !== FALSE) {
        list($Class, $Method) = explode("::", $call_arg);
    }
    // Case4: f("function", params)
    else if(is_string($call_arg) && strpos($call_arg, "::") === FALSE) {
        $Method = $call_arg;
    }
    // Case5: f(closure, params)
    else if(is_object($call_arg) && $call_arg instanceof \Closure) {
        $Method = $call_arg;
    }
    else {
        throw new \Exception("Case not allowed! Invalid Data supplied!");
    }

    $Func = $Class
        ? new \ReflectionMethod($Class, $Method)
        : new \ReflectionFunction($Method);

    return array($Func, $Object);
}

/**
 * Calls a method, function or closure. Parameters are supplied by their names instead of their position.
 * @param $call_arg like $callback in call_user_func_array()
 * @return boolean
 */
function is_user_function_callable($call_arg)
{
    try {
        get_callable_user_function($call_arg);
    } catch (\Exception $e) {
        return false;
    }

    return true;
}