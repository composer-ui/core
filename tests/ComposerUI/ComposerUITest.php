<?php

 namespace ComposerUI\Test;
 
 use ComposerUI\ComposerUI;
 use Composer\Json\JsonFile;

 class ComposerUITest extends TestCase
 {
    protected function createTempDir()
    {
        $directory = realpath(sys_get_temp_dir()) .DIRECTORY_SEPARATOR.'composer-ui';
        $this->ensureDirectoryExistsAndClear($directory);
        return $directory;
    }
    public function testInstall()
    {
        $tempDir = $this->createTempDir();
        $composerJson = $tempDir.DIRECTORY_SEPARATOR."composer.json";
        
        $this->assertTrue(file_put_contents($composerJson,'{}') !== FALSE);
        
        $file = new JsonFile($composerJson);
        $file->write(array(
                    "require" => array(
                            "seld/jsonlint" => "1.0",
                            "monolog/monolog" => "1.0"
                            )));
        $app = new ComposerUI($tempDir);
        
        $this->assertTrue($app->install());
        $this->assertTrue(is_dir($tempDir.DIRECTORY_SEPARATOR.'vendor'));
        $this->assertTrue(is_dir($tempDir.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'seld'));
        $this->assertTrue(is_dir($tempDir.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'seld'.DIRECTORY_SEPARATOR.'jsonlint'));
        $this->assertTrue(is_dir($tempDir.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'monolog'));
        $this->assertTrue(is_dir($tempDir.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'monolog'.DIRECTORY_SEPARATOR.'monolog'));
       
        return $tempDir;
    }
    /**
     * @depends testInstall
     */
    public function testUpdate($tempDir)
    {
        $composerJson = $tempDir.DIRECTORY_SEPARATOR."composer.json";
        $file = new JsonFile($composerJson);
        $json = $file->read();
        $json['require']['nesbot/carbon'] = '1.0'; 
        $file->write($json);
        $app = new ComposerUI($tempDir);
        
        $this->assertTrue($app->update($tempDir));
        $this->assertTrue(is_dir($tempDir.DIRECTORY_SEPARATOR.'vendor'));
        $this->assertTrue(is_dir($tempDir.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'seld'));
        $this->assertTrue(is_dir($tempDir.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'seld'.DIRECTORY_SEPARATOR.'jsonlint'));
        $this->assertTrue(is_dir($tempDir.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'monolog'));
        $this->assertTrue(is_dir($tempDir.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'monolog'.DIRECTORY_SEPARATOR.'monolog'));
        $this->assertTrue(is_dir($tempDir.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'nesbot'));
        $this->assertTrue(is_dir($tempDir.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'nesbot'.DIRECTORY_SEPARATOR.'carbon'));
        
    }
 }
?>