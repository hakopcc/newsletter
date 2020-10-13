<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\AdditionalLocationsListing\Command;

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

    protected function commandWorkflow()
    {
        $this->simpleLog('[-] Add elastic search index');
        $this->overwriteIndexCreation(['mappings', 'listing', 'properties', 'extraLocationId'],
            ['type' => 'string', 'analyzer' => 'locationIdAnalyzer']);
        $this->overwriteIndexCreation(['mappings', 'listing', 'properties', 'mainLocationId'],
            ['type' => 'string', 'analyzer' => 'locationIdAnalyzer']);

        $this->simpleLog('[-] Copy migrates files');
        $this->copyMigrations($this->bundle->getName());

        $this->simpleLog('[-] Copy SASS files');
        $this->copySass($this->bundle->getName());
    }

    protected function afterCommandWorkflow()
    {
        $this->appendToData('afterCommand', 'assets:install');
        $this->appendToData('afterCommand', 'gulp-frontend');
        $this->appendToData('afterCommand', 'edirectory:sync');
        $this->appendToData('afterCommand', 'migrate:domain');
        $this->appendToData('afterCommand', [
            'fixture:domain' => [
                'fixtures' => [
                    'src/ArcaSolutions/ModStoresBundle/Plugins/AdditionalLocationsListing/DataFixtures/ORM/Common',
                ],
            ],
        ]);
    }
}
