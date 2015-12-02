<?php
namespace IP\Code\Strata\Shell\Command;

use Strata\Shell\Command\StrataCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DevelopmentCommand extends StrataCommand
{
    protected function configure()
    {
        $this
            ->setName('development')
            ->setDescription('Launches the Strata installation in dev mode.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->startup($input, $output);
		
		$this->output->writeln('Launching a development server...');
		$output->writeln('Press <info>CTRL + C</info> to exit');
		$this->nl();
		$this->nl();
        
        if ($this->isKnownGulp()) {
            $this->startDetachedPHPServer();
            $this->startGulpWatch();
		} elseif ($this->isKnownGrunt()) {
            $this->startDetachedPHPServer();
            $this->startGruntWatch();
        } else {     
			$this->output->writeln("This project's configuration does not allow the use of the 'development' command.");
		}
        
        $this->shutdown();
    }
    
    private function startPHPServer()
    {
        system("WP_ENV=development php -S 0.0.0.0:5454 -t web/");
    }
	
    private function startDetachedPHPServer()
    {
        system("WP_ENV=development php -S 0.0.0.0:5454 -t web/ > /dev/null &");
    }
    
    private function startGulpWatch()
    {
        system("cd web/app/themes/sage-master/ && gulp watch");
    }
	
    private function startGruntWatch()
    {
        system("cd web/app/themes/iprospect-roots-wordpress-template/ && grunt watch");
    }
    
    private function isKnownGulp()
    {
        return file_exists('web/app/themes/sage-master/gulpfile.js');
    }
    
    private function isKnownGrunt()
    {
        return file_exists('web/app/themes/iprospect-roots-wordpress-template/Gruntfile.js');
    }
}
