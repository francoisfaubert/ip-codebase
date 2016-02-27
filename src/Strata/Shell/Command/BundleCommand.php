<?php
namespace IP\Code\Strata\Shell\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The bundle command will ensure the application is correctly configured
 * when the project is first setup.
 */
class BundleCommand extends \Strata\Shell\Command\StrataCommandBase {

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('bundle')
            ->setDescription('Bundles the project frontend files.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->startup($input, $output);
        $this->bundleThemesFrontend();
        $this->shutdown();
    }

    private function bundleThemesFrontend()
    {
        foreach ($this->getThemesDirectories() as $themePath) {
            $this->bundleFrontend($themePath);
        }
    }

    private function getThemesDirectories()
    {
        return glob("web/app/themes/*/");
    }

    /**
     * Goes in the directory and bundles the frontend tools
     * @param  string $themePath
     */
    private function bundleFrontend($themePath)
    {
        $this->output->writeln("Creating frontend bundle for <info>$themePath</info>");
        $this->nl();

        if ($this->hasNpm($themePath)) {
            $this->buildNpm($themePath);
        }

        if ($this->hasBower($themePath)) {
            $this->buildBower($themePath);
        }

        if ($this->hasGrunt($themePath)) {
            $this->buildGrunt($themePath);
        }

        if ($this->hasGulp($themePath)) {
            $this->buildGulp($themePath);
        }
    }

    private function hasNpm($themePath)
    {
        return file_exists($themePath . "package.json");
    }

    private function hasBower($themePath)
    {
        return file_exists($themePath . "bower.json");
    }

    private function hasGrunt($themePath)
    {
        return file_exists($themePath . "Gruntfile.js");
    }

    private function hasGulp($themePath)
    {
        return file_exists($themePath . "gulpfile.js");
    }

    private function buildNpm($themePath)
    {
        $this->output->writeln("Updating <info>NPM</info>...");
        $this->nl();
        system("cd $themePath && npm install");
    }

    private function buildBower($themePath)
    {
        $this->output->writeln("Updating <info>Bower</info>...");
        $this->nl();
        system("cd $themePath && bower install --allow-root");
        $this->nl();
    }

    private function buildGrunt($themePath)
    {
        $this->output->writeln("Running <info>Grunt</info> for current environment (".WP_ENV.")...");
        $this->nl();

        $command = WP_ENV === "development" ? "staging" : WP_ENV;
        system("cd $themePath && grunt " . $command);
        $this->nl();
    }

    private function buildGulp($themePath)
    {
        $this->output->writeln("Running <info>Gulp</info> for current environment (".WP_ENV.")...");
        $this->nl();

        $command = WP_ENV === "production" ? "--production" : "";
        system("cd $themePath && gulp " . $command);
        $this->nl();
    }
}
