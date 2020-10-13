<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\DropdownMenu\Command;

use ArcaSolutions\ModStoresBundle\Command\AbstractEdirectoryModstoresInstallCommand;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle;
use ArcaSolutions\ModStoresBundle\Traits\WorkflowMethodsTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EdirectoryModstoresInstallCommand extends AbstractEdirectoryModstoresInstallCommand
{
    use WorkflowMethodsTrait;

    /**
     * EdirectoryModstoresInstallCommand constructor.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param AbstractPluginBundle $bundle
     */
    public function __construct(InputInterface $input, OutputInterface $output, AbstractPluginBundle $bundle)
    {
        parent::__construct($input, $output, $bundle);

        // enable workflow
        $this->hasWorkflow = true;
        $this->hasAfterWorkflow = true;
    }

    public function commandWorkflow()
    {
        // run plugin install workflow
        $this->simpleLog('[-] Copy migrates files');
        $this->copyMigrations($this->bundle->getName());

        $this->simpleLog('[-] Copy SASS files');
        $this->copySass($this->bundle->getName());

        $this->simpleLog('[-] Create directories');
        $this->makeDirectory('web/sitemgr/design', 'site-navigation');

        $this->simpleLog('[-] Copy Stub files');
        $this->copyStub($this->bundle->getName(), 'site-navigation.php',
            'web/sitemgr/design/site-navigation/index.php');
    }

    public function afterCommandWorkflow()
    {
        $this->appendToData('afterCommand', 'assets:install');
        $this->appendToData('afterCommand', 'gulp-frontend');
        $this->appendToData('afterCommand', 'migrate:domain');
    }
}
