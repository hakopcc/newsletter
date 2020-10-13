<?php

namespace ArcaSolutions\WysiwygBundle\Services;

use ArcaSolutions\WysiwygBundle\Entity\ListingWidget;
use ArcaSolutions\WysiwygBundle\Entity\Widget;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ListingWidgetService
 * @package ArcaSolutions\WysiwygBundle\Services
 */
class ListingWidgetService
{
    /**
     * @var ContainerInterface,
     */
    private $container;

    public $customWidgets = [
        ListingWidget::RELATED_LISTINGS,
        ListingWidget::LINKED_LISTINGS,
        ListingWidget::MORE_DETAILS,
        ListingWidget::DESCRIPTION,
        ListingWidget::SPECIALTIES,
        ListingWidget::RANGE,
        ListingWidget::CALL_TO_ACTION,
        ListingWidget::CHECK_LIST
    ];

    /**
     * ListingWidgetService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $type
     * @param $section
     * @return ListingWidget[]|array
     */
    public function getListingWidgetsByTypeAndSection($type, $section)
    {
        return $this->container->get('doctrine')->getRepository('WysiwygBundle:ListingWidget')->findAllGrouped($type, $section);
    }

    /**
     * @param integer $id
     *
     * @return object
     */
    public function getOriginalListingWidget($id)
    {
        // Get Default Widget information (Widget Table)
        return $this->container->get('doctrine')->getRepository('WysiwygBundle:ListingWidget')->find($id);
    }

    /**
     * Return standard Listing Widgets
     *
     * @return array
     */
    public function getDefaultListingWidgets()
    {
        $standardWidgets = [];

        $standardWidgets[] = $this->getDefaultDetailHeaderListingWidgets();
        $standardWidgets[] = $this->getDefaultDetailMainListingWidgets();
        $standardWidgets[] = $this->getDefaultDetailSidebarListingWidgets();

        return $standardWidgets;
    }

    /**
     * Return standard detail header Listing Widgets
     *
     * @return array
     */
    public function getDefaultDetailHeaderListingWidgets()
    {
        $standardWidgets = [
            [
                'title'    => ListingWidget::HEADER,
                'twigFile' => '/header.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [],
                'modal'    => '',
                'section'  => ListingWidget::HEADER_SECTION
            ],
            [
                'title'    => ListingWidget::LEADERBOARD,
                'twigFile' => '/banner.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'bannerType'      => 'leaderboard',
                    'isWide'          => 'false',
                    'banners'         => [
                        1 => 'leaderboard'
                    ],
                    'backgroundColor' => 'base'
                ],
                'modal'    => '',
                'section'  => ListingWidget::HEADER_SECTION
            ]
        ];

