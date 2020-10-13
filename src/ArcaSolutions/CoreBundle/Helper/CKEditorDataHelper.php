<?php


namespace ArcaSolutions\CoreBundle\Helper;


use Exception;
use RuntimeException;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CKEditorDataHelper
{
    /** @var ContainerInterface $_container */
    private $_container;

    /** @var Logger $_logger */
    private $_logger;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->_container = $container;
    }

    /**
     * @return Logger|null
     */
    private function &getLogger()
    {
        if($this->_container !== null) {
            if($this->_logger === null) {
                $loggerFromContainer = $this->_container->get('logger');
                if($loggerFromContainer!==null && is_a($loggerFromContainer, Logger::class)){
                    $this->_logger = $loggerFromContainer;
                }
                unset($loggerFromContainer);
            }
        } else {
            $this->_logger = null;
        }
        return $this->_logger;
    }

    /**
     * @param string $ckeditorGeneratedRawHtmlString
     * @return string
     */
    public function cleanupCKEditorGeneratedRawHtmlString($ckeditorGeneratedRawHtmlString)
    {
        $returnValue = $ckeditorGeneratedRawHtmlString;
        try {
            $returnValue = $this->removeNonBreakingSpacesFromParagraphs($returnValue);
            $returnValue = $this->addTextAlignLeftAsDefaultParagraphsTextAlignment($returnValue);
        } catch (Exception $e) {
            $errorMsg = 'Unexpected error on method cleanupCKEditorGeneratedRawHtmlString in CKEditorDataHelper.php';
            $logger = &$this->getLogger();
            if($logger !== null){
                $logger->critical($errorMsg, array('exception'=>$e));
            } else {
                throw new RuntimeException($errorMsg,0,$e);
            }
            unset($errorMsg);
        } finally {
            return $returnValue;
        }
    }

    /**
     * @param string $ckeditorGeneratedRawHtmlString
     * @return string
     */
    public function removeNonBreakingSpacesFromParagraphs($ckeditorGeneratedRawHtmlString)
    {
        $returnValue = $ckeditorGeneratedRawHtmlString;
        try {
            $removeNonBreakingSpacesOnBegginingOfNonEmptyParagraphsRegex = '/([<])(p[^>]*)([>])(&nbsp;)(?!\1)(.*)/m';
            $removeNonBreakingSpacesOnBegginingOfNonEmptyParagraphsSubst = '$1$2$3$5';
            $removeNonBreakingSpacesOnMiddleOfNonEmptyParagraphsRegex = '/([^>])(&nbsp;)([^<])(?=[^<]*\<\/p)/m';
            $removeNonBreakingSpacesOnMiddleOfNonEmptyParagraphsSubst = '$1 $3';
            $returnValue = preg_replace($removeNonBreakingSpacesOnBegginingOfNonEmptyParagraphsRegex, $removeNonBreakingSpacesOnBegginingOfNonEmptyParagraphsSubst, $returnValue);
            $returnValue = preg_replace($removeNonBreakingSpacesOnMiddleOfNonEmptyParagraphsRegex, $removeNonBreakingSpacesOnMiddleOfNonEmptyParagraphsSubst, $returnValue);
        } catch (Exception $e) {
            $errorMsg = 'Unexpected error on method removeNonBreakingSpacesFromParagraphs in CKEditorDataHelper.php';
            $logger = &$this->getLogger();
            if($logger !== null){
                $logger->critical($errorMsg, array('exception'=>$e));
            } else {
                throw new RuntimeException($errorMsg,0,$e);
            }
            unset($errorMsg);
        } finally {
            return $returnValue;
        }
    }

    /**
     * @param string $ckeditorGeneratedRawHtmlString
     * @return string
     */
    public function addTextAlignLeftAsDefaultParagraphsTextAlignment($ckeditorGeneratedRawHtmlString)
    {
        $returnValue = $ckeditorGeneratedRawHtmlString;
        try {
            $addTextAlignLeftAsDefaultParagraphAlignmentWhenStyleExistsRegex = '/(\<p[^>]*style\=)([\'"])(?![^\'"<>=]*[^\-a-zA-Z0-9]?text-align\:)/m';
            $addTextAlignLeftAsDefaultParagraphAlignmentWhenStyleExistsSubst = '$1$2text-align:left;';
            $addTextAlignLeftAsDefaultParagraphAlignmentWhenStyleNotExistsRegex = '/(\<[\s\t\n\r]*p)(([^a-zA-Z0-9]|[\s\t\n\r])[\s\t\n\r]*)(?![^<>]*style\=[^<>]*)/m';
            $addTextAlignLeftAsDefaultParagraphAlignmentWhenStyleNotExistsSubst = '$1 style="text-align:left;"$2';
            $returnValue = preg_replace($addTextAlignLeftAsDefaultParagraphAlignmentWhenStyleExistsRegex, $addTextAlignLeftAsDefaultParagraphAlignmentWhenStyleExistsSubst, $returnValue);
            $returnValue = preg_replace($addTextAlignLeftAsDefaultParagraphAlignmentWhenStyleNotExistsRegex, $addTextAlignLeftAsDefaultParagraphAlignmentWhenStyleNotExistsSubst, $returnValue);
        } catch (Exception $e) {
            $errorMsg = 'Unexpected error on method addTextAlignLeftAsDefaultParagraphsTextAlignment in CKEditorDataHelper.php';
            $logger = &$this->getLogger();
            if($logger !== null){
                $logger->critical($errorMsg, array('exception'=>$e));
            } else {
                throw new RuntimeException($errorMsg,0,$e);
            }
            unset($errorMsg);
        } finally {
            return $returnValue;
        }
    }
}
