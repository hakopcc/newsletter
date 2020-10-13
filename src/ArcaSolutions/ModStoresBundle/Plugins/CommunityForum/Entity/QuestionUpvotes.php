<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * QuestionUpvotes
 *
 * @ORM\Table(name="QuestionUpvotes")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class QuestionUpvotes
{
    /**
     * @var integer
     *
     * @ORM\Column(name="question", type="integer", nullable=true)
     */
    private $question;

    /**
     * @var integer
     *
     * @ORM\Column(name="account_id", type="integer", nullable=true)
     */
    private $accountId;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Get question
     *
     * @return integer
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set question
     *
     * @param integer $question
     * @return QuestionUpvotes
     */
    public function setQuestion($question)
    {
        $this->question = $question;

        return $this;
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
     * @return QuestionUpvotes
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;

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
}
