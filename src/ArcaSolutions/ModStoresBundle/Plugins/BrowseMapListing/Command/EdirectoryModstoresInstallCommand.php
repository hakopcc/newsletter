<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\BrowseMapListing\Command;

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
        $this->simpleLog('[-] Copy migrates files');
        $this->copyMigrations($this->bundle->getName());

        $this->simpleLog('[-] Copy SASS files');
        $this->copySass($this->bundle->getName());

        $this->simpleLog('[-] Create directories');
        $this->makeDirectory('web/assets/js/modstores', 'jvectormap');

        $this->simpleLog('[-] Copy widget placeholder');
        $this->copyWidgetPlaceholders($this->bundle->getName());
    }

    public function afterCommandWorkflow()
    {
        $this->appendToData('afterCommand', 'cache:clear');
        $this->appendToData('afterCommand', 'gulp-frontend');
        $this->appendToData('afterCommand', 'assets:install');
        $this->appendToData('afterCommand', 'migrate:domain');
        $this->appendToData('afterCommand', [
            'fixture:domain' => [
                'fixtures' => [
                    'src/ArcaSolutions/ModStoresBundle/Plugins/BrowseMapListing/DataFixtures/ORM/Common',
                ],
            ],
        ]);
    }
}
