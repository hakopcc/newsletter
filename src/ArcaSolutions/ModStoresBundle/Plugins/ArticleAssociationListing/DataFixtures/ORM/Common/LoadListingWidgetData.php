<?php


namespace ArcaSolutions\ModStoresBundle\Plugins\ArticleAssociationListing\DataFixtures\ORM\Common;


use ArcaSolutions\WysiwygBundle\Entity\ListingWidget;
use ArcaSolutions\WysiwygBundle\Repository\ListingWidgetRepository;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ArcaSolutions\ModStoresBundle\Plugins\ArticleAssociationListing\ListingWidgetAssociatedArticle;

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
        $listingWidgetAssociatedArticle = $listingWidgetRepository->findOneBy([
            'twigFile' => ListingWidgetAssociatedArticle::LISTING_WIDGET_TWIG_FILE,
            'title'    => ListingWidgetAssociatedArticle::LISTING_WIDGET_TITLE,
            'section'  => ListingWidget::MAIN_SECTION
        ]);
        $needToPersist = false;
        if(empty($listingWidgetAssociatedArticle)) {
            $listingWidgetAssociatedArticle = new ListingWidget();
            $listingWidgetAssociatedArticle->setTitle(ListingWidgetAssociatedArticle::LISTING_WIDGET_TITLE);
            $listingWidgetAssociatedArticle->setTwigFile(ListingWidgetAssociatedArticle::LISTING_WIDGET_TWIG_FILE);
            $listingWidgetAssociatedArticle->setType(ListingWidget::DETAIL_TYPE);
            $listingWidgetAssociatedArticle->setContent(json_encode(ListingWidgetAssociatedArticle::LISTING_WIDGET_DEFAULT_CONTENT));
            $listingWidgetAssociatedArticle->setModal(ListingWidgetAssociatedArticle::LISTING_WIDGET_DEFAULT_MODAL);
            $listingWidgetAssociatedArticle->setSection(ListingWidget::MAIN_SECTION);
            $needToPersist = true;
        } else {
            $existentType = $listingWidgetAssociatedArticle->getType();
            $existentContent = json_decode($listingWidgetAssociatedArticle->getContent(),false);
            $invalidContent = json_last_error()!==JSON_ERROR_NONE;
            $existentModal = $listingWidgetAssociatedArticle->getModal();
            if($existentType!==ListingWidget::DETAIL_TYPE){
                $listingWidgetAssociatedArticle->setType(ListingWidget::DETAIL_TYPE);
                $needToPersist = true;
            }
            if($invalidContent || $existentContent!==ListingWidgetAssociatedArticle::LISTING_WIDGET_DEFAULT_CONTENT){
                $listingWidgetAssociatedArticle->setContent(json_encode(ListingWidgetAssociatedArticle::LISTING_WIDGET_DEFAULT_CONTENT));
                $needToPersist = true;
            }
            if($existentModal!==ListingWidgetAssociatedArticle::LISTING_WIDGET_DEFAULT_MODAL){
                $listingWidgetAssociatedArticle->setModal(ListingWidgetAssociatedArticle::LISTING_WIDGET_DEFAULT_MODAL);
                $needToPersist = true;
            }
        }
        if($needToPersist){
            $manager->persist($listingWidgetAssociatedArticle);
            $manager->flush();
        }

        $referenceName = 'LISTING_' . ListingWidget::MAIN_SECTION . '_' . ListingWidgetAssociatedArticle::LISTING_WIDGET_TITLE;
        if(!$this->hasReference($referenceName)) {
            $this->setReference($referenceName, $listingWidgetAssociatedArticle);
        } else {
            $this->addReference($referenceName, $listingWidgetAssociatedArticle);
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
