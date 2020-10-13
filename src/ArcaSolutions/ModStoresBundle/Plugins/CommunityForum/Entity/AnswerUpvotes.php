<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AnswerUpvotes
 *
 * @ORM\Table(name="AnswerUpvotes")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class AnswerUpvotes
{
    /**
     * @var integer
     *
     * @ORM\Column(name="answer", type="integer", nullable=true)
     */
    private $answer;

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
     * Get answer
     *
     * @return integer
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * Set answer
     *
     * @param integer $answer
     * @return AnswerUpvotes
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;

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
     * @return AnswerUpvotes
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
