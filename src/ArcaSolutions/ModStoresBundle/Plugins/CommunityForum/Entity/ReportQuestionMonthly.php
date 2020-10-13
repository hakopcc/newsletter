<?php


namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * ReportPostMonthly
 *
 * @ORM\Table(name="Report_Question_Monthly")
 * @ORM\Entity
 */
class ReportQuestionMonthly
{
    /**
     * @var integer
     *
     * @ORM\Column(name="question_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $questionId = '0';

    /**
     * @var DateTime
     *
     * @ORM\Column(name="day", type="date", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $day;

    /**
     * @var integer
     *
     * @ORM\Column(name="summary_view", type="integer", nullable=false)
     */
    private $summaryView = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="detail_view", type="integer", nullable=false)
     */
    private $detailView = '0';



    /**
     * Set questionId
     *
     * @param integer $questionId
     * @return ReportQuestionMonthly
     */
    public function setQuestionId($questionId)
    {
        $this->questionId = $questionId;

        return $this;
    }

    /**
     * Get questionId
     *
     * @return integer
     */
    public function getQuestionId()
    {
        return $this->questionId;
    }

    /**
     * Set day
     *
     * @param DateTime $day
     * @return ReportQuestionMonthly
     */
    public function setDay($day)
    {
        $this->day = $day;

        return $this;
    }

    /**
     * Get day
     *
     * @return DateTime
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * Set summaryView
     *
     * @param integer $summaryView
     * @return ReportQuestionMonthly
     */
    public function setSummaryView($summaryView)
    {
        $this->summaryView = $summaryView;

        return $this;
    }

    /**
     * Get summaryView
     *
     * @return integer
     */
    public function getSummaryView()
    {
        return $this->summaryView;
    }

    /**
     * Set detailView
     *
     * @param integer $detailView
     * @return ReportQuestionMonthly
     */
    public function setDetailView($detailView)
    {
        $this->detailView = $detailView;

        return $this;
    }

    /**
     * Get detailView
     *
     * @return integer
     */
    public function getDetailView()
    {
        return $this->detailView;
    }
}
