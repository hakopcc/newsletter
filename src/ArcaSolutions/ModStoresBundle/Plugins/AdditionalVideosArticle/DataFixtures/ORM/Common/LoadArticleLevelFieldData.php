<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\AdditionalVideosArticle\DataFixtures\ORM\Common;

use ArcaSolutions\ModStoresBundle\Plugins\AdditionalVideosArticle\Entity\ArticleLevelFieldVideos;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadArticleLevelFieldData
 */
class LoadArticleLevelFieldData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $standardInserts = [
            [
                'level' => 50,
                'field' => 4,
            ],
        ];

        foreach ($standardInserts as $listingLevelFieldInsert) {

            if (!$manager->getRepository('AdditionalVideosArticleBundle:ArticleLevelFieldVideos')->findOneBy([
                'level' => $listingLevelFieldInsert['level'],
            ])) {

                $listingLevelField = new ArticleLevelFieldVideos();

                $listingLevelField->setLevel($listingLevelFieldInsert['level']);
                $listingLevelField->setField($listingLevelFieldInsert['field']);

                $manager->persist($listingLevelField);

            }
        }

        $manager->flush();
    }


    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1;
    }
}
