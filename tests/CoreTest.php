<?php

 namespace ComposerUI\Tests;
 
 use ComposerUI\Core as ComposerUI;
 use Composer\Json\JsonFile;

 class CoreTest extends TestCase
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
                            ),
                    "require-dev" => array(
                            "phpunit/phpunit" => "3.7.*"
                    )));
        $app = new ComposerUI($tempDir);
        $vendorDir = $tempDir.DIRECTORY_SEPARATOR.'vendor';
        
        $this->assertTrue($app->install());
        $this->assertFileExists($vendorDir);
        $this->assertFileExists($vendorDir.DIRECTORY_SEPARATOR.'seld'.DIRECTORY_SEPARATOR.'jsonlint');
        $this->assertFileExists($vendorDir.DIRECTORY_SEPARATOR.'monolog'.DIRECTORY_SEPARATOR.'monolog');
        $this->assertFileExists($vendorDir.DIRECTORY_SEPARATOR.'phpunit');
       
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
        $this->assertTrue(is_dir($tempDir.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'seld'.DIRECTORY_SEPARATOR.'jsonlint'));
        $this->assertTrue(is_dir($tempDir.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'monolog'.DIRECTORY_SEPARATOR.'monolog'));
        $this->assertTrue(is_dir($tempDir.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'nesbot'.DIRECTORY_SEPARATOR.'carbon'));
        
    }
    
    public function testCreateProject()
    {
        $tempDir = $this->createTempDir();
        $app = new ComposerUI($tempDir);
        
        $this->assertTrue($app->createProject('seld/jsonlint','1.0'));
        $this->assertFileExists($tempDir.DIRECTORY_SEPARATOR.'jsonlint');
        
        $this->assertTrue($app->createProject('seld/jsonlint','1.0','testing'));
        $this->assertFileExists($tempDir.DIRECTORY_SEPARATOR.'testing');
    }
    public function testRequire()
    {
        $tempDir = $this->createTempDir();
        $app = new ComposerUI($tempDir);
        $vendorDir = $tempDir.DIRECTORY_SEPARATOR.'vendor';
        
        $this->assertTrue($app->require(array('seld/jsonlint'=>'1.0','nesbot/carbon'=>'1.0')));
        $this->assertFileExists($tempDir.DIRECTORY_SEPARATOR.'composer.json');
        $this->assertFileExists($vendorDir);
        $this->assertFileExists($vendorDir.DIRECTORY_SEPARATOR.'nesbot'.DIRECTORY_SEPARATOR.'carbon');
        $this->assertFileExists($vendorDir.DIRECTORY_SEPARATOR.'seld'.DIRECTORY_SEPARATOR.'jsonlint');
    }
    /**
     * @depends testInstall
     */
    public function testRequireAfterInstall($tempDir)
    {
        $app = new ComposerUI($tempDir);
        $vendorDir = $tempDir.DIRECTORY_SEPARATOR.'vendor';
        
        $this->assertTrue($app->require(array('seld/jsonlint'=>'1.0','nesbot/carbon'=>'1.0')));
        $this->assertFileExists($tempDir.DIRECTORY_SEPARATOR.'composer.json');
        $this->assertFileExists($vendorDir);
        $this->assertFileExists($vendorDir.DIRECTORY_SEPARATOR.'nesbot'.DIRECTORY_SEPARATOR.'carbon');
        $this->assertFileExists($vendorDir.DIRECTORY_SEPARATOR.'seld'.DIRECTORY_SEPARATOR.'jsonlint');
    }
    public function testInit()
    {
        echo "\n\ntesting init\n\n";
        $tempDir = $this->createTempDir();
        $app = new ComposerUI($tempDir);
        $options = array(
            'name'=>'test/test',
            'author' => $app->makeAuthorString('TestAuthor','author@example.com'),
            'description' => 'a test package',
            'homepage'=>'http://example.com',
            'require'=>array(
                $app->createFullPackageName('seld/jsonlint','1.0'),
                $app->createFullPackageName('nesbot/carbon','1.0')
            ),
            'require-dev'=>array(
                $app->createFullPackageName('phpunit/phpunit','3.7.*')
            ),
            'stability'=>'dev',
            'license'=>'MIT'
        );
        
        $this->assertTrue($app->init($options));
        $this->assertFileExists($tempDir.DIRECTORY_SEPARATOR.'composer.json');
        
        $json = JsonFile::parseJson(file_get_contents($tempDir.DIRECTORY_SEPARATOR.'composer.json'));
        
        $this->assertArrayHasKey('name',$json);
        $this->assertArrayHasKey('require',$json);
        $this->assertArrayHasKey('authors',$json);
        $this->assertArrayHasKey('description',$json);
        $this->assertArrayHasKey('homepage',$json);
    }
    public function testRemove()
    {
        $tempDir = $this->testInstall();
        $app = new ComposerUI($tempDir);
        $vendorDir = $tempDir.DIRECTORY_SEPARATOR.'vendor';
        
        $this->assertTrue($app->remove(array("monolog/monolog")));
        $this->assertFileExists($vendorDir);
        $this->assertFileExists($vendorDir.DIRECTORY_SEPARATOR.'seld'.DIRECTORY_SEPARATOR.'jsonlint');
        $this->assertFileNotExists($vendorDir.DIRECTORY_SEPARATOR.'monolog'.DIRECTORY_SEPARATOR.'monolog');
    }
    public function testRemoveDev()
    {
        $tempDir = $this->testInstall();
        $app = new ComposerUI($tempDir);
        $vendorDir = $tempDir.DIRECTORY_SEPARATOR.'vendor';
        
        $this->assertTrue($app->remove(array("phpunit/phpunit"),true));
        $this->assertFileExists($vendorDir);
        $this->assertFileExists($vendorDir.DIRECTORY_SEPARATOR.'seld'.DIRECTORY_SEPARATOR.'jsonlint');
        $this->assertFileNotExists($vendorDir.DIRECTORY_SEPARATOR.'phpunit'.DIRECTORY_SEPARATOR.'phpunit');
    }
 }
?>