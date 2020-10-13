<?php


namespace Application\Migrations;

use ArcaSolutions\WysiwygBundle\Entity\Page;
use ArcaSolutions\WysiwygBundle\Entity\PageType;
use ArcaSolutions\WysiwygBundle\Entity\PageWidget;
use ArcaSolutions\WysiwygBundle\Entity\Widget;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Migrations\Configuration\YamlConfiguration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Query\Expr\GroupBy;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Acl\Exception\Exception;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class VersionCommunityForum7 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @inheritDoc
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @inheritDoc
     */
    public function getContainer()
    {
        return $this->container;
    }

    private function ensureForumPagesAndWidgets(){
        $twoColumnsDefArray = [
            'title' => 'Two columns recent questions',
            'twigFile' => '/forum/two-columns-recent-questions.html.twig',
            'type' => 'forum',
            'content' => [
                'labelCategories' => 'Categories',
                'labelPopularQuestions' => 'Popular topics',
                'hasDesign' => 'false',
                'backgroundColor' => 'brand',
            ],
            'modal' => 'edit-generic-modal',
        ];

        $forumHomePageTypeDefArray = [
            'title' => 'Forum Homepage',
        ];

        $forumHomePageDefArray = [
            'title'     => 'Forum Homepage',
            'url'       => '',
            'metaDesc'  => '',
            'metaKey'   => '',
            'sitemap'   => false,
            'customTag' => '',
            'pageType'  => null,
        ];

        $forumHomePagePageTypeQueryBuilder = $this->connection->createQueryBuilder();
        $forumHomePagePageTypeQueryBuilder
            ->select('pt.id')
            ->from('PageType', 'pt')
            ->where('pt.title = :title')
            ->orderBy('pt.id','DESC')
            ->setParameter(':title', $forumHomePageTypeDefArray['title']);
        $forumHomePagePageTypeId = $forumHomePagePageTypeQueryBuilder->execute()->fetch();

        //If Forum Home Page page type does not exists, create it
        if(empty($forumHomePagePageTypeId)){
            $container = $this->getContainer();
            if(!empty($container)) {
                $manager = $container->get('doctrine.orm.entity_manager');
                if (!empty($manager)) {
                    $forumHomePagePageType = new PageType();
                    $forumHomePagePageType->setTitle($forumHomePageTypeDefArray['title']);

                    $manager->persist($forumHomePagePageType);
                    $manager->flush();
                    $forumHomePagePageTypeId = $forumHomePagePageTypeQueryBuilder->execute()->fetch();
                }
                unset($manager);
            }
            unset($container);
        }

        // - If Forum Home Page page type exists, check existence of pages with type related to "Forum Home";
        // -- If exists, change de first occurrence title, url and page type to the actual definitions of the Forum HomePage page and after that remove all another occurrences.
        // -- Remove the page type related to "Forum Home"
        if(!empty($forumHomePagePageTypeId)) {
            $container = $this->getContainer();
            if (!empty($container)) {
                $forumHomePageDefArray['url'] = $container->getParameter('alias_forum_module');
                $em = $container->get('doctrine.orm.entity_manager');
                if (!empty($em)) {
                    $forumHomePagePageType = $container->get('doctrine')->getRepository('WysiwygBundle:PageType')->find($forumHomePagePageTypeId['id']);
                    if(is_array($forumHomePagePageType)){
                        $forumHomePagePageType = $forumHomePagePageType[0];
                    }
                    $forumHomePageDefArray['pageType'] = $forumHomePagePageType;

                    $forumHomePageQueryBuilder = $this->connection->createQueryBuilder();
                    $forumHomePageQueryBuilder
                        ->select('p.id')
                        ->from('Page', 'p')
                        ->where('p.title = :title')
                        ->setParameter(':title', 'Forum Home');

                    $forumHomePageIds = $forumHomePageQueryBuilder->execute()->fetchAll();

                    $forumHomePagePageQueryBuilder = $this->connection->createQueryBuilder();
                    $forumHomePagePageQueryBuilder
                        ->select('p.id')
                        ->from('Page', 'p')
                        ->where('p.title = :title')
                        ->orderBy('p.id', 'DESC')
                        ->setParameter(':title', $forumHomePageDefArray['title']);
                    $forumHomePagePageId = $forumHomePagePageQueryBuilder->execute()->fetch();

                    if (!empty($forumHomePageIds)) {
                        $firstHomePageOccurrence = true;
                        foreach ($forumHomePageIds as $forumHomePageId) {
                            /**
                             * @var Page $forumHomePage
                             */
                            $forumHomePage = $container->get('doctrine')->getRepository('WysiwygBundle:Page')->findOneBy([
                                'id' => $forumHomePageId['id']
                            ]);
                            if (!empty($forumHomePagePageId) || !$firstHomePageOccurrence) {
                                $forumHomePageWidgets = $container->get('doctrine')->getRepository('WysiwygBundle:PageWidget')->findBy([
                                    'pageId' => $forumHomePageId['id']
                                ]);
                                /**
                                 * @var PageWidget[] $forumHomePageWidgets
                                 */
                                $anyForumHomePageWidgetRemoved = false;
                                foreach ($forumHomePageWidgets as $forumHomePageWidget) {
                                    $em->remove($forumHomePageWidget);
                                    if (!$anyForumHomePageWidgetRemoved) {
                                        $anyForumHomePageWidgetRemoved = true;
                                    }
                                }
                                if ($anyForumHomePageWidgetRemoved) {
                                    $em->flush();
                                }
                                unset($anyForumHomePageWidgetRemoved);
                                $em->remove($forumHomePage);
                                $em->flush();
                            } else {
                                $forumHomePage->setTitle($forumHomePageDefArray['title']);
                                $forumHomePage->setUrl($forumHomePageDefArray['url']);
                                $forumHomePage->setPageType($forumHomePageDefArray['pageType']);
                                $em->persist($forumHomePage);
                                $em->flush();
                            }
                            if ($firstHomePageOccurrence) {
                                $firstHomePageOccurrence = false;
                            }
                        }

                        $forumHomePageTypeQueryBuilder = $this->connection->createQueryBuilder();
                        $forumHomePageTypeQueryBuilder
                            ->select('pt.id')
                            ->from('PageType', 'pt')
                            ->where('pt.title = :title')
                            ->orderBy('pt.id', 'DESC')
                            ->setParameter(':title', 'Forum Home');
                        $forumHomePageTypeId = $forumHomePagePageTypeQueryBuilder->execute()->fetch();
                        if (!empty($forumHomePageTypeId)) {
                            /**
                             * @var PageType $forumHomePageType
                             */
                            $forumHomePageType = $container->get('doctrine')->getRepository('WysiwygBundle:PageType')->findOneBy([
                                'id' => $forumHomePageTypeId['id']
                            ]);
                            if (!empty($forumHomePageType)) {
                                $forumHomeWidgetPageTypes = $container->get('doctrine')->getRepository('WysiwygBundle:WidgetPageType')->findBy([
                                    'pagetype_id' => $forumHomePageTypeId['id']
                                ]);
                                $anyPageTypeRemoved = false;
                                foreach ($forumHomeWidgetPageTypes as $forumHomeWidgetPageType) {
                                    $em->remove($forumHomeWidgetPageType);
                                    if (!$anyPageTypeRemoved) {
                                        $anyPageTypeRemoved = true;
                                    }
                                }
                                if ($anyPageTypeRemoved) {
                                    $em->flush();
                                }
                                $em->remove($forumHomePageType);
                                $em->flush();
                            }
                        }
                    }
                }
                unset($em);
            }
            unset($container);
        }

        $twoColumnsRecentQuestionsWidgetQueryBuilder = $this->connection->createQueryBuilder();
        $twoColumnsRecentQuestionsWidgetQueryBuilder
            ->select('w.id')
            ->from('Widget', 'w')
            ->where('w.title = :title')
            ->orderBy('w.id','DESC')
            ->setParameter(':title', $twoColumnsDefArray['title']);

        $twoColumnsRecentQuestionsWidgetId = $twoColumnsRecentQuestionsWidgetQueryBuilder->execute()->fetch();
        //If Two Columns Recent Questions widget does not exists, create it
        if(empty($twoColumnsRecentQuestionsWidgetId)){
            $container = $this->getContainer();
            if(!empty($container)) {
                $manager = $container->get('doctrine.orm.entity_manager');
                if (!empty($manager)) {
                    $twoColumnsWidget = new Widget();

                    $twoColumnsWidget->setTitle($twoColumnsDefArray['title']);
                    $twoColumnsWidget->setTwigFile($twoColumnsDefArray['twigFile']);
                    $twoColumnsWidget->setType($twoColumnsDefArray['type']);
                    $twoColumnsWidget->setContent(json_encode($twoColumnsDefArray['content']));
                    $twoColumnsWidget->setModal($twoColumnsDefArray['modal']);

                    $manager->persist($twoColumnsWidget);
                    $manager->flush();
                    $twoColumnsRecentQuestionsWidgetId = $twoColumnsRecentQuestionsWidgetQueryBuilder->execute()->fetch();
                }
                unset($manager);
            }
            unset($container);
        }

        //If Two Columns Recent Questions widget exists:
        //- Check existence of the Forum Homepage widget in the Forum HomePage pages and:
        //-- If existent, exchange the Forum Homepage widget to the Two Columns Recent Questions widget
        //-- Check existence of a search widget in this page and:
        //--- If search widget does not exists, add one just before the Two Columns Recent Questions widget
        //-- Remove the Forum Homepage widget definition
        //-- Remove the Forum Homepage widget relations definition with Themes
        //-- Remove the Forum Homepage widget relations definition with Page Types
        if(!empty($twoColumnsRecentQuestionsWidgetId)) {
            $forumHomepageWidgetQueryBuilder = $this->connection->createQueryBuilder();
            $forumHomepageWidgetQueryBuilder
                ->select('w.id')
                ->from('Widget', 'w')
                ->where('w.title = :title')
                ->setParameter(':title', 'Forum Homepage');

            $forumHomepageWidgetIds = $forumHomepageWidgetQueryBuilder->execute()->fetchAll();

            if (!empty($forumHomepageWidgetIds)) {
                $container = $this->getContainer();
                if (!empty($container)) {
                    $em = $container->get('doctrine.orm.entity_manager');
                    if (!empty($em)) {

                        $searchBarWidget = $container->get('doctrine')->getRepository('WysiwygBundle:Widget')->findOneBy([
                            'title' => Widget::SEARCH_BAR
                        ]);

                        $twoColumnsRecentQuestionsWidget = $container->get('doctrine')->getRepository('WysiwygBundle:Widget')->findOneBy([
                            'id'=>$twoColumnsRecentQuestionsWidgetId['id']
                        ]);

                        $needFlush = false;
                        foreach ($forumHomepageWidgetIds as $forumHomepageWidgetId) {
                            $forumHomepageWidgetPageQueryBuilder = $this->connection->createQueryBuilder();
                            $forumHomepageWidgetPageQueryBuilder
                                ->select('pw.page_id')
                                ->from('Page_Widget', 'pw')
                                ->where('pw.widget_id = :widgetId')
                                ->groupBy('pw.page_id')
                                ->setParameter(':widgetId', $forumHomepageWidgetId['id']);

                            $forumHomepageWidgetPageIds = $forumHomepageWidgetPageQueryBuilder->execute()->fetchAll();

                            $edirectoryThemeQueryBuilder = $this->connection->createQueryBuilder();
                            $edirectoryThemeQueryBuilder
                                ->select('t.id')
                                ->from('Theme', 't');
                            $edirectoryThemeIds = $edirectoryThemeQueryBuilder->execute()->fetchAll();

                            if (!empty($forumHomepageWidgetPageIds) && !empty($edirectoryThemeIds)) {
                                foreach ($forumHomepageWidgetPageIds as $forumHomepageWidgetPageId) {
                                    $forumHomepageWidgetPage = $container->get('doctrine')->getRepository('WysiwygBundle:Page')->findOneBy([
                                        'id' => $forumHomepageWidgetPageId['page_id']
                                    ]);
                                    if (!empty($forumHomepageWidgetPage)) {
                                        foreach ($edirectoryThemeIds as $edirectoryThemeId) {

                                            /**
                                             * @var PageWidget $forumHomepagePageWidget
                                             */
                                            $forumHomepagePageWidget = $container->get('doctrine')->getRepository('WysiwygBundle:PageWidget')->findOneBy([
                                                'pageId' => $forumHomepageWidgetPageId['page_id'],
                                                'themeId' => $edirectoryThemeId['id'],
                                                'widgetId' => $forumHomepageWidgetId['id']
                                            ]);

                                            if(!empty($forumHomepagePageWidget)) {
                                                $forumHomepageWidgetPagePageWidgets = $container->get('doctrine')->getRepository('WysiwygBundle:PageWidget')->findBy([
                                                    'pageId' => $forumHomepageWidgetPageId['page_id'],
                                                    'themeId' => $edirectoryThemeId['id']
                                                ]);

                                                if (!empty($forumHomepageWidgetPagePageWidgets)) {
                                                    $pageAlreadyHasSearchWidget = false;
                                                    foreach ($forumHomepageWidgetPagePageWidgets as $forumHomepageWidgetPagePageWidget) {
                                                        $forumHomepageWidgetPagePageWidgetWidget = $forumHomepageWidgetPagePageWidget->getWidget();
                                                        if (!empty($forumHomepageWidgetPagePageWidgetWidget)) {
                                                            $widgetTitle = $forumHomepageWidgetPagePageWidgetWidget->getTitle();
                                                            if ($forumHomepageWidgetPagePageWidgetWidget->getType() == 'search' && strpos($widgetTitle, 'Search') !== false) {
                                                                $pageAlreadyHasSearchWidget = true;
                                                                unset($pagePageWidgetWidget);
                                                                break;
                                                            }
                                                        }
                                                        unset($pagePageWidgetWidget);
                                                    }


                                                    if (!$pageAlreadyHasSearchWidget && !empty($searchBarWidget)) {
                                                        $currentWidgetOrder = $forumHomepagePageWidget->getOrder();
                                                        $forumHomepageWidgetPagePageWidgetsArrayCollection = new ArrayCollection($forumHomepageWidgetPagePageWidgets);
                                                        $orderGreaterCriteria = Criteria::create()->andWhere(Criteria::expr()->gt('order', $currentWidgetOrder));
                                                        /**
                                                         * @var PageWidget[] $pagePageWidgetsWithGreaterOrder
                                                         */
                                                        $pagePageWidgetsWithGreaterOrder = $forumHomepageWidgetPagePageWidgetsArrayCollection->matching($orderGreaterCriteria)->toArray();

                                                        if (!empty($pagePageWidgetsWithGreaterOrder)) {
                                                            foreach ($pagePageWidgetsWithGreaterOrder as $pagePageWidgetWithGreaterOrder) {
                                                                $order = $pagePageWidgetWithGreaterOrder->getOrder();
                                                                $pagePageWidgetWithGreaterOrder->setOrder($order + 1);
                                                                $em->persist($pagePageWidgetWithGreaterOrder);
                                                                if (!$needFlush) $needFlush = true;
                                                            }
                                                        }
                                                        $forumHomepagePageWidgetOriginalOrder = $forumHomepagePageWidget->getOrder();
                                                        $forumHomepagePageWidget->setOrder($forumHomepagePageWidgetOriginalOrder + 1);

                                                        $searchBarPageWidget = new PageWidget();
                                                        $searchBarPageWidget->setTheme($forumHomepagePageWidget->getTheme());
                                                        $searchBarPageWidget->setOrder($forumHomepagePageWidgetOriginalOrder);
                                                        $searchBarPageWidget->setPage($forumHomepageWidgetPage);
                                                        $searchBarPageWidget->setWidget($searchBarWidget);
                                                        $encodedSearchBarPageWidgetContentJson = [
                                                            'placeholderSearchKeyword' => [
                                                                'value' => 'Subjects, terms, answers...',
                                                                'label' => 'Placeholder for search by keyword field'
                                                            ],
                                                            'hasDesign' => 'true',
                                                            'backgroundColor' => 'white',
                                                        ];
                                                        $encodedSearchBarPageWidgetContentEncodedJson = json_encode($encodedSearchBarPageWidgetContentJson);
                                                        $searchBarPageWidget->setContent($encodedSearchBarPageWidgetContentEncodedJson);
                                                        $em->persist($searchBarPageWidget);
                                                        if (!$needFlush) $needFlush = true;
                                                    }
                                                }
                                                if (!empty($twoColumnsRecentQuestionsWidget)) {
                                                    $forumHomepagePageWidget->setWidget($twoColumnsRecentQuestionsWidget);
                                                    $forumHomepagePageWidget->setContent(json_encode($twoColumnsDefArray['content']));
                                                    $em->persist($forumHomepagePageWidget);
                                                    if (!$needFlush) $needFlush = true;
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            $forumHomepageWidgetThemeQueryBuilder = $this->connection->createQueryBuilder();
                            $forumHomepageWidgetThemeQueryBuilder
                                ->delete('Widget_Theme')
                                ->where('widget_id = :widgetId')
                                ->setParameter(':widgetId', $forumHomepageWidgetId['id'])
                                ->execute();

                            $forumHomepageWidgetPageTypeQueryBuilder = $this->connection->createQueryBuilder();
                            $forumHomepageWidgetPageTypeQueryBuilder
                                ->delete('Widget_PageType')
                                ->where('widget_id = :widgetId')
                                ->setParameter(':widgetId', $forumHomepageWidgetId['id'])
                                ->execute();

                            $forumHomepageWidget = $container->get('doctrine')->getRepository('WysiwygBundle:Widget')->find($forumHomepageWidgetId['id']);
                            if (is_array($forumHomepageWidget)) {
                                foreach ($forumHomepageWidget as $forumHomepageWidgetArrayItem) {
                                    $em->remove($forumHomepageWidgetArrayItem);
                                    if (!$needFlush) $needFlush = true;
                                }
                            } else {
                                if (!empty($forumHomepageWidget)) {
                                    $em->remove($forumHomepageWidget);
                                    if (!$needFlush) $needFlush = true;
                                }
                            }
                        }
                        if($needFlush) {
                            $em->flush();
                        }
                    }
                    unset($em);
                }
                unset($container);
            }
        }

        //Check existence of the Horizontal Question Bar widget in the Forum Detail pages and:
        //-- If existent:
        //--- Check the existence of a search widget in the page and:
        //---- If not existent:
        //----- Add one Search Bar widget just after the Horizontal Question Bar widget
        $container = $this->getContainer();
        if (!empty($container)) {
            $em = $container->get('doctrine.orm.entity_manager');
            if (!empty($em)) {
                $horizontalQuestionBarWidget = $container->get('doctrine')->getRepository('WysiwygBundle:Widget')->findOneBy(['title' => 'Horizontal Question Bar']);
                /**
                 * @var Widget $horizontalQuestionBarWidget
                 */
                $searchBarWidget = $container->get('doctrine')->getRepository('WysiwygBundle:Widget')->findOneBy(['title'=>Widget::SEARCH_BAR]);
                /**
                 * @var Widget $searchBarWidget
                 */
                if(!empty($horizontalQuestionBarWidget)&&!empty($searchBarWidget)) {
                    /**
                     * @var Page $forumDetailPage
                     */
                    $forumDetailPage = $container->get('doctrine')->getRepository('WysiwygBundle:Page')->findOneBy(['title' => 'Forum Detail']);
                    if (!empty($forumDetailPage)) {

                        /**
                         * @var PersistentCollection $forumDetailPagePageWidgets
                         */
                        $forumDetailPagePageWidgets = $forumDetailPage->getPageWidgets();

                        $horizontalQuestionBarPageWidgetCriteria = Criteria::create()->orderBy(['order'=>'ASC'])->andWhere(Criteria::expr()->eq('widgetId', $horizontalQuestionBarWidget->getId()));

                        $forumDetailPageHorizontalQuestionBarPageWidgets = $forumDetailPagePageWidgets->matching($horizontalQuestionBarPageWidgetCriteria);
                        if(!empty($forumDetailPageHorizontalQuestionBarPageWidgets)) {
                            foreach($forumDetailPageHorizontalQuestionBarPageWidgets as $forumDetailPageHorizontalQuestionBarPageWidget){
                                if (!empty($forumDetailPageHorizontalQuestionBarPageWidget)) {
                                    $forumDetailPageHorizontalQuestionBarPageWidget->setWidget($searchBarWidget);
                                    $encodedSearchBarPageWidgetContentJson = [
                                        'placeholderSearchKeyword' => [
                                            'value' => 'Subjects, terms, answers...',
                                            'label' => 'Placeholder for search by keyword field'
                                        ],
                                        'hasDesign' => 'true',
                                        'backgroundColor' => 'brand',
                                    ];
                                    $encodedSearchBarPageWidgetContentEncodedJson = json_encode($encodedSearchBarPageWidgetContentJson);
                                    $forumDetailPageHorizontalQuestionBarPageWidget->setContent($encodedSearchBarPageWidgetContentEncodedJson);
                                    $em->persist($forumDetailPageHorizontalQuestionBarPageWidget);
                                    $em->flush();
                                }
                            }
                        }
                    }
                }
            }
            unset($em);
        }
        unset($container);
    }

    /**
     * @param Schema $schema
     * @throws DBALException
     * @throws AbortMigrationException
     * @throws SchemaException
     */
    public function up(Schema $schema)
    {
        $this->ensureForumPagesAndWidgets();

        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        if ($schema->hasTable('QuestionCategory')) {
            if ($schema->getTable('QuestionCategory')->hasColumn('thumb_id')) {
                $this->addSql('ALTER TABLE QuestionCategory DROP thumb_id');
            }
        }
        if ($schema->hasTable('Question')) {
            if (!$schema->getTable('Question')->hasColumn('number_views')) {
                $this->addSql('ALTER TABLE Question ADD number_views INT NOT NULL');
            }
        }
        if (!$schema->hasTable('Report_Question')) {
            $this->addSql('CREATE TABLE Report_Question (id INT AUTO_INCREMENT NOT NULL, question_id INT NOT NULL, report_type INT NOT NULL, report_amount INT NOT NULL, date DATE NOT NULL, INDEX question_id (question_id), INDEX report_type (report_type), INDEX date (date), UNIQUE INDEX report_info (question_id, report_type, date), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        }
        if (!$schema->hasTable('Report_Question_Daily')) {
            $this->addSql('CREATE TABLE Report_Question_Daily (question_id INT NOT NULL, day DATE NOT NULL, summary_view INT NOT NULL, detail_view INT NOT NULL, PRIMARY KEY(question_id, day)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        }
        if (!$schema->hasTable('Report_Question_Monthly')) {
            $this->addSql('CREATE TABLE Report_Question_Monthly (question_id INT NOT NULL, day DATE NOT NULL, summary_view INT NOT NULL, detail_view INT NOT NULL, PRIMARY KEY(question_id, day)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        if ($schema->hasTable('QuestionCategory')) {
            if (!$schema->getTable('QuestionCategory')->hasColumn('thumb_id')) {
                $this->addSql('ALTER TABLE QuestionCategory ADD thumb_id INT DEFAULT NULL');
            }
        }
        if ($schema->hasTable('Question')) {
            if ($schema->getTable('Question')->hasColumn('number_views')) {
                $this->addSql('ALTER TABLE Question DROP number_views');
            }
        }
        if ($schema->hasTable('Report_Question')) {
            $this->addSql('DROP TABLE Report_Question');
        }
        if ($schema->hasTable('Report_Question_Daily')) {
            $this->addSql('DROP TABLE Report_Question_Daily');
        }
        if ($schema->hasTable('Report_Question_Monthly')) {
            $this->addSql('DROP TABLE Report_Question_Monthly');
        }
    }
}
