<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity;

use ArcaSolutions\WebBundle\Entity\Accountprofilecontact;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Question
 *
 * @ORM\Table(name="Question")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Question
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="account_id", type="integer", nullable=true)
     */
    private $accountId;

    /**
     * @var integer
     *
     * @ORM\Column(name="number_views", type="integer", nullable=false)
     */
    private $numberViews = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="friendly_url", type="string", length=255, nullable=true)
     */
    private $friendlyUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="keywords", type="text", nullable=true)
     */
    private $keywords;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="entered", type="datetime", nullable=true)
     */
    private $entered;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @var integer
     *
     * @ORM\Column(name="upvotes", type="integer", nullable=true)
     */
    private $upvotes;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=2, nullable=false)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity\Answer", mappedBy="question")
     * @ORM\OrderBy({"entered" = "ASC"})
     * @Serializer\Type("array")
     */
    private $answers;

    /**
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\WebBundle\Entity\Accountprofilecontact", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="account_id", onDelete="CASCADE")
     */
    private $account;

    /**
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity\QuestionCategory")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="fulltextsearch_keyword", type="text", nullable=true)
     */
    private $fulltextsearchKeyword;


    /**
     * Get fulltextsearchKeyword
     *
     * @return string
     */
    public function getFulltextsearchKeyword()
    {
        return $this->fulltextsearchKeyword;
    }

    /**
     * Set fulltextsearchKeyword
     * @param string $fulltextsearchKeyword
     * @return Question
     */
    public function setFulltextsearchKeyword($fulltextsearchKeyword)
    {
        $this->fulltextsearchKeyword = $fulltextsearchKeyword;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get numberViews
     *
     * @return integer
     */
    public function getNumberViews()
    {
        return $this->numberViews;
    }

    /**
     * Get accountId
     *
     * @return integer
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * Set accountId
     *
     * @param integer $accountId
     * @return Question
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Question
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get friendlyUrl
     *
     * @return string
     */
    public function getFriendlyUrl()
    {
        return $this->friendlyUrl;
    }

    /**
     * Set friendlyUrl
     *
     * @param string $friendlyUrl
     * @return Question
     */
    public function setFriendlyUrl($friendlyUrl)
    {
        $this->friendlyUrl = $friendlyUrl;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Question
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get category
     *
     * @return string
     */
    public function getKeywords()
    {
        return '';
    }

    /**
     * Set keywords
     *
     * @param string $keywords
     * @return Question
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * Get entered
     *
     * @return DateTime
     */
    public function getEntered()
    {
        return $this->entered;
    }

    /**
     * Set entered
     *
     * @param DateTime $entered
     * @return Question
     */
    public function setEntered($entered)
    {
        $this->entered = $entered;

        return $this;
    }

    /**
     * Get updated
     *
     * @return DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set updated
     *
     * @param DateTime $updated
     * @return Question
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get upvotes
     *
     * @return integer
     */
    public function getUpvotes()
    {
        return $this->upvotes;
    }

    /**
     * Set upvotes
     *
     * @param integer $upvotes
     * @return Question
     */
    public function setUpvotes($upvotes)
    {
        $this->upvotes = $upvotes;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Question
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get category
     *
     * @return QuestionCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set category
     *
     * @param QuestionCategory $category
     * @return Question
     */
    public function setCategory(QuestionCategory $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Accountprofilecontact
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param Accountprofilecontact $account
     */
    public function setAccount(Accountprofilecontact $account = null)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * @param mixed $answers
     * @return Question
     */
    public function setAnswers($answers)
    {
        $this->answers = $answers;

        return $this;
    }
}
