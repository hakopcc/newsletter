<?php
namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Services;

use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity\Answer;
use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity\Question;
use ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity\ReportQuestion;
use ArcaSolutions\ReportsBundle\Services\ReportHandler;
use ArcaSolutions\SearchBundle\Services\ParameterHandler;
use DateTime;
use Exception;

class ReportHandlerWithCommunityForum extends ReportHandler
{
    const QUESTION_SUMMARY = 1;
    const QUESTION_DETAIL = 2;

    /**
     * Increase column of number view in every module's table
     *
     * @param Question | Answer $item
     *
     * @throws Exception
     */
    private function increaseNumberViews($item, $module)
    {
        $metadata = $this->container->get('doctrine')->getManager()
            ->getClassMetadata(get_class($item));

        $connection = $this->container->get('doctrine.dbal.domain_connection');

        $connection->update($metadata->getTableName(), [
            'number_views' => $item->getNumberViews()+1
        ], ['id' => $item->getId()]);

        $this->container->get($module.".synchronization")->addUpsert($item->getId());
    }

    public function addQuestionReport($questionId, $type, $amount = 1)
    {
        $return = false;

        if (!$this->container->get("utility")->isRobotUser()) {
            try {
                $doctrine = $this->container->get("doctrine");

                $repository = $doctrine->getRepository("CommunityForumBundle:ReportQuestion");

                $date = new DateTime();

                $report = $repository->findOneBy([
                    "questionId"     => $questionId,
                    "date"       => new DateTime(),
                    "reportType" => $type
                ]);

                if ($report) {
                    $report->setReportAmount($report->getReportAmount() + $amount);
                } else {
                    $report = new ReportQuestion();
                    $report->setQuestionId($questionId);
                    $report->setReportType($type);
                    $report->setReportAmount($amount);
                    $report->setDate($date);
                }

                /* Increases column of number views in module's table if it's detail page */
                if ($type == $this::POST_DETAIL) {
                    $this->increaseNumberViews($doctrine->getRepository('CommunityForumBundle:Question')->find($questionId), 'question');
                }

                $doctrine->getManager()->persist($report);
                $doctrine->getManager()->flush($report);

                $return = true;
            } catch (Exception $e) {
                $this->container->get("logger")->error("Unable to create forum question report.", ["Exception" => $e]);
            }
        }

        return $return;
    }

}
