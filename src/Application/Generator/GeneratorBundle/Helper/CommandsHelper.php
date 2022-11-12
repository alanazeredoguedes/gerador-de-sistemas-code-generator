<?php

namespace App\Application\Generator\GeneratorBundle\Helper;

use Symfony\Component\Process\Process;

class CommandsHelper
{

    public function __construct(
        protected string $workingDirectory,
        protected string $projectDirectory,
        protected string $projectName,
    )
    {}

    protected $listCommands = [
        //'./run bin/console doctrine:schema:update --force'
        //'composer install',
        //'bin/console lexik:jwt:generate-keypair',
        //'chmod 777 /var/ -R',
        //'bin/console doctrine:database:create',
        //'bin/console doctrine:schema:update --force',
        //'bin/console security:create-admin admin admin@email.com admin',
        //'bin/console security:create-user user user@email.com user',
        //'bin/console assets:install --symlink',
        //'bin/console cache:clear',
    ];

    public function runCommands(): bool
    {
         foreach ($this->listCommands as $command){

             $command = explode(' ', $command);
             //dd($command);
             $process = new Process($command);
             $process->setWorkingDirectory($this->workingDirectory . $this->projectName);
             $process->run();
         }

     return true;
    }


    /*public function startContainer(): bool
    {
        dd($this->workingDirectory . $this->projectName);
        $command = ['./start.sh'  ];
        $process = new Process($command);
        $process->setWorkingDirectory($this->workingDirectory . $this->projectName . '/');
        $process->run();

        return $process->isSuccessful();
    }*/

    public function installDependencies(): bool
    {
        $command = ['composer', 'install' ];
        $process = new Process($command);
        $process->setWorkingDirectory($this->workingDirectory . $this->projectName);
        $process->run();

        return $process->isSuccessful();
    }


    public function recursiveRemoveDir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                        $this->recursiveRemoveDir($dir. DIRECTORY_SEPARATOR .$object);
                    else
                        unlink($dir. DIRECTORY_SEPARATOR .$object);
                }
            }
            rmdir($dir);
        }
    }


}