<?php

namespace NativePHP\Example;

use PHPUnit\Framework\TestCase;

class FileCopierTest extends TestCase
{
    private $fs;

    protected function setUp()
    {
        $this->fs = new \NativePHP\Example\InMemFileSystem();

        $this->generateStubForFSMethod('file_put_contents');
        $this->generateStubForFSMethod('file_get_contents');
        $this->generateStubForFSMethod('file_exists');
        $this->generateStubForFSMethod('unlink');
    }

    public function testShouldCopy() {
        $this->fs->file_put_contents("foobar", "qwerty");

        $fc = new \NativePHP\Example\FileCopier();
        $fc->copy("foobar", "barfoo");

        $this->assertEquals(
            "qwerty",
            $this->fs->file_get_contents("barfoo")
        );
    }

    private function generateStubForFSMethod($methodName)
    {
        $stub = \NativePHP\NativeFunction::getStub(
            $methodName
            , 'NativePHP\Example'
            , array($this->fs, $methodName)
        );

        return $stub;
    }
}