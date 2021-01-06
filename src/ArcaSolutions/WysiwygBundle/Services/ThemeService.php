<?php

namespace ArcaSolutions\WysiwygBundle\Services;

use ArcaSolutions\WysiwygBundle\Entity\Theme;
use ArcaSolutions\WysiwygBundle\Entity\Widget;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Wysiwyg
 *
 * This service handles everything but RENDERING that has something to do with Wysiwyg
 * Create, Edit, Delete pages and their widgets
 * Retrieving the data from DB, saving data in DB.
 *
 */
class ThemeService
{
    /**
     * ContainerInterface
     *
     * @var ContainerInterface
     */
    protected $container;

    private $theme = Theme::DEFAULT_THEME;

    /**
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @param string $theme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    /**
     * Return the system selected theme as an Entity
     *
     * @return Theme
     */
    public function getSelectedTheme()
    {
        $templateName = $this->container->get('multi_domain.information')->getTemplate();

        return $this->container->get('doctrine')->getRepository('WysiwygBundle:Theme')->findOneBy([
            'title' => ucfirst($templateName),
        ]);
    }

    /**
     * Returns the common widgets to all themes
     *
     * @return array
     */
    public function getCommonThemeWidgets()
    {
        $widgets = [
            Widget::SEARCH_BOX,
            Widget::LEADER_BOARD_AD_BAR,
            Widget::THREE_RECTANGLE_AD_BAR,
            Widget::UPCOMING_EVENTS_CAROUSEL,
            Widget::BROWSE_BY_LOCATION,
            Widget::SPONSORED_LINKS,
            Widget::DOWNLOAD_OUR_APPS_BAR,
            Widget::SEARCH_BAR,
            Widget::HORIZONTAL_CARDS,
            Widget::VERTICAL_CARDS,
            Widget::VERTICAL_CARD_PLUS_HORIZONTAL_CARDS,
            Widget::TWO_COLUMNS_HORIZONTAL_CARDS,
            Widget::CENTRALIZED_HIGHLIGHTED_CARD,
            Widget::UPCOMING_EVENTS,
            Widget::RESULTS_INFO,
            Widget::RESULTS,
            Widget::LISTING_DETAIL,
            Widget::EVENT_DETAIL,
            Widget::CLASSIFIED_DETAIL,
            Widget::ARTICLE_DETAIL,
            Widget::DEAL_DETAIL,
            Widget::BLOG_DETAIL,
            Widget::CONTACT_FORM,
            Widget::FAQ_BOX,
            Widget::SECTION_HEADER,
            Widget::CUSTOM_CONTENT,
            Widget::PRICING_AND_PLANS,
            Widget::ALL_LOCATIONS,
            Widget::REVIEWS_BLOCK,
            Widget::HEADER,
            Widget::HEADER_WITH_CONTACT_PHONE,
            Widget::NAVIGATION_WITH_LEFT_LOGO_PLUS_SOCIAL_MEDIA,
            Widget::NAVIGATION_WITH_CENTERED_LOGO,
            Widget::FOOTER,
            Widget::FOOTER_WITH_NEWSLETTER,
            Widget::FOOTER_WITH_LOGO,
            Widget::FOOTER_WITH_SOCIAL_MEDIA,
            Widget::EVENTS_CALENDAR,
            Widget::RECENT_REVIEWS,
            Widget::RECENT_MEMBERS,
            WIdget::LISTING_PRICES,
            Widget::EVENT_PRICES,
            Widget::CLASSIFIED_PRICES,
            Widget::BANNER_PRICES,
            Widget::ARTICLE_PRICES,
            Widget::NEWSLETTER,
            Widget::VIDEO_GALLERY,
            Widget::LEAD_FORM,
            Widget::SOCIAL_NETWORK_BAR,
            Widget::CONTACT_INFORMATION_BAR,
            Widget::CALL_TO_ACTION,
            Widget::SLIDER,
            Widget::TWO_COLUMNS_RECENT_POSTS,
            Widget::FEATURED_CATEGORIES_WITH_IMAGES,
            Widget::ALL_CATEGORIES,
            Widget::FEATURED_CATEGORIES_WITH_IMAGES_TYPE_2,
            Widget::FEATURED_CATEGORIES,
            Widget::FEATURED_CATEGORIES_WITH_ICONS,
            Widget::FEATURED_CATEGORIES_TYPE_2,
            Widget::RECENT_ARTICLES_PLUS_POPULAR_ARTICLES,
            Widget::ONE_HORIZONTAL_CARD,
            Widget::THREE_VERTICAL_CARDS,
            Widget::LIST_OF_HORIZONTAL_CARDS,

            /*
             * CUSTOM ADDWIDGET
             * here are an example of how you add the widget 'Widget test' for all themes
             * if you need that 'Widget test' to be available only for a specific theme you have
             * to remove it from here and add at the right function below
             */
            /* $trans->trans('Widget test', [], 'widgets', 'en'), */

            Widget::FOOTER_WITH_NEWSLETTER_TYPE_2,
            Widget::HEADER_WITH_SEARCH,
            Widget::FEATURED_CATEGORIES_WITH_IMAGES_TYPE_3,
            Widget::RECENT_ARTICLES_SLIDER,
            Widget::BILLBOARD_AD_BAR,
        ];

        /* ModStores Hooks */
        HookFire("themeservice_after_add_commonwidgets", [
            "widget" => &$widgets
        ]);

        return $widgets;
    }

    /**
     *  CUSTOM ADDTHEME
     *  here are an example of you add all the common widgets and the specific widgets to the Test Theme
     */
    /*public function getTestThemeWidgets()
    {
        $trans = $this->container->get('translator');

        return array_merge($this->getCommonThemeWidgets(), [
            $trans->trans('Widget test', [], 'widgets', 'en'),
        ]);
    }*/

    //region Default Widgets of each page by theme
    /**
     * Each function of this region returns the widgets ordered of each page
     * The widgets returned are different for each theme
     * ALERT: DO NOT CHANGE THE FUNCTIONS NAME
     * They have to match its own PageType constant plus 'DefaultWidgets' for the reset feature at sitemgr
     * Ex:  constant HOME_PAGE
     *      function getHomePageDefaultWidgets()
     */

    /**
     * Returns the commons and the Default Theme widgets
     *
     * @return array
     */
    public function getDefaultThemeWidgets()
    {
        $trans = $this->container->get('translator');

        return array_merge($this->getCommonThemeWidgets(), [
            /*
             * CUSTOM ADDWIDGET
             * here are an example of how you add the widget 'Widget test' for Default theme
             * if you need that 'Widget test' to be available for all themes you have
             * to remove it from here and add at the right function above
             *
             * $trans->trans('Widget test', [], 'widgets', 'en'),*/
        ]);
    }

    /**
     * Returns the commons and the Doctor Theme widgets
     *
     * @return array
     */
    public function getDoctorThemeWidgets()
    {
        $trans = $this->container->get('translator');

        return array_merge($this->getCommonThemeWidgets(), []);
    }

    /**
     * Returns the commons and the Restaurant Theme widgets
     *
     * @return array
     */
    public function getRestaurantThemeWidgets()
    {
        $trans = $this->container->get('translator');

        return array_merge($this->getCommonThemeWidgets(), []);
    }

    /**
     * Returns the commons and the Wedding Theme widgets
     *
     * @return array
     */
    public function getWeddingThemeWidgets()
    {
        $trans = $this->container->get('translator');

        return array_merge($this->getCommonThemeWidgets(), []);
    }

}
