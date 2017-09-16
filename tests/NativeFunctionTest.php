<?php

namespace NativePHP {

    use PHPUnit\Framework\TestCase;

    class NativeFunctionTest extends TestCase
    {
        public $checkDateStub;

        protected function setUp()
        {
            $this->checkDateStub = \NativePHP\NativeFunction::getStub(
                'checkdate', 'SomeNamespace');

            $this->checkDateStub->workAs(array(new Substitutes,'custom'));
        }

        public function testShouldRunStubbedMethodInsteadOfActualOne()
        {
            $this->assertEquals(
                "Month:7, Day:30, Year:2014\n",
                (new \SomeNamespace\SomeClass)->SomeClassMethod()
            );

            $this->assertEquals(
                "Month:7, Day:30, Year:2014\n",
                \SomeNamespace\SomeMethod()
            );
        }

        public function testShouldRunStubbedMethodInLimitedMethodScopeOnly()
        {
            $this->checkDateStub->inOnly(null, 'SomeMethod');

            $this->assertEquals(
                true,
                (new \SomeNamespace\SomeClass)->SomeClassMethod()
            );

            $this->assertEquals(
                "Month:7, Day:30, Year:2014\n",
                \SomeNamespace\SomeMethod()
            );
        }

        public function testShouldRunStubbedMethodInLimitedClassScopeOnly()
        {
            $this->checkDateStub->inOnly('SomeClass', null);

            $this->assertEquals(
                "Month:7, Day:30, Year:2014\n",
                (new \SomeNamespace\SomeClass)->SomeClassMethod()
            );

            $this->assertEquals(
                "Month:7, Day:30, Year:2014\n",
                (new \SomeNamespace\SomeClass)->SomeMethod()
            );

            $this->assertEquals(
                true,
                \SomeNamespace\SomeMethod()
            );
        }

        public function testShouldRunStubbedMethodInLimitedClassMethodScopeOnly()
        {
            $this->checkDateStub->inOnly('SomeClass', 'SomeClassMethod');

            $this->assertEquals(
                "Month:7, Day:30, Year:2014\n",
                (new \SomeNamespace\SomeClass)->SomeClassMethod()
            );

            $this->assertEquals(
                true,
                (new \SomeNamespace\SomeClass)->SomeMethod()
            );

            $this->assertEquals(
                true,
                \SomeNamespace\SomeMethod()
            );
        }

        public function testShouldAcceptClassStaticFunctionAsSubstitute()
        {
            $this->checkDateStub->workAs(array('\NativePHP\Substitutes','staticCustom'));

            $this->assertEquals(
                "Month:7, Day:30, Year:2014\n",
                \SomeNamespace\SomeMethod()
            );
        }

        public function testShouldAcceptAnonymousFunctionAsSubstitute()
        {
            $this->checkDateStub->workAs(function ($m, $d, $y) {
                return "Month:$m, Day:$d, Year:$y\n";
            });

            $this->assertEquals(
                "Month:7, Day:30, Year:2014\n",
                \SomeNamespace\SomeMethod()
            );
        }

        protected function tearDown()
        {
            $this->checkDateStub->clearScope();
        }
    }

    class Substitutes {

        public static function staticCustom($m, $d, $y)
        {
            return "Month:$m, Day:$d, Year:$y\n";
        }

        public function custom($m, $d, $y)
        {
            return "Month:$m, Day:$d, Year:$y\n";
        }
    }
}

namespace SomeNamespace{
    class SomeClass{
        public function SomeClassMethod(){
            return checkdate(7,30,2014);
        }

        public function SomeMethod(){
            return checkdate(7,30,2014);
        }
    }

    function SomeMethod(){
        return checkdate(7,30,2014);
    }
}