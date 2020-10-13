<?php

namespace ArcaSolutions\ListingBundle\DataFixtures\ORM;

use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\ListingBundle\Entity\ListingLevel;
use ArcaSolutions\ListingBundle\Entity\ListingLevelField;
use ArcaSolutions\ListingBundle\Entity\ListingTField;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadListingLevelFieldData
 * @package ArcaSolutions\ListingBundle\DataFixtures\ORM
 */
class LoadListingLevelFieldData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /* These are the standard data of the system */
        $standardInserts = [
            ListingLevel::DIAMOND_LEVEL => [
                [
                    'field'         => 'email',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::EMAIL
                ],
                [
                    'field'         => 'phone',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::PHONE
                ],
                [
                    'field'         => 'url',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::URL
                ],
                [
                    'field'         => 'additionalPhone',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::ADDITIONAL_PHONE
                ],
                [
                    'field'         => 'videoSnippet',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::VIDEO
                ],
                [
                    'field'         => 'attachmentFile',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::ATTACHMENT_FILE
                ],
                [
                    'field'         => 'description',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::DESCRIPTION
                ],
                [
                    'field'         => 'longDescription',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::LONG_DESCRIPTION
                ],
                [
                    'field'         => 'hoursWork',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::HOURS_WORK
                ],
                [
                    'field'         => 'choices',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::BADGES
                ],
                [
                    'field'         => 'locations',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::LOCATIONS
                ],
                [
                    'field'         => 'socialNetwork',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::SOCIAL_NETWORK
                ],
                [
                    'field'         => 'features',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::FEATURES
                ],
                [
                    'field'         => 'deals',
                    'quantity'      => 10,
                    'templateField' => ListingTField::DEALS
                ],
                [
                    'field'         => 'classifieds',
                    'quantity'      => 10,
                    'templateField' => ListingTField::CLASSIFIEDS
                ],
                [
                    'field'         => 'review',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::REVIEW
                ],
                [
                    'field'         => 'mainImage',
                    'quantity'      => 9,
                    'templateField' => ListingTField::IMAGES
                ],
                [
                    'field'         => 'coverImage',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::COVER_IMAGE
                ],
                [
                    'field'         => 'logoImage',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::LOGO_IMAGE
                ]
            ],
            ListingLevel::GOLD_LEVEL => [
                [
                    'field'         => 'email',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::EMAIL
                ],
                [
                    'field'         => 'url',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::URL
                ],
                [
                    'field'         => 'phone',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::PHONE
                ],
                [
                    'field'         => 'additionalPhone',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::ADDITIONAL_PHONE
                ],
                [
                    'field'         => 'description',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::DESCRIPTION
                ],
                [
                    'field'         => 'choices',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::BADGES
                ],
                [
                    'field'         => 'deals',
                    'quantity'      => 0,
                    'templateField' => ListingTField::DEALS
                ],
                [
                    'field'         => 'classifieds',
                    'quantity'      => 0,
                    'templateField' => ListingTField::CLASSIFIEDS
                ],
                [
                    'field'         => 'review',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::REVIEW
                ],
                [
                    'field'         => 'mainImage',
                    'quantity'      => 0,
                    'templateField' => ListingTField::IMAGES
                ]
            ],
            ListingLevel::SILVER_LEVEL => [
                [
                    'field'         => 'email',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::EMAIL
                ],
                [
                    'field'         => 'url',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::URL
                ],
                [
                    'field'         => 'phone',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::PHONE
                ],
                [
                    'field'         => 'deals',
                    'quantity'      => 0,
                    'templateField' => ListingTField::DEALS
                ],
                [
                    'field'         => 'classifieds',
                    'quantity'      => 0,
                    'templateField' => ListingTField::CLASSIFIEDS
                ],
                [
                    'field'         => 'review',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::REVIEW
                ],
                [
                    'field'         => 'mainImage',
                    'quantity'      => 0,
                    'templateField' => ListingTField::IMAGES
                ]
            ],
            ListingLevel::BRONZE_LEVEL => [
                [
                    'field'         => 'phone',
                    'quantity'      => NULL,
                    'templateField' => ListingTField::PHONE
                ],
                [
                    'field'         => 'deals',
                    'quantity'      => 0,
                    'templateField' => ListingTField::DEALS
                ],
                [
                    'field'         => 'classifieds',
                    'quantity'      => 0,
                    'templateField' => ListingTField::CLASSIFIEDS
                ],
                [
                    'field'         => 'mainImage',
                    'quantity'      => 0,
                    'templateField' => ListingTField::IMAGES
                ]
            ]
        ];

        $repository = $manager->getRepository('ListingBundle:ListingLevelField');

        foreach ($standardInserts as $level => $listingLevelFieldInserts) {
            /** @var ListingLevel $listingLevel */
            $listingLevel = $this->container->get('doctrine')->getRepository('ListingBundle:ListingLevel')->find($level);
            foreach($listingLevelFieldInserts as $listingLevelFieldInsert) {
                $query = $repository->findOneBy([
                    'level' => $level,
                    'field' => $listingLevelFieldInsert['field'],
                ]);

                $listingLevelField = new ListingLevelField();

                /* checks if the listingLevelField already exist so they can be updated or added */
                if (!$query) {
                    if($this->hasReference('FIELD_' . $listingLevelFieldInsert['templateField'])) {
                        /** @var ListingTField $templateField */
                        $templateField = $this->getReference('FIELD_' . $listingLevelFieldInsert['templateField']);
                    } else {
                        $templateField = $this->container->get('doctrine')->getRepository('ListingBundle:ListingTField')->findOneBy([
                            'label'     => $listingLevelFieldInsert['templateField'],
                            'fieldType' => 'default'
                        ]);
                    }

                    $listingLevelField->setListingLevel($listingLevel);
                    $listingLevelField->setField($listingLevelFieldInsert['field']);
                    $listingLevelField->setListingTField($templateField);
                    !empty($listingLevelFieldInsert['quantity']) and $listingLevelField->setQuantity($listingLevelFieldInsert['quantity']);

                    $manager->persist($listingLevelField);
                }
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
        return 3;
    }

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
