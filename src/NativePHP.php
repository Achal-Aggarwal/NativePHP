<?php
namespace NativePHP;
$_NATIVEPHP = array();
class NativePHP
{
	protected $namespace = '\\';
	protected $class = null;
	protected $function = null;
	protected $mock = null;
	protected $callback = null;
	private function __construct($funct, $namespace)
	{
		$this->mock = $funct;
		$this->namespace = $namespace;
		$this->callback = function(){
			return null;
		};
	}

	public static function getMock($funct, $namespace)
	{
		global $_NATIVEPHP;

		if (!array_key_exists($funct, $_NATIVEPHP))
		{
			$object = new NativePHP($funct, $namespace);
			$_NATIVEPHP[$funct] = array();
			$_NATIVEPHP[$funct][$namespace] = $object;
			$object->generate();
		}
		elseif (!array_key_exists($namespace, $_NATIVEPHP[$funct]))
		{
			$object = new NativePHP($funct, $namespace);
			$_NATIVEPHP[$funct][$namespace] = $object;
			$object->generate();
		}
		else
		{
			$object = $_NATIVEPHP[$funct][$namespace];
		}

		return $object;
	}

	public function setNamespace($namespace)
	{
		$this->namespace = $namespace;
	}

	public function getCallback($trace)
	{
		$caller = (count($trace) >= 2) ? $trace[1] : $trace[0];

		$callingFunction = $caller['function'];
		$callingClass = isset($caller['class']) ? isset($caller['class']) : null;

		$classSatisfied = false;
		if ($this->class != null && $callingClass != null
			&& "$this->namespace\\$this->class" == $callingClass)
		{
			$classSatisfied = true;
		}
		elseif ($this->class == null)
		{
			$classSatisfied = true;
		}

		$functionSatisfied = false;
		if ($this->function != null && $callingFunction == $this->function)
		{
			$functionSatisfied = true;
		}
		elseif ($this->function != null && $callingClass == null && $callingFunction != null
			&& "$this->namespace\\$this->function" == $callingFunction)
		{
			$functionSatisfied = true;
		}
		elseif ($this->function == null)
		{
			$functionSatisfied = true;
		}

		if ($classSatisfied && $functionSatisfied)
		{
			return $this->callback;
		}

		return "\\$this->mock";
	}

    public function workAs($funct)
    {
		$this->callback = $funct;
    }

	public function inOnly($class, $function = null)
	{
		$this->class = $class;
		$this->function = $function;
	}

	private function generate()
	{
		$ns = $this->namespace;
		$mock = $this->mock;

		$str = '
		namespace ' . $ns . '{
			function ' . $mock . '(){
				global $_NATIVEPHP;
				$object = $_NATIVEPHP["' . $mock . '"]["' . $ns . '"];
				$callback = $object->getCallback(debug_backtrace());
				return call_user_func_array($callback, func_get_args());
			}
		}';

		//var_dump($str);

		eval($str);
	}
}