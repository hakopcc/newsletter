<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity;

use ArcaSolutions\WebBundle\Entity\Accountprofilecontact;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Answer
 *
 * @ORM\Table(name="Answer")
 * @ORM\Entity(repositoryClass="ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Repository\AnswerRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Answer
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
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    private $description;

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
     * @ORM\Column(name="upvotes", type="integer", nullable=false)
     */
    private $upvotes;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=2, nullable=false)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\WebBundle\Entity\Accountprofilecontact", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="account_id", onDelete="CASCADE")
     */
    private $account;

    /**
     * @ORM\ManyToOne(targetEntity="ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity\Question", inversedBy="answers")
     * @ORM\JoinColumn(name="question_id", referencedColumnName="id")
     */
    private $question;

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
     * @return Answer
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;

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
     * @return Answer
     */
    public function setDescription($description)
    {
        $this->description = $description;

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
     * @return Answer
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
     * @return Answer
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
     * @return Answer
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
     * @return Answer
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get question
     *
     * @return Question
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set question
     *
     * @param Question $question
     * @return Answer
     */
    public function setQuestion(Question $question = null)
    {
        $this->question = $question;

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
    }
}
