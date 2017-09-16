<?php

namespace NativePHP;

class NativeFunction
{
    private static $_object_map = array();

    private $inNamespace;
    private $functionToMock;
    private $substituteFunction;

    private $inClasses;
    private $inFunctions;

    private function __construct($functionToMock = null, $inNamespace = '\\')
    {
        $this->functionToMock = $functionToMock;
        $this->inNamespace = $inNamespace;

        $this->substituteFunction = function() {};

        $this->clearScope();
    }

    public static function getStub($functionToMock, $inNamespace)
    {
        if (!array_key_exists($functionToMock, self::$_object_map))
        {
            self::$_object_map[$functionToMock] = array();
        }

        if (!array_key_exists($inNamespace, self::$_object_map[$functionToMock]))
        {
            $object = new NativeFunction($functionToMock, $inNamespace);
            self::$_object_map[$functionToMock][$inNamespace] = $object;
            $object->generate();
        }
        else
        {
            $object = self::$_object_map[$functionToMock][$inNamespace];
        }

        return $object;
    }

    public function workAs($substituteFunction)
    {
        if (is_user_function_callable($substituteFunction)) {
            $this->substituteFunction = $substituteFunction;
        } else {
            throw new \Exception("Substitution function is not callable.");
        }
    }

    public function inOnlyFunction($inFunction)
    {
        $namespacePrefix = "$this->inNamespace\\";
        $this->inFunctions[$namespacePrefix . $inFunction] = true;
    }

    public function inOnlyClass($inClass, $inMethod = null)
    {
        $namespacePrefix = "$this->inNamespace\\";

        if (!array_key_exists($namespacePrefix . $inClass, $this->inClasses)) {
            $this->inClasses[$namespacePrefix . $inClass] = array();
        }

        if (!is_null($inMethod)) {
            $this->inClasses[$namespacePrefix . $inClass][$inMethod] = true;
        }
    }

    public function clearScope() {
        $this->inClasses = $this->inFunctions = array();
    }

    private function getCaller($callTrace) {
        $caller = (count($callTrace) >= 2) ? $callTrace[1] : $callTrace[0];

        return (object) array(
            'callingFunction' => $caller['function'],
            'callingClass' => isset($caller['class']) ? $caller['class'] : null
        );
    }

    private function isClassScopeSatisfied($caller)
    {
        if ($caller->callingClass == null) {
            return true;
        }

        return array_key_exists($caller->callingClass, $this->inClasses);
    }

    private function isFunctionScopeSatisfied($caller)
    {
        if ($caller->callingClass != null
            && $this->isClassScopeSatisfied($caller))
        {
            return count($this->inClasses[$caller->callingClass]) == 0
            || array_key_exists($caller->callingFunction, $this->inClasses[$caller->callingClass]);
        }

        return array_key_exists($caller->callingFunction, $this->inFunctions);
    }

    public function getSubstitutedFunction($callTrace)
    {
        $caller = $this->getCaller($callTrace);

        if (count($this->inClasses) + count($this->inFunctions) == 0
            || $this->isClassScopeSatisfied($caller)
            && $this->isFunctionScopeSatisfied($caller))
        {
            return $this->substituteFunction;
        }

        return "\\$this->functionToMock";
    }

    private function generate()
    {
        $ns = $this->inNamespace;
        $mock = $this->functionToMock;

        $str = '
        namespace ' . $ns . '{
            function ' . $mock . '(){
                $object = \NativePHP\NativeFunction::getStub("' . $mock . '","' . $ns . '");
                $callback = $object->getSubstitutedFunction(debug_backtrace());
                return \NativePHP\call_user_function_param($callback, func_get_args());
            }
        }';

        eval($str);
    }
}