<?php
namespace ArcaSolutions\WebBundle\Twig\Extension;

use ArcaSolutions\ApiBundle\Entity\Result;
use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\WysiwygBundle\Entity\PageType;
use DateTime;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BlocksExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $containerInterface
     */
    public function __construct(ContainerInterface $containerInterface)
    {
        $this->container = $containerInterface;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('contentCards', [$this, 'contentCards'], [
                'needs_environment' => true,
                'is_safe' => ['html']
            ]),
            new \Twig_SimpleFunction('getCardData', [$this, 'getCardData'], [
                'is_safe'           => ['html'],
            ]),
            new \Twig_SimpleFunction('generateLink', [$this, 'generateLink'], []),
        ];
    }

    /**
     * @param \Twig_Environment $twig_Environment
     * @param $content
     * @param $widgetLink
     * @param null $items
     * @param bool $associatedItems
     * @param Listing|null $listing
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function contentCards(\Twig_Environment $twig_Environment, $content, $widgetLink = null, $items = null, $associatedItems = false, Listing $listing = null, $section = null)
    {
        if(\is_array($content)) {
            $content = json_decode(json_encode($content));
        }
        if($listing !== null) {
            $content->custom->listing = $listing->getId();
            if(!empty($content->custom->filter)) {
                if (strpos($content->custom->filter, 'location') !== false) {
                    $content->custom->locations = new \stdClass();
                    !empty($listing->getLocation1()) and $content->custom->locations->location_1 = $listing->getLocation1();
                    !empty($listing->getLocation2()) and $content->custom->locations->location_2 = $listing->getLocation2();
                    !empty($listing->getLocation3()) and $content->custom->locations->location_3 = $listing->getLocation3();
                    !empty($listing->getLocation4()) and $content->custom->locations->location_4 = $listing->getLocation4();
                    !empty($listing->getLocation5()) and $content->custom->locations->location_5 = $listing->getLocation5();

                    if(empty($content->custom->locations)) {
                        return '';
                    }
                }

                if (strpos($content->custom->filter, 'category') !== false) {
                    if(empty($listing->getCategories()->getValues())) {
                        return '';
                    }

                    $content->custom->categories = [];
                    foreach($listing->getCategories()->getValues() as $category) {
                        $content->custom->categories[] = $category->getId();
                    }
                }
            }
        }
        if ($section === 'sidebar') {
            $content->cardType = 'sidebar-card';

        }

        $content->module = $content->module === 'promotion' ? 'deal' : $content->module;
        $modules = $this->container->get('modules');

        if (empty($content->items) && empty($content->custom)) {
            return '';
        }

        if (!$modules->isModule($content->module) || !$modules->isModuleAvailable($content->module)) {
            return '';
        }

        $quantity = !empty($content->items) ? null : $content->custom->quantity;

        if ($quantity !== null && $quantity < 1) {
            return '';
        }

        if(!$associatedItems) {
            $items = $this->container->get('search.block')->getCards($content->module, $quantity, $content);
        }

        if (!$items) {
            return '';
        }

        $flag = 0;

        $content->module === 'event' and $flag |= 1;
        !empty($content->items) and $flag |= 2;

        if($flag & 3) {
            foreach ($items as $item) {
                if ($flag & 1) {
                    $item->event = $this->container->get('doctrine')
                        ->getRepository('EventBundle:Event')
                        ->find($item->getId());
                }
                if ($flag & 2) {
                    $indexedItems[$item->getId()] = $item;
                }
            }
        }

        if($flag & 2) {
            foreach($content->items as $key => $contentItem) {
                !empty($indexedItems[$contentItem]) and $orderedItems[$key] = $indexedItems[$contentItem];
            }
            !empty($orderedItems) and $items = $orderedItems;
        }

        if(!empty($content->banner) && $content->banner !== 'empty') {
            $banner = $this->container->get('twig')->render('::widgets/page-editor/banners/banner.html.twig', [
                'content' => [
                    'bannerType' => $content->banner,
                    'banners'    => $content->banner === 'skyscraper' ? $content->banner : [
                        $content->banner,
                        $content->banner
                    ],
                    'isWide'     => $content->banner === 'skyscraper' ? 'true' : 'false',
                ]
            ]);
        } else {
            $banner = '';
        }

        if ($content->cardType === 'centralized-highlighted-card') {
            $jsHandler = $this->container->get('javascripthandler');
            $jsHandler->addJSExternalFile('assets/js/widgets/cards/centralized-highlighted-card.js');
        }

        $itemsPerRow = null;
        if(isset($content->columns)) {
            $itemsPerRow = $content->columns - ($banner ? 1 : 0);
        }

        $cardTypeBlockTwigName = "::modules/$content->module/blocks/$content->cardType.html.twig";
        $contentModule = $content->module;
        $contentCardType = $content->cardType;
        /* ModStores Hooks */
        HookFire('blocks-extension_before_return_rendered-card-type-block', [
            'twigName' => &$cardTypeBlockTwigName,
            'items'           => &$items,
            'itemsPerRow'     => &$itemsPerRow,
            'banner'          => &$banner,
            'content'         => &$content,
            'widgetLink'      => &$widgetLink,
            'module'          => &$contentModule,
            'cardType'        => &$contentCardType
        ]);

        return $twig_Environment->render($cardTypeBlockTwigName, [
            'items'           => $items,
            'itemsPerRow'     => $itemsPerRow,
            'banner'          => $banner,
            'content'         => $content,
            'widgetLink'      => $widgetLink,
            'module'          => $contentModule,
            'cardType'        => $contentCardType
        ]);
    }

    /**
     * @param $item
     * @param $itemType
     * @return array
     * @throws \Exception
     */
    public function getCardData($item, $itemType)
    {
        $detailLink = $this->container->get('router')->generate($itemType . '_detail',
            ['friendlyUrl' => !empty($item->friendlyUrl) ? $item->friendlyUrl : $item->getFriendlyUrl(), '_format' => 'html'],
            true
        );

        if ($item instanceof \Elastica\Result) {
            $thumbnail = !empty($item->thumbnail) ? $item->thumbnail : '';
        } else {
            if(!empty($item->getImageId())) {
                $thumbnail = $this->container->get('imagehandler')->getPath($this->container->get('doctrine')->getRepository('ImageBundle:Image')->find($item->getImageId()));
            }
            if ($itemType === 'listing') {
                $logoImage = $this->container->get('imagehandler')->getPath($item->getLogoImage());
            }
        }

        if (!empty($thumbnail)) {
            $imagePath = $this->container->get('templating.helper.assets')->getUrl($thumbnail, 'domain_images', null);
        } else {
            $imagePath = '';
        }

        $data = [
            'detailLink' => $detailLink,
            'imagePath'  => $imagePath
        ];

        !empty($logoImage) and $data['logoImage'] = $this->container->get('templating.helper.assets')->getUrl($logoImage, 'domain_images');

        if ($itemType === 'event') {
            if (!HookFire('blocksextension_overwrite_recurringdata', [
                'item' => $item,
                'data' => &$data
            ], true)) {
                if ($item->recurring['enabled'] && !empty($item->date['start'])) {
                    $dateStart = $this->container->get('event.recurring.service')->getNextOccurrence(
                        new DateTime($item->date['start']),
                        new DateTime($item->recurring['until']),
                        str_replace('RRULE:', '',
                            $this->container->get('event.recurring.service')->getRRule_rfc2445($item->event))
                    );
                } else {
                    $dateStart = !empty($item->date['start']) ? new \DateTime($item->date['start']) : $item->getStartDate();
                }

                if (!empty($dateStart)) {
                    $data['weekDay'] = $dateStart->format('w') + 1;
                    $data['month'] = $dateStart->format('m');
                    $data['day'] = $dateStart->format('d');
                }
            }
        }

        if ($itemType === 'deal') {
            $data['dealvalue'] = !empty($item->value['deal']) ? $item->value['deal'] : $item->getDealValue();
            $data['realvalue'] = !empty($item->value['real']) ? $item->value['real'] : $item->getRealValue();

            if(!empty($data['dealvalue']) && !empty($data['realvalue'])) {
                $data['percentage'] = 100 - ($data['dealvalue'] * 100 / $data['realvalue']);
            }

            $endDate = !empty($item->date['end']) ? new \DateTime($item->date['end']) : $item->getEndDate();

            $data['newEndDate'] = $endDate->modify('+1 day');
            $data['interval'] = $endDate->diff(new \DateTime());
        }

        if ($itemType === 'article' && !empty($item->authorImageId)) {
            $authorImage = $this->container->get('imagehandler')->getPath($this->container->get('doctrine')->getRepository('ImageBundle:Image')->find($item->authorImageId));
            $data['authorImage'] = $this->container->get('templating.helper.assets')->getUrl($authorImage, 'domain_images', null);
        }

        $data['itemId'] = $item->getId();

        return $data;
    }

    /**
     * @param \stdClass $linkObj
     *
     * @return string $link
     * @throws \Exception
     */
    public function generateLink($linkObj)
    {
        if (!empty($linkObj->target) && !empty($linkObj->value) && !empty($linkObj->customLink) && $linkObj->value === 'custom') {
            $link = ($linkObj->target === 'external' ? $linkObj->customLink : $this->container->get('pagetype.service')->getBaseUrl(PageType::HOME_PAGE) . '/' . $linkObj->customLink);
        } else {
            $pageRepository = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page');
            $link = $this->container->get('page.service')->getFinalPageUrl($pageRepository->find($linkObj->value));
        }

        return $link;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'blocks';
    }
}
