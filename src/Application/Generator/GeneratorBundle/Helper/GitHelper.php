<?php

namespace App\Application\Generator\GeneratorBundle\Helper;

use Symfony\Component\Process\Process;

class GitHelper
{
    protected string $kernelDirectory;
    protected $projectsDir;
    protected $baseProjectDir;
    protected $projectDir;
    //protected $gitBaseRepository = 'https://github.com/alanazeredoguedes/symfonySonata7.2.git';
    protected $gitBaseRepository = 'https://github.com/alanazeredoguedes/symfony-jwtauth-php8.git';

    /**
     * @param string $kernelDirectory
     */
    public function __construct(string $kernelDirectory, string $projectDir)
    {
        $this->kernelDirectory = $kernelDirectory;
        $this->projectsDir = $kernelDirectory . '/public/projects/';
        $this->baseProjectDir = $kernelDirectory . '/public/projects/symfony-jwtauth-php8';
        $this->projectDir = $projectDir;
    }

    public function cloneBaseRepository(): bool
    {
        $this->removeDir($this->baseProjectDir);
        $this->removeDir($this->projectDir);

        $command = ['git', 'clone', $this->gitBaseRepository];
        $process = new Process($command);
        $process->setWorkingDirectory($this->projectsDir);
        $process->run();

        if($process->isSuccessful())
            $this->renameDirectory($this->baseProjectDir, $this->projectDir);

        return $process->isSuccessful();
    }

    public function renameDirectory(string $directory, string $newDirectory): bool
    {
        $command = ['mv', $directory, $newDirectory];
        $process = new Process($command);
        $process->setWorkingDirectory($this->projectsDir);
        $process->run();

        return $process->isSuccessful();
    }


    public function removeDir($directory): bool
    {
        $command = ['rm', '-rf', $directory];
        $process = new Process($command);
        $process->setWorkingDirectory($this->projectsDir);
        $process->run();

        return $process->isSuccessful();
    }


}