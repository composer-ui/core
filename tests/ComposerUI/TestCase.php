<?php

 namespace ComposerUI\Test;

 use Composer\Util\Filesystem;
 
 class TestCase extends \PHPUnit_Framework_TestCase
 {
     protected function ensureDirectoryExistsAndClear($directory)
    {
        $fs = new Filesystem();
        if (is_dir($directory)) {
            $fs->removeDirectory($directory);
        }
        mkdir($directory, 0777, true);
    }
 }
?>