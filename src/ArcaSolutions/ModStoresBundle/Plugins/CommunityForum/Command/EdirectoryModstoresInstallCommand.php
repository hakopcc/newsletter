<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Command;

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
        $this->simpleLog('[-] Add elastic search index');
        $this->overwriteIndexCreation(['mappings', 'question'], [
            'dynamic'    => 'strict',
            'properties' => [
                'accountId'       => [
                    'type'  => 'integer',
                    'index' => 'not_analyzed',
                ],
                'categoryId'      => [
                    'type'     => 'string',
                    'analyzer' => 'categoryIdAnalyzer',
                ],
                'description'     => [
                    'type'     => 'string',
                    'analyzer' => 'text',
                ],
                'friendlyUrl'     => [
                    'type'  => 'string',
                    'index' => 'not_analyzed',
                ],
                'publicationDate' => [
                    'type'   => 'date',
                    'format' => 'yyyy-MM-dd',
                ],
                'reviewCount'     => [
                    'type' => 'integer',
                ],
                'searchInfo'      => [
                    'type'       => 'object',
                    'properties' => [
                        'keyword' => [
                            'type'     => 'string',
                            'analyzer' => 'text',
                        ],
                    ],
                ],
                'status'          => [
                    'type' => 'boolean',
                ],
                'suggest'         => [
                    'type'       => 'object',
                    'properties' => [
                        'what' => [
                            'type'     => 'completion',
                            'analyzer' => 'simple',
                            'payloads' => true,
                            'context'  => [
                                'module' => [
                                    'type' => 'category',
                                    'path' => '_type',
                                ],
                            ],
                        ],
                    ],
                ],
                'title'           => [
                    'type'   => 'string',
                    'fields' => [
                        'analyzed' => [
                            'type'     => 'string',
                            'analyzer' => 'text',
                        ],
                        'raw'      => [
                            'type'  => 'string',
                            'index' => 'not_analyzed',
                        ],
                    ],
                ],
                'views' => [
                    'type' => 'integer',
                ]
            ],
        ]);

        $this->simpleLog('[-] Copy migrates files');
        $this->copyMigrations($this->bundle->getName());

        $this->simpleLog('[-] Copy SASS files');
        $this->copySass($this->bundle->getName());

        $this->simpleLog('[-] Create directories');
        $this->makeDirectory('web/profile', 'forum');
        $this->makeDirectory('web/sitemgr/content', 'forum');
        $this->makeDirectory('web/sitemgr/content/forum', 'answers');
        $this->makeDirectory('web/sitemgr/content/forum', 'categories');

        $this->simpleLog('[-] Copy Stub files');
        $this->copyStub($this->bundle->getName(), 'profile-answer.php', 'web/profile/forum/answer.php');
        $this->copyStub($this->bundle->getName(), 'profile-question.php', 'web/profile/forum/question.php');
        $this->copyStub($this->bundle->getName(), 'sitemgr-questions.php', 'web/sitemgr/content/forum/index.php');
        $this->copyStub($this->bundle->getName(), 'sitemgr-forum-report.php', 'web/sitemgr/content/forum/report.php');
        $this->copyStub($this->bundle->getName(), 'sitemgr-question-form.php',
            'web/sitemgr/content/forum/question.php');
        $this->copyStub($this->bundle->getName(), 'sitemgr-answers.php', 'web/sitemgr/content/forum/answers/index.php');
        $this->copyStub($this->bundle->getName(), 'sitemgr-answer-form.php',
            'web/sitemgr/content/forum/answers/answer.php');
        $this->copyStub($this->bundle->getName(), 'sitemgr-forum-categories.php',
            'web/sitemgr/content/forum/categories/index.php');
        $this->copyStub($this->bundle->getName(), 'sitemgr-forum-category-form.php',
            'web/sitemgr/content/forum/categories/category.php');

        $this->simpleLog('[-] Copy widget placeholder');
        $this->copyWidgetPlaceholders($this->bundle->getName());
    }

    public function afterCommandWorkflow()
    {
        $lang = !empty($this->input->getOption('lang')) ? strtoupper($this->input->getOption('lang')) : 'EN';

        $this->appendToData('afterCommand', 'elastic:recreate-index');
        $this->appendToData('afterCommand', 'cache:clear');
        $this->appendToData('afterCommand', 'gulp-frontend');
        $this->appendToData('afterCommand', 'edirectory:sync');
        $this->appendToData('afterCommand', 'migrate:domain');
        $this->appendToData('afterCommand', 'assets:install');
        $this->appendToData('afterCommand', [
            'fixture:domain' => [
                'fixtures' => [
                    'src/ArcaSolutions/ModStoresBundle/Plugins/CommunityForum/DataFixtures/ORM/Common',
                    'src/ArcaSolutions/ModStoresBundle/Plugins/CommunityForum/DataFixtures/ORM/'.$lang,
                ],
            ],
        ]);
    }
}