        return $standardWidgets;
    }

    /**
     * Return standard detail main Listing Widgets
     *
     * @return array
     */
    public function getDefaultDetailMainListingWidgets()
    {
        $standardWidgets = [
            [
                'title'    => ListingWidget::ABOUT,
                'twigFile' => '/about.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [],
                'modal'    => '',
                'section'  => ListingWidget::MAIN_SECTION
            ],
            [
                'title'    => ListingWidget::ADDITIONAL_INFORMATION,
                'twigFile' => '/additional-information.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [],
                'modal'    => '',
                'section'  => ListingWidget::MAIN_SECTION
            ],
            [
                'title'    => ListingWidget::FEATURES,
                'twigFile' => '/feature.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [],
                'modal'    => '',
                'section'  => ListingWidget::MAIN_SECTION
            ],
            [
                'title'    => ListingWidget::REVIEWS_PAGINATED,
                'twigFile' => '/reviews-paginated.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [],
                'modal'    => '',
                'section'  => ListingWidget::MAIN_SECTION
            ],
            [
                'title'    => ListingWidget::PHOTO_GALLERY,
                'twigFile' => '/photo-gallery.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [],
                'modal'    => '',
                'section'  => ListingWidget::MAIN_SECTION
            ],
            [
                'title'    => ListingWidget::RECENT_REVIEWS,
                'twigFile' => '/recent-reviews.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [],
                'modal'    => '',
                'section'  => ListingWidget::MAIN_SECTION
            ],
            [
                'title'    => ListingWidget::VIDEO,
                'twigFile' => '/video.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [],
                'modal'    => '',
                'section'  => ListingWidget::MAIN_SECTION
            ],
            [
                'title'    => ListingWidget::ASSOCIATED_DEALS,
                'twigFile' => '/associated-deals.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [],
                'modal'    => '',
                'section'  => ListingWidget::MAIN_SECTION
            ],
            [
                'title'    => ListingWidget::ASSOCIATED_CLASSIFIEDS,
                'twigFile' => '/associated-classifieds.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [],
                'modal'    => '',
                'section'  => ListingWidget::MAIN_SECTION
            ],
            [
                'title'    => ListingWidget::CHECK_LIST,
                'twigFile' => '/check-list.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'fieldTitle'      => '',
                    'icon'            => 'fa-check',
                    'hideTitle'       => 'false',
                    'groupFields'     => [
                        [
                            'title'       => '',
                            'placeholder' => ''
                        ]
                    ]
                ],
                'modal'    => 'edit-checklist-modal',
                'section'  => ListingWidget::MAIN_SECTION
            ],
            [
                'title'    => ListingWidget::CALL_TO_ACTION,
                'twigFile' => '/call-to-action-button.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'fieldTitle'  => '',
                    'buttonStyle' => 'default',
                    'alignment'   => 'extended',
                    'buttonLabel' => '',
                    'required'    => 'true'
                ],
                'modal'    => 'edit-calltoaction-modal',
                'section'  => ListingWidget::MAIN_SECTION
            ],
            [
                'title'    => ListingWidget::DESCRIPTION,
                'twigFile' => '/description.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'fieldTitle'      => '',
                    'descriptionType' => 'short',
                    'placeholder'     => '',
                    'hideTitle'       => 'false',
                    'required'        => 'true'
                ],
                'modal'    => 'edit-description-modal',
                'section'  => ListingWidget::MAIN_SECTION
            ],
            [
                'title'    => ListingWidget::MORE_DETAILS,
                'twigFile' => '/more-details.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'fieldTitle'      => '',
                    'required'    => 'true',
                    'groupFields'     => [
                        [
                            'title'       => '',
                            'placeholder' => ''
                        ]
                    ]
                ],
                'modal'    => 'edit-moredetails-modal',
                'section'  => ListingWidget::MAIN_SECTION
            ],
            [
                'title'    => ListingWidget::RELATED_LISTINGS,
                'twigFile' => '/related-listings.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'cardType'   => Widget::VERTICAL_CARDS_TYPE,
                    'module'     => 'listing',
                    'fieldTitle' => '',
                    'columns'    => 3,
                    'custom'     => [
                        'order1'     => '',
                        'order2'     => '',
                        'quantity'   => 3,
                        'filter'     => 'category',
                        'level'      => []
                    ]
                ],
                'modal'    => 'edit-relatedlistings-modal',
                'section'  => ListingWidget::MAIN_SECTION
            ],
            [
                'title'    => ListingWidget::LINKED_LISTINGS,
                'twigFile' => '/linked-listings.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'fieldTitle'  => '',
                    'widgetTitle' => ''
                ],
                'modal'    => 'edit-linkedlistings-modal',
                'section'  => ListingWidget::MAIN_SECTION
            ],
            [
                'title'    => ListingWidget::SEPARATOR,
                'twigFile' => '/widget-separator.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [],
                'modal'    => '',
                'section'  => ListingWidget::MAIN_SECTION
            ],
            [
                'title'    => ListingWidget::LEADERBOARD,
                'twigFile' => '/banner.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'bannerType'      => 'leaderboard',
                    'isWide'          => 'false',
                    'banners'         => [
                        1 => 'leaderboard'
                    ],
                    'backgroundColor' => 'base'
                ],
                'modal'    => '',
                'section'  => ListingWidget::MAIN_SECTION
            ],
            [
                'title'    => ListingWidget::LARGE_MOBILE_BANNER,
                'twigFile' => '/banner.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'bannerType'      => 'large-mobile',
                    'isWide'          => 'false',
                    'banners'         => [
                        1 => 'largebanner',
                        2 => 'largebanner'
                    ],
                    'backgroundColor' => 'base'
                ],
                'modal'    => '',
                'section'  => ListingWidget::MAIN_SECTION
            ],
            [
                'title'    => ListingWidget::SQUARE_BANNER,
                'twigFile' => '/banner.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'bannerType'      => 'square',
                    'isWide'          => 'false',
                    'banners'         => [
                        1 => 'square',
                        2 => 'square',
                        3 => 'square'
                    ],
                    'backgroundColor' => 'base'
                ],
                'modal'    => '',
                'section'  => ListingWidget::MAIN_SECTION
            ],
            [
                'title'    => ListingWidget::SPONSORED_LINKS,
                'twigFile' => '/banner.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'bannerType'      => 'large-mobile',
                    'isWide'          => 'false',
                    'banners'         => [
                        1 => 'sponsor-links',
                        2 => 'sponsor-links'
                    ],
                    'backgroundColor' => 'base'
                ],
                'modal'    => '',
                'section'  => ListingWidget::MAIN_SECTION
            ],
            [
                'title'    => ListingWidget::HOURS,
                'twigFile' => '/hours.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [],
                'modal'    => '',
                'section'  => ListingWidget::MAIN_SECTION
            ],
            [
                'title'    => ListingWidget::RANGE,
                'twigFile' => '/range.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'fieldTitle' => '',
                    'hideTitle'  => 'false',
                    'icon'       => 'fa-usd',
                    'minRange'   => '1',
                    'maxRange'   => '5'
                ],
                'modal'    => 'edit-range-modal',
                'section'  => ListingWidget::MAIN_SECTION
            ],
            [
                'title'    => ListingWidget::SPECIALTIES,
                'twigFile' => '/specialties.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'fieldTitle'      => '',
                    'placeholder'     => '',
                    'required'    => 'true',
                    'dropdownOptions' => [
                        [
                            'value'   => '',
                        ]
                    ]
                ],
                'modal'    => 'edit-specialties-modal',
                'section'  => ListingWidget::MAIN_SECTION
            ]
        ];

        return $standardWidgets;
    }

    /**
     * Return standard detail sidebar Listing Widgets
     *
     * @return array
     */
    public function getDefaultDetailSidebarListingWidgets()
    {
        $standardWidgets = [
            [
                'title'    => ListingWidget::FACEBOOK_FEED,
                'twigFile' => '/facebook-feed.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [],
                'modal'    => '',
                'section'  => ListingWidget::SIDEBAR_SECTION
            ],
            [
                'title'    => ListingWidget::HOURS,
                'twigFile' => '/hours.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [],
                'modal'    => '',
                'section'  => ListingWidget::SIDEBAR_SECTION
            ],
            [
                'title'    => ListingWidget::LOCATION,
                'twigFile' => '/location.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [],
                'modal'    => '',
                'section'  => ListingWidget::SIDEBAR_SECTION
            ],
            [
                'title'    => ListingWidget::SOCIAL_BUTTONS,
                'twigFile' => '/social-buttons.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [],
                'modal'    => '',
                'section'  => ListingWidget::SIDEBAR_SECTION
            ],
            [
                'title'    => ListingWidget::SQUARE_BANNER,
                'twigFile' => '/banner.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'bannerType'      => 'square',
                    'isWide'          => 'false',
                    'banners'         => [
                        1 => 'square'
                    ],
                    'backgroundColor' => 'base'
                ],
                'modal'    => '',
                'section'  => ListingWidget::SIDEBAR_SECTION
            ],
            [
                'title'    => ListingWidget::WIDE_SKYSCRAPER_BANNER,
                'twigFile' => '/banner.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'bannerType'      => 'skyscraper',
                    'isWide'          => 'true',
                    'banners'         => [
                        1 => 'skyscraper'
                    ],
                    'backgroundColor' => 'base'
                ],
                'modal'    => '',
                'section'  => ListingWidget::SIDEBAR_SECTION
            ],
            [
                'title'    => ListingWidget::CALL_TO_ACTION,
                'twigFile' => '/call-to-action-button.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'fieldTitle'  => '',
                    'buttonStyle' => 'default',
                    'alignment'   => 'extended',
                    'buttonLabel' => '',
                    'required'    => 'true'
                ],
                'modal'    => 'edit-calltoaction-modal',
                'section'  => ListingWidget::SIDEBAR_SECTION
            ],
            [
                'title'    => ListingWidget::RANGE,
                'twigFile' => '/range.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'fieldTitle' => '',
                    'hideTitle'  => 'false',
                    'icon'       => 'fa-usd',
                    'minRange'   => '1',
                    'maxRange'   => '5'
                ],
                'modal'    => 'edit-range-modal',
                'section'  => ListingWidget::SIDEBAR_SECTION
            ],
            [
                'title'    => ListingWidget::DESCRIPTION,
                'twigFile' => '/description.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'fieldTitle'      => '',
                    'descriptionType' => 'short',
                    'placeholder'     => '',
                    'hideTitle'       => 'false',
                    'required'        => 'true'
                ],
                'modal'    => 'edit-description-modal',
                'section'  => ListingWidget::SIDEBAR_SECTION
            ],
            [
                'title'    => ListingWidget::SPECIALTIES,
                'twigFile' => '/specialties.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'fieldTitle'      => '',
                    'placeholder'     => '',
                    'required'    => 'true',
                    'dropdownOptions' => [
                        [
                            'value'   => '',
                        ]
                    ]
                ],
                'modal'    => 'edit-specialties-modal',
                'section'  => ListingWidget::SIDEBAR_SECTION
            ],
            [
                'title'    => ListingWidget::RELATED_LISTINGS,
                'twigFile' => '/related-listings.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'cardType'   => Widget::SIDEBAR_CARDS_TYPE,
                    'module'     => 'listing',
                    'fieldTitle' => '',
                    'custom'     => [
                        'order1'     => '',
                        'order2'     => '',
                        'quantity'   => 3,
                        'filter'     => 'category',
                        'level'      => []
                    ]
                ],
                'modal'    => 'edit-relatedlistings-modal',
                'section'  => ListingWidget::SIDEBAR_SECTION
            ],
            [
                'title'    => ListingWidget::LINKED_LISTINGS,
                'twigFile' => '/linked-listings.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'fieldTitle'  => '',
                    'widgetTitle' => ''
                ],
                'modal'    => 'edit-linkedlistings-modal',
                'section'  => ListingWidget::SIDEBAR_SECTION
            ],
            [
                'title'    => ListingWidget::SEPARATOR,
                'twigFile' => '/widget-separator.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [],
                'modal'    => '',
                'section'  => ListingWidget::SIDEBAR_SECTION
            ],
            [
                'title'    => ListingWidget::LARGE_MOBILE_BANNER,
                'twigFile' => '/banner.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'bannerType'      => 'large-mobile',
                    'isWide'          => 'false',
                    'banners'         => [
                        1 => 'largebanner',
                    ],
                    'backgroundColor' => 'base'
                ],
                'modal'    => '',
                'section'  => ListingWidget::SIDEBAR_SECTION
            ],
            [
                'title'    => ListingWidget::SPONSORED_LINKS,
                'twigFile' => '/banner.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'bannerType'      => 'large-mobile',
                    'isWide'          => 'false',
                    'banners'         => [
                        1 => 'sponsor-links'
                    ],
                    'backgroundColor' => 'base'
                ],
                'modal'    => '',
                'section'  => ListingWidget::SIDEBAR_SECTION
            ],
            [
                'title'    => ListingWidget::ADDITIONAL_INFORMATION,
                'twigFile' => '/additional-information.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [],
                'modal'    => '',
                'section'  => ListingWidget::SIDEBAR_SECTION
            ],
            [
                'title'    => ListingWidget::FEATURES,
                'twigFile' => '/feature.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [],
                'modal'    => '',
                'section'  => ListingWidget::SIDEBAR_SECTION
            ],
            [
                'title'    => ListingWidget::VIDEO,
                'twigFile' => '/video.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [],
                'modal'    => '',
                'section'  => ListingWidget::SIDEBAR_SECTION
            ],
            [
                'title'    => ListingWidget::CHECK_LIST,
                'twigFile' => '/check-list.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'fieldTitle'      => '',
                    'icon'            => 'fa-check',
                    'hideTitle'       => 'false',
                    'groupFields'     => [
                        [
                            'title'       => '',
                            'placeholder' => ''
                        ]
                    ]
                ],
                'modal'    => 'edit-checklist-modal',
                'section'  => ListingWidget::SIDEBAR_SECTION
            ],
            [
                'title'    => ListingWidget::MORE_DETAILS,
                'twigFile' => '/more-details.html.twig',
                'type'     => ListingWidget::DETAIL_TYPE,
                'content'  => [
                    'fieldTitle'      => '',
                    'required'    => 'true',
                    'groupFields'     => [
                        [
                            'title'       => '',
                            'placeholder' => ''
                        ]
                    ]
                ],
                'modal'    => 'edit-moredetails-modal',
                'section'  => ListingWidget::SIDEBAR_SECTION
            ]
        ];

        return $standardWidgets;
    }

    /**
     * @return array
     */
    public function getCustomWidgets(): array
    {
        return $this->customWidgets;
    }
}
