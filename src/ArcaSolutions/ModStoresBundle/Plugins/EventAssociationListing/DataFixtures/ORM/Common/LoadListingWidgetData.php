<?php


namespace ArcaSolutions\ModStoresBundle\Plugins\EventAssociationListing\DataFixtures\ORM\Common;


use ArcaSolutions\WysiwygBundle\Entity\ListingWidget;
use ArcaSolutions\WysiwygBundle\Repository\ListingWidgetRepository;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ArcaSolutions\ModStoresBundle\Plugins\EventAssociationListing\ListingWidgetAssociatedEvent;

/**
 * Class LoadListingWidgetData
 * @package ArcaSolutions\WysiwygBundle\DataFixtures\ORM
 */
class LoadListingWidgetData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        /** @var ListingWidgetRepository $listingWidgetRepository */
        $listingWidgetRepository = $manager->getRepository('WysiwygBundle:ListingWidget');
        /** @var ListingWidget|null $query */
        $listingWidgetAssociatedEvent = $listingWidgetRepository->findOneBy([
            'twigFile' => ListingWidgetAssociatedEvent::LISTING_WIDGET_TWIG_FILE,
            'title'    => ListingWidgetAssociatedEvent::LISTING_WIDGET_TITLE,
            'section'  => ListingWidget::MAIN_SECTION
        ]);
        $needToPersist = false;
        if(empty($listingWidgetAssociatedEvent)) {
            $listingWidgetAssociatedEvent = new ListingWidget();
            $listingWidgetAssociatedEvent->setTitle(ListingWidgetAssociatedEvent::LISTING_WIDGET_TITLE);
            $listingWidgetAssociatedEvent->setTwigFile(ListingWidgetAssociatedEvent::LISTING_WIDGET_TWIG_FILE);
            $listingWidgetAssociatedEvent->setType(ListingWidget::DETAIL_TYPE);
            $listingWidgetAssociatedEvent->setContent(json_encode(ListingWidgetAssociatedEvent::LISTING_WIDGET_DEFAULT_CONTENT));
            $listingWidgetAssociatedEvent->setModal(ListingWidgetAssociatedEvent::LISTING_WIDGET_DEFAULT_MODAL);
            $listingWidgetAssociatedEvent->setSection(ListingWidget::MAIN_SECTION);
            $needToPersist = true;
        } else {
            $existentType = $listingWidgetAssociatedEvent->getType();
            $existentContent = json_decode($listingWidgetAssociatedEvent->getContent(),false);
            $invalidContent = json_last_error()!==JSON_ERROR_NONE;
            $existentModal = $listingWidgetAssociatedEvent->getModal();
            if($existentType!==ListingWidget::DETAIL_TYPE){
                $listingWidgetAssociatedEvent->setType(ListingWidget::DETAIL_TYPE);
                $needToPersist = true;
            }
            if($invalidContent || $existentContent!==ListingWidgetAssociatedEvent::LISTING_WIDGET_DEFAULT_CONTENT){
                $listingWidgetAssociatedEvent->setContent(json_encode(ListingWidgetAssociatedEvent::LISTING_WIDGET_DEFAULT_CONTENT));
                $needToPersist = true;
            }
            if($existentModal!==ListingWidgetAssociatedEvent::LISTING_WIDGET_DEFAULT_MODAL){
                $listingWidgetAssociatedEvent->setModal(ListingWidgetAssociatedEvent::LISTING_WIDGET_DEFAULT_MODAL);
                $needToPersist = true;
            }
        }
        if($needToPersist){
            $manager->persist($listingWidgetAssociatedEvent);
            $manager->flush();
        }

        $referenceName = 'LISTING_' . ListingWidget::MAIN_SECTION . '_' . ListingWidgetAssociatedEvent::LISTING_WIDGET_TITLE;
        if(!$this->hasReference($referenceName)) {
            $this->setReference($referenceName, $listingWidgetAssociatedEvent);
        } else {
            $this->addReference($referenceName, $listingWidgetAssociatedEvent);
        }
    }


    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 4;
    }

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
