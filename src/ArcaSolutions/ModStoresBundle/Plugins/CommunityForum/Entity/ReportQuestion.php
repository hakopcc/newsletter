<?php


namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * ReportPost
 *
 * @ORM\Table(name="Report_Question", uniqueConstraints={@ORM\UniqueConstraint(name="report_info", columns={"question_id", "report_type", "date"})}, indexes={@ORM\Index(name="question_id", columns={"question_id"}), @ORM\Index(name="report_type", columns={"report_type"}), @ORM\Index(name="date", columns={"date"})})
 * @ORM\Entity
 */
class ReportQuestion
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
     * @ORM\Column(name="question_id", type="integer", nullable=false)
     */
    private $questionId = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="report_type", type="integer", nullable=false)
     */
    private $reportType = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="report_amount", type="integer", nullable=false)
     */
    private $reportAmount = '0';

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=false)
     */
    private $date = '0000-00-00';



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
     * Set postId
     *
     * @param integer $postId
     * @return ReportQuestion
     */
    public function setQuestionId($questionId)
    {
        $this->questionId = $questionId;

        return $this;
    }

    /**
     * Get postId
     *
     * @return integer
     */
    public function getQuestionId()
    {
        return $this->questionId;
    }

    /**
     * Set reportType
     *
     * @param integer $reportType
     * @return ReportQuestion
     */
    public function setReportType($reportType)
    {
        $this->reportType = $reportType;

        return $this;
    }

    /**
     * Get reportType
     *
     * @return integer
     */
    public function getReportType()
    {
        return $this->reportType;
    }

    /**
     * Set reportAmount
     *
     * @param integer $reportAmount
     * @return ReportQuestion
     */
    public function setReportAmount($reportAmount)
    {
        $this->reportAmount = $reportAmount;

        return $this;
    }

    /**
     * Get reportAmount
     *
     * @return integer
     */
    public function getReportAmount()
    {
        return $this->reportAmount;
    }

    /**
     * Set date
     *
     * @param DateTime $date
     * @return ReportQuestion
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}
