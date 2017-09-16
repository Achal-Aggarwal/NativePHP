<?php

namespace NativePHP;

class NativeFunction
{
    private static $_object_map = array();

    private $inNamespace;
    private $functionToMock;
    private $substituteFunction;

    private $inClass = null;
    private $inFunction = null;

    private function __construct($functionToMock = null, $inNamespace = '\\')
    {
        $this->functionToMock = $functionToMock;
        $this->inNamespace = $inNamespace;

        $this->substituteFunction = function() {
            return null;
        };
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

    public function inOnly($inClass, $inFunction = null)
    {
        //@todo make them array
        $this->inClass = $inClass;
        $this->inFunction = $inFunction;
    }

    public function clearScope() {
        $this->inClass = $this->inFunction = null;
    }

    private function getCaller($callTrace) {
        $caller = (count($callTrace) >= 2) ? $callTrace[1] : $callTrace[0];

        return (object) array(
            'callingFunction' => $caller['function'],
            'callingClass' => isset($caller['class']) ? $caller['class'] : null
        );
    }

    private function isClassSatisfied($caller)
    {
        $classSatisfied = $this->inClass == null;

        if (!$classSatisfied
            && $this->inClass != null
            && $caller->callingClass != null
            && "$this->inNamespace\\$this->inClass" == $caller->callingClass) {
            $classSatisfied = true;
        }

        return $classSatisfied;
    }

    private function isFunctionSatisfied($caller)
    {
        $functionSatisfied = $this->inFunction == null;

        if (!$functionSatisfied && $this->inFunction != null) {
            if ($this->inClass != null && $this->inFunction == $caller->callingFunction) {
                $functionSatisfied = true;
            } else if ($this->inClass == null
                && "$this->inNamespace\\$this->inFunction" == $caller->callingFunction) {
                $functionSatisfied = true;
            }
        }

        return $functionSatisfied;
    }

    public function getSubstitutedFunction($callTrace)
    {
        $caller = $this->getCaller($callTrace);

        if ($this->isClassSatisfied($caller) && $this->isFunctionSatisfied($caller))
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