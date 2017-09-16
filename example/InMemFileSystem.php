<?php

namespace NativePHP\Example;

class InMemFileSystem {
    private $files = array();

    public function file_put_contents($filename, $data) {
        $this->files[$filename] = $data;

        return strlen($data);
    }

    public function file_get_contents($filename) {
        return $this->files[$filename];
    }

    public function file_exists($filename) {
        return array_key_exists($filename, $this->files);
    }

    public function unlink($filename) {
        unset($this->files[$filename]);

        return true;
    }
}
