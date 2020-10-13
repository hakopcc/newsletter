<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Services\Synchronization;

use ArcaSolutions\CoreBundle\Services\Utility;
use ArcaSolutions\ElasticsearchBundle\Services\Synchronization\Modules\BaseSynchronizable;
use ArcaSolutions\ElasticsearchBundle\Services\Synchronization\Synchronization;
use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity\Question;
use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Search\QuestionConfiguration;
use Elastica\Document;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class QuestionSynchronizable extends BaseSynchronizable implements EventSubscriberInterface
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->configurationService = 'question.search';
        $this->databaseType = Synchronization::DATABASE_DOMAIN;
        $this->upsertFormat = static::DOCUMENT_UPSERT;
        $this->deleteFormat = static::DELETE_ID_RAW;
    }

    public static function getSubscribedEvents()
    {
        return [
            'edirectory.synchronization' => 'handleEvent',
        ];
    }

    public function handleEvent($event, $eventName)
    {
        $this->generateAll();
    }

    public function generateAll($output = null, $pageSize = Synchronization::BULK_THRESHOLD)
    {

        $progressBar = null;
        $doctrine = $this->container->get('doctrine');
        $ca = $doctrine->getRepository('CommunityForumBundle:QuestionCategory');
        $qB = $doctrine->getRepository('CommunityForumBundle:Question')->createQueryBuilder('question');

        if ($output) {
            $totalCount = $qB->select('COUNT(question.id)')->getQuery()->getSingleScalarResult();

            $progressBar = new ProgressBar($output, $totalCount);

            $progressBar->start();
        }

        $this->container->get('search.engine')->clearType(QuestionConfiguration::$elasticType);

        $iteration = 0;

        $query = $qB->select('question.id')
            ->where('question.status = :questionStatus')
            ->setParameter('questionStatus', 'A');

        do {
            $query->setMaxResults($pageSize)->setFirstResult($pageSize * $iteration++);

            $ids = $query->getQuery()->getArrayResult();


            if ($foundCount = count($ids)) {
                array_walk($ids, function (&$value) {
                    $value = $value['id'];
                });

                $this->addUpsert($ids);
                $progressBar and $progressBar->advance($foundCount);
            }

            $doctrine->getManager()->clear();
        } while ($foundCount);

        $progressBar and $progressBar->finish();
    }

    /**
     * {@inheritdoc}
     */
    public function getUpsertStash()
    {
        $result = [];

        if ($ids = parent::getUpsertStash()) {
            $ca = $this->container->get('doctrine')->getRepository('CommunityForumBundle:QuestionCategory');
            $elements = $this->container->get('doctrine')->getRepository('CommunityForumBundle:Question')->findBy(['id' => $ids]);

            while ($element = array_pop($elements)) {
                $result[] = $this->getUpsertDocument($element);
            }
        }

        return $result;
    }

    /**
     * @param Question $question
     * @return Document|null
     */
    public function getUpsertDocument($question)
    {
        $document = null;

        if ($question and is_object($question)) {
            $document = new Document(
                $question->getId(),
                $this->generateDocFromEntity($question),
                $this->container->get($this->getConfigurationService())->getElasticType(),
                $this->container->get('search.engine')->getElasticIndexName()
            );

            $document->setDocAsUpsert(true);
        }

        return $document;
    }

    /**
     * @param Question $element
     * @return array
     */
    public function generateDocFromEntity($element)
    {
        if ($reviewCount = $this->container->get('doctrine')->getRepository('WebBundle:Review')->getTotalByItemId($element->getId(),
            'question')
        ) {
            is_array($reviewCount) and $reviewCount = array_pop($reviewCount);
        }

        $categoryIds = [];
        if ($element->getCategory() !== null) {
            $categoryIds[] = $this->container->get('question.category.synchronization')->normalizeId($element->getCategory()->getId());
        }

        $description = str_replace(["\r", "\n", "\t", "\s{2,}"], ' ',
            html_entity_decode(strip_tags($element->getDescription()),ENT_QUOTES,'UTF-8'));
        $description = preg_replace('!\s+!', ' ', $description);

        $publicationDate = $element->getEntered()->format('Y-m-d');

        $fulltextsearchKeyword = $element->getFulltextsearchKeyword();

        $suggest = [
            'input'   => $fulltextsearchKeyword,
            'output'  => $element->getTitle(),
            'payload' => [
                'friendlyUrl' => $element->getFriendlyUrl(),
                'type'        => 'question',
                'id'          => $element->getId(),
            ],
            'weight'  => 90,
        ];

        $document =
            [
                'accountId'       => $element->getAccountId(),
                'categoryId'      => implode(' ', $categoryIds) ?: null,
                'description'     => $description,
                'friendlyUrl'     => $element->getFriendlyUrl(),
                'reviewCount'     => 0,
                'publicationDate' => ($publicationDate === Utility::BAD_DATE_VALUE) ? null : $publicationDate,
                'searchInfo'      => [
                    'keyword' => $fulltextsearchKeyword,
                ],
                'status'          => (strtolower($element->getStatus()) === 'a'),
                'suggest'         => [
                    'what' => $suggest,
                ],
                'title'           => $element->getTitle(),
                'views' => $element->getNumberViews(),
            ];

        return $document;
    }

    /**
     * @inheritdoc
     */
    public function extractFromResult($info)
    {
        return [
            '_id'             => $info['_id'],
            'accountId'       => $info['accountId'],
            'categoryId'      => $info['categoryId'],
            'description'     => $info['description'],
            'friendlyUrl'     => $info['friendlyUrl'],
            'reviewCount'     => $info['reviewCount'],
            'publicationDate' => $info['publicationDate'],
            'searchInfo'      => [
                'keyword' => $info['searchInfo.keyword'],
            ],
            'status'          => $info['status'],
            'suggest'         => [
                'what' => [
                    'input'   => $info['suggest.what.input'],
                    'output'  => $info['suggest.what.output'],
                    'payload' => $info['suggest.what.payload'],
                    'weight'  => $info['suggest.what.weight'],
                ],
            ],
            'title'           => $info['title'],
            'views' => $info['views'],
        ];
    }
}
