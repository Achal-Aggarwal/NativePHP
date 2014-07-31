<?php
namespace Another
{
    include_once 'src/NativePHP.php';
	$n = \NativePHP\NativePHP::getMock('checkdate', 'One');
	class A{
		public function custom($m, $d, $y)
		{
			return "Month:$m, Day:$d, Year:$y\n";
		}
	}
	$m = function($m, $d, $y){
		return "Month:$m, Day:$d, Year:$y\n";
	};
	$c = new A;
	$n->workAs(array($c,'custom'));
	$n->inOnly(null, 'hmm');
}
namespace One{
	class Om{

		public function naa(){
			echo checkdate(7,30,2014);
		}
	}
	function hmm(){
		echo checkdate(7,30,2014);
	}
	hmm();
	$o = new Om;
	$o->naa();
}