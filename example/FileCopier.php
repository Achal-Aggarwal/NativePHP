<?php

namespace NativePHP\Example;

class FileCopier {
    public function copy($src, $dest, $overwrite = false) {
        if ($overwrite && file_exists($dest)) {
            unlink($dest);
        }

        return file_put_contents($dest, file_get_contents($src));
    }
}