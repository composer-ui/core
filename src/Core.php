<?php

namespace ComposerUI;

use Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Composer\Console\Application as Composer;

class Core
{
    private $workingDirectory;
    private $verbosity;
    private $output;
    const VERBOSITY_NORMAL = 1;
    const VERBOSITY_MORE = 2;
    const VERBOSITY_DEBUG = 3;
    
    public function __construct($workingDirectory,  OutputInterface $output = null ,$verbosity = self::VERBOSITY_NORMAL)
    {
        $this->setWorkingDirectory($workingDirectory);
        $this->setVerbosity($verbosity);
        $this->setOutputInterface($output);
    }

    public function setWorkingDirectory($workingDirectory)
    {
        return $this->workingDirectory = $workingDirectory;
    }
    
    public function getWorkingDirectory()
    {
        return $this->workingDirectory;
    }
    public function setOutputInterface(OutputInterface $output = null)
    {
        return $this->output = $output;
    }
    
    public function getOutputInterface()
    {
        return $this->output;
    }
    public function getVerbosity()
    {
        return $this->verbosity;
    }
    
    public function setVerbosity($verbosity)
    {
        if(in_array($verbosity,array(self::VERBOSITY_NORMAL,self::VERBOSITY_MORE,self::VERBOSITY_DEBUG)))
            return $this->verbosity = $verbosity;
        else
            throw new \InvalidArgumentException;
    }
    protected function parseVerbosity()
    {
        $output = '-';
        for($i = 1 ; $i<=$this->verbosity; $i++)
            $output .= 'v';
        return $output;
    }
    private function simpleCommand($command)
    {
        $input = $this->makeInput($command);
        return $this->runComposer($input);
    }
    public function createFullPackageName($package,$version)
    {
        return $package.':'.$version;
    }
    public function makeAuthorString($name,$email)
    {
        return sprintf("%s <%s>",$name,$email);
    }
    public function __call($method,$arguments)
    {
        if(in_array($method,array('install','update','dump-autoload')))
                return $this->simpleCommand ($method);
        else if ($method == 'require')
            return $this->req($arguments[0]);
        else 
            throw new \BadMethodCallException;
    }
    
    protected function req(array $packages)
    {
        $inputArray = array();
        foreach($packages as $package => $version)
            $inputArray['packages'][] = $this->createFullPackageName ($package, $version);
        $input = $this->makeInput('require',$inputArray);
        return $this->runComposer($input);
    }
    public function createProject($package,$version,$directory = null)
    {
        $input = $this->makeInput('create-project',array(
            'package'=>$package,
            'version'=>$version,
            'directory'=>$directory
                ));
        return $this->runComposer($input);
    }
    public function init(array $inputOptions)
    {
        $options = array();
        foreach($inputOptions as $key => $value)
        {  
            if($key[0] !== '-') 
                $key = '--'.$key;
            $options[$key] = $value;
        }
        $input = $this->makeInput('init',$options);
        return $this->runComposer($input);
    }
    public function remove(array $packages,$isDev = false)
    {
        $arguments = array('packages'=>$packages);
        if($isDev) 
            $arguments['--dev'] = null;
        $input = $this->makeInput('remove',$arguments);
        return $this->runComposer($input);
    }
    private function makeInput($command,$arguments = array())
    {
        $input = array(
            'command' => $command,
            $this->parseVerbosity() => null, 
            "--working-dir" => $this->getWorkingDirectory(),
            "--no-interaction" => null);
        return new ArrayInput(array_merge($input, $arguments));
    }

    private function runComposer(InputInterface $input)
    {
        $app = new Composer();
        $app->setAutoExit(false);
        return $app->run($input,$this->getOutputInterface()) == 0;
    }

}

?>