<?php

namespace ArcaSolutions\WysiwygBundle\Entity;

use ArcaSolutions\ListingBundle\Entity\ListingTField;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * ListingWidget
 *
 * @ORM\Table(name="ListingWidget")
 * @ORM\Entity(repositoryClass="ArcaSolutions\WysiwygBundle\Repository\ListingWidgetRepository")
 */
class ListingWidget
{
    /**
     * Types
     */
    const DETAIL_TYPE     = 'detail';

    /**
     * Sections
     */
    const MAIN_SECTION    = 'main';
    const SIDEBAR_SECTION = 'sidebar';
    const HEADER_SECTION  = 'header';

    /**
     * Main Listing Widgets
     */
    const ABOUT                  = 'About';
    const REVIEWS_PAGINATED       = 'Reviews Paginated';
    const PHOTO_GALLERY          = 'Photo Gallery';
    const RECENT_REVIEWS          = 'Recent Reviews';
    const ASSOCIATED_DEALS       = 'Associated Deals';
    const ASSOCIATED_CLASSIFIEDS = 'Associated Classifieds';

    /**
     * Sidebar Listing Widgets
     */
    const FACEBOOK_FEED          = 'Social Feed';
    const LOCATION               = 'Location';
    const SOCIAL_BUTTONS         = 'Social Buttons';
    const WIDE_SKYSCRAPER_BANNER = 'Wide Skyscraper Banner';

    /**
     * Multi section Widgets (main and sidebar)
     */
    const CALL_TO_ACTION         = 'Call to Action';
    const DESCRIPTION            = 'Description';
    const ADDITIONAL_INFORMATION = 'Additional Information';
    const FEATURES               = 'Features';
    const VIDEO                  = 'Video';
    const CHECK_LIST             = 'Check List';
    const MORE_DETAILS           = 'More details';
    const HOURS                  = 'Hours';
    const RANGE                  = 'Range';
    const SPECIALTIES            = 'Specialties';
    const RELATED_LISTINGS       = 'Related Listings';
    const LINKED_LISTINGS        = 'Linked Listings';
    const SEPARATOR              = 'Separator';
    const LEADERBOARD            = 'Leaderboard Banner';
    const LARGE_MOBILE_BANNER    = 'Large Mobile Banner';
    const SQUARE_BANNER          = 'Square Banner';
    const SPONSORED_LINKS        = 'Sponsored Links';
    /**
     * Header Listing Widgets
     */
    const HEADER                 = 'Header';
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="twig_file", type="string", nullable=false)
     */
    private $twigFile;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="`type`", type="string", nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="`modal`", type="string", nullable=true)
     */
    private $modal;

    /**
     * @var string
     *
     * @ORM\Column(name="section", type="string", nullable=false, options={"default"="main"})
     */
    private $section = self::MAIN_SECTION;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTwigFile()
    {
        return $this->twigFile;
    }

    /**
     * @param string $twigFile
     */
    public function setTwigFile($twigFile)
    {
        $this->twigFile = $twigFile;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getModal()
    {
        return $this->modal;
    }

    /**
     * @param string $modal
     */
    public function setModal($modal)
    {
        $this->modal = $modal;
    }


    /**
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @param string $section
     */
    public function setSection(string $section)
    {
        $this->section = $section;
    }
}
