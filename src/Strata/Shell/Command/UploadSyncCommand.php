<?php
namespace IP\Code\Strata\Shell\Command;

use Strata\Shell\Command\StrataCommandBase;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use InvalidArgumentException;

use Strata\Strata;

class UploadSyncCommand extends StrataCommandBase
{
    private $project;
    private $direction;

    // This command is coded to assume we want to
    // perform actions on the bamboo server. Operations
    // done on other servers must be done manually.
    private $username = "iprospect";
    private $host = "host.iprospect.work";

    protected function configure()
    {
        $this
            ->setName('uploads-sync')
            ->setDescription('Syncs the local ~/upload directory with a remote versio of the directory.')
            ->addArgument(
                'direction',
                InputArgument::REQUIRED,
                'One of the following: pull, push.'
            )
            ->addArgument(
                'environment',
                InputArgument::REQUIRED,
                'One of the following: projectname-dev, projectname-staging.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->startup($input, $output);

        $this->project = $input->getArgument('environment');

        switch ($input->getArgument('direction')) {
            case "pull":
                $this->pullData();
                break;

            case "push":
                $this->pushData();
                break;

            default : throw new InvalidArgumentException("This is not a valid argument for this command.");
        }

        $this->shutdown();
    }

    protected function pullData()
    {
        $commandPattern = "rsync -av %s@%s:/home/iprospect/www/%s/web/app/uploads/* %s/web/app/uploads/";
        $command = sprintf($commandPattern,
            $this->username,
            $this->host,
            $this->project,
            Strata::getRootPath()
        );

        system($command);
    }

    protected function pushData()
    {
        $commandPattern = "rsync -av %s/web/app/uploads/ %s@%s:/home/iprospect/www/%s/web/app/uploads/";
        $command = sprintf($commandPattern,
            Strata::getRootPath(),
            $this->username,
            $this->host,
            $this->project
        );

        system($command);
    }

}
