<?php
namespace IP\Code\Strata\Shell\Command;

use Strata\Strata;
use Strata\Shell\Command\StrataCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DevelopmentCommand extends StrataCommand
{
    private $serverPid = 0;

    protected function configure()
    {
        $this
            ->setName('development')
            ->setDescription('Launches the Strata installation in dev mode.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->startup($input, $output);

        if ($this->isKnownGulp()) {
            $this->executeGulpTask();
        } elseif ($this->isKnownGrunt()) {
            $this->executeGruntTask();
        } else {
            $output->writeln("This project's configuration does not allow the use of the 'development' command.");
        }

        $this->shutdown();
    }

    private function executeGulpTask()
    {
        $this->startDetachedPHPServer();
        $this->startGulpWatch();
    }

    private function executeGruntTask()
    {
        $this->output->writeln('A webserver is now available at <info>http://127.0.0.1:5454/</info>');
        $this->output->writeln('Press <info>CTRL + C</info> to exit');
        $this->nl();
        $this->nl();

        $this->startDetachedPHPServer();
        $this->startGruntWatch();
    }

    private function startDetachedPHPServer()
    {
        $command = 'WP_ENV=development php -S 0.0.0.0:5454 -t web/ > /dev/null & printf "%u" $!';

        if ($this->hasIniFile()) {
            $command .= " -c php.ini";
        }

        $this->serverPid = shell_exec($command);


        $this->output->writeln('Launching development server (#' . $this->serverPid . ')');
        $this->output->writeln('Press <info>CTRL + C</info> to exit');
        $this->nl();
        $this->nl();



    }

    private function startGulpWatch()
    {
        system("cd web/app/themes/sage-master/ && gulp watch");
        $this->closePHPServer();
    }

    private function startGruntWatch()
    {
        system("cd web/app/themes/iprospect-roots-wordpress-template/ && grunt watch");
        $this->closePHPServer();
    }

    private function closePHPServer()
    {
        if ((int)$this->serverPid > 0) {
            $this->output->writeln('Closing the server (#' . $this->serverPid . ')');
            $this->nl();

            system("kill " . $this->serverPid);
        }
        $this->serverPid = 0;
    }

    private function isKnownGulp()
    {
        return file_exists('web/app/themes/sage-master/gulpfile.js');
    }

    private function isKnownGrunt()
    {
        return file_exists('web/app/themes/iprospect-roots-wordpress-template/Gruntfile.js');
    }

    private function hasIniFile()
    {
        return file_exists(Strata::getRootPath() . "php.ini");
    }
}
