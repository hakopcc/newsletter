<?php
namespace ArcaSolutions\WebBundle\Services;

use ArcaSolutions\CoreBundle\Services\LanguageHandler;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use ArcaSolutions\MultiDomainBundle\Services\Settings as DomainSettings;
use ArcaSolutions\CoreBundle\Services\Settings as MainSettings;
use ArcaSolutions\WebBundle\Entity\DiscountCode;
use Doctrine\ORM\EntityRepository;
use Exception;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use ArcaSolutions\CoreBundle\Str;

/**
 * Class DiscountCodeService
 * @package ArcaSolutions\WebBundle\Services
 */
class DiscountCodeService
{
    /**
     * @var ContainerInterface $container
     */
    private $container;
    /**
     * @var DoctrineRegistry $doctrine
     */
    private $doctrine;

    /**
     * @var TranslatorInterface $translator
     */
    private $translator;

    /**
     * @var string $lang
     */
    private $lang;

    /**
     * @var Logger $logger
     */
    private $logger;

    /**
     * @var string[]
     */
    private $allowedObjClassNames;

    /**
     * @var string[]
     */
    private $allowedObjClassPaymentLogEntityRepositories;

    /**
     * @var string[]
     */
    private $allowedObjClassInvoiceEntityRepositories;

    /**
     * Checks if current loaded page is from sitemgr area
     *
     * @return boolean
     */
    protected function isSitemgr()
    {
        $request = Request::createFromGlobals();

        $alias = $this->container->getParameter('alias_sitemgr_module');

        // verify if sitemgr alias from real URL as well as http referer in case of ajax request
        return (
            (strpos($request->getUri(), $alias) !== false) ||
            ($request->isXmlHttpRequest() === true && strpos($request->server->get('HTTP_REFERER'), $alias) !== false)
        );
    }

    /**
     * DiscountCodeService constructor.
     * @param ContainerInterface $container
     * @param DoctrineRegistry $doctrine
     * @param TranslatorInterface $translator
     * @param LanguageHandler $languageHandler
     * @param DomainSettings $domainSettings
     * @param MainSettings $mainSettings
     */
    public function __construct(ContainerInterface $container, DoctrineRegistry $doctrine, TranslatorInterface $translator, LanguageHandler $languageHandler, DomainSettings $domainSettings, MainSettings $mainSettings, Logger $logger)
    {
        $this->container = $container;
        $this->doctrine = $doctrine;
        $this->translator = $translator;
        $this->logger = $logger;
        $this->allowedObjClassNames = array('Listing','Banner','Article','Event','Classified');
        $this->allowedObjClassPaymentLogEntityRepositories = array(
            'Listing'=>'ListingBundle:PaymentListingLog',
            'Banner'=>'BannersBundle:PaymentBannerLog',
            'Article'=>'ArticleBundle:PaymentArticleLog',
            'Event'=>'EventBundle:PaymentEventLog',
            'Classified'=>'ClassifiedBundle:PaymentClassifiedLog'
        );
        $this->allowedObjClassInvoiceEntityRepositories = array(
            'Listing'=>'ListingBundle:InvoiceListing',
            'Banner'=>'BannersBundle:InvoiceBanner',
            'Article'=>'ArticleBundle:InvoiceArticle',
            'Event'=>'EventBundle:InvoiceEvent',
            'Classified'=>'ClassifiedBundle:InvoiceClassified'
        );

        $this->lang = 'en';
        if ($languageHandler !== null) {
            $locale = null;
            if (!$this->isSitemgr()) {
                $locale = $domainSettings->getLocale();
            } else {
                $locale = $mainSettings->getSetting('sitemgr_language');
            }
            $this->lang = $languageHandler->getISOLang($locale);
            unset($locale);
        }
    }

    /**
     * @param $discountId
     * @param mixed $itemObj
     * @param $message
     * @param $errorNum
     * @return bool
     * @throws Exception
     */
    function discountCodeIsValidById($discountId, $itemObj, &$message, &$errorNum) {
        $returnValue = false;
        try {
            if (!$discountId && $discountId !== "0") {
                $returnValue = true;
            } else {
                /** @var EntityRepository $listingTemplateRepository */
                $discountCodeRepository = $this->doctrine->getRepository('WebBundle:DiscountCode');
                if ($discountCodeRepository !== null) {
                    /** @var DiscountCode $discountCode */
                    $discountCode = $discountCodeRepository->find($discountId);
                    if ($discountCode !== null) {
                        $returnValue = $this->discountCodeIsValid($discountCode, $itemObj, $message, $errorNum);
                    }
                } else {
                    $errorNum = 5;
                    $message .= "&#149;&nbsp;" . $this->translator->trans('Internal system error', array(), 'system', $this->lang);
                    $this->logger->critical('Cannot get DiscountCode Repository on discountCodeIsValidById method in DiscountCodeService.php');
                }
            }
        } catch (Exception $e) {
            $this->logger->critical('Unexpected error on discountCodeIsValidById method of DiscountCodeService.php', ['exception' => $e]);
            throw $e;
        } finally {
            unset($discountCodeRepository, $discountCode);
        }
        return $returnValue;
    }

    /**
     * @param DiscountCode $discountCode
     * @param mixed $itemObj
     * @param $message
     * @param $errorNum
     * @return bool
     * @throws Exception
     */
    function discountCodeIsValid(DiscountCode $discountCode, $itemObj, &$message, &$errorNum) {
        $returnValue = true;
        try {
            $itemObjClassName = get_class($itemObj);

            $discountcodeLabelConstantValue = constant('DISCOUNTCODE_LABEL');
            if($discountcodeLabelConstantValue === null) {
                $discountcodeLabelConstantValue = 'promotional code';//Consider the default value for this; Layout works for: "discount code" and "promotional code" (available to any label)
            }
            $labelDiscountCode = Str::ucwords($discountcodeLabelConstantValue);

            $discountCodeId = $discountCode->getId();
            $discountIdHtmlEntities = Str::htmlEntities($discountCodeId);

            if (!in_array($itemObjClassName,$this->allowedObjClassPaymentLogEntityRepositories,true) || !method_exists($itemObj,'getId')){
                $errorNum = 6;
                $message .= '&#149;&nbsp;' . /** @Ignore */$this->translator->trans('Internal system error', array(), 'system', $this->lang);
                $this->logger->critical('Unsupported item type received by discountCodeIsValid method in DiscountCodeService.php');
                $returnValue = false;
            } else {
                $methodNameToGetEnabledForObjectType = 'get'.$itemObjClassName;
                if ((strlen($discountCodeId) <= 0)) {
                    $errorNum = 1;
                    $message .= '&#149;&nbsp;' . /** @Ignore */$this->translator->trans('Inexistent ' . $labelDiscountCode, array(), null, $this->lang) . ' "<b>' . $discountIdHtmlEntities . '</b>".';
                    $returnValue = false;
                } else {
                    $translatedLabelDiscountCode = /** @Ignore */$this->translator->trans($labelDiscountCode, array(), null, $this->lang);
                    if ($discountCode->getStatus() !== 'A') {
                        $errorNum = 2;
                        $message .= '&#149;&nbsp;' . $translatedLabelDiscountCode . ' "<b>' . $discountIdHtmlEntities . '</b>" ' . /** @Ignore */$this->translator->trans('is not available.', array(), null, $this->lang);
                        $returnValue = false;
                    } elseif ($discountCode->$methodNameToGetEnabledForObjectType() !== "on") {
                        $errorNum = 3;
                        $message .= '&#149;&nbsp;' . $translatedLabelDiscountCode . ' "<b>' . $discountIdHtmlEntities . '</b>" ' .  /** @Ignore */$this->translator->trans('is not available for this item type.', array(), null, $this->lang);
                        $returnValue = false;
                    } elseif (($discountCode->getRecurring() === 'no') && ($itemObj->getId() > 0)) {
                        $itemObjPaymentLogEntityName = $this->allowedObjClassPaymentLogEntityRepositories[$itemObjClassName];
                        /** @var EntityRepository $itemObjPaymentLogRepository */
                        $itemObjPaymentLogRepository = $this->doctrine->getRepository($itemObjPaymentLogEntityName);
                        $itemObjInvoiceEntityName = $this->allowedObjClassInvoiceEntityRepositories[$itemObjClassName];
                        /** @var EntityRepository $itemObjInvoiceRepository */
                        $itemObjInvoiceRepository = $this->doctrine->getRepository($itemObjInvoiceEntityName);
                        if($itemObjPaymentLogRepository===null || $itemObjInvoiceRepository === null){
                            $errorNum = 5;
                            $message .= "&#149;&nbsp;" . $this->translator->trans('Internal system error', array(), 'system', $this->lang);
                            $this->logger->critical('Cannot get '. $itemObjPaymentLogEntityName .' or ' . $itemObjInvoiceEntityName . ' repository on discountCodeIsValid method in DiscountCodeService.php');
                            $returnValue = false;
                        } else {
                            $itemObjItemObjIdField = Str::lower($itemObjClassName) . 'Id';
                            $itemObjId = $itemObj->getId();
                            $itemObjPaymentLogCount = $itemObjPaymentLogRepository->count(array(
                                'discountId' => $discountCodeId,
                                $itemObjItemObjIdField => $itemObjId
                            ));
                            $itemObjInvoiceCount = $itemObjInvoiceRepository->count(array(
                                'discountId' => $discountCodeId,
                                $itemObjItemObjIdField => $itemObjId
                            ));

                            if($itemObjPaymentLogCount >= 1 || $itemObjInvoiceCount >= 1){
                                $errorNum = 4;
                                $message .= "&#149;&nbsp;" . $translatedLabelDiscountCode . " <strong>" . $discountIdHtmlEntities . "</strong> " . /** @Ignore */$this->translator->trans("cannot be used twice.", array(), null, $this->lang);
                                $returnValue = false;
                            }
                            unset($itemObjItemObjIdField,
                                $itemObjId,
                                $itemObjPaymentLogCount,
                                $itemObjInvoiceCount);
                        }
                        unset($itemObjPaymentLogEntityName,
                            $itemObjPaymentLogRepository,
                            $itemObjInvoiceEntityName,
                            $itemObjInvoiceRepository);
                    }
                    unset($translatedLabelDiscountCode);
                }
                unset($methodNameToGetEnabledForObjectType);
            }
            unset($itemObjClassName,
                $discountcodeLabelConstantValue,
                $labelDiscountCode,
                $discountCodeId,
                $discountIdHtmlEntities);
        } catch (Exception $e) {
            $this->logger->critical('Unexpected error on discountCodeIsValid method of DiscountCodeService.php', ['exception' => $e]);
            throw $e;
        } finally {
            unset($itemObjClassName,
                $discountcodeLabelConstantValue,
                $labelDiscountCode,
                $discountCodeId,
                $discountIdHtmlEntities,
                $methodNameToGetEnabledForObjectType,
                $translatedLabelDiscountCode,
                $itemObjPaymentLogEntityName,
                $itemObjPaymentLogRepository,
                $itemObjInvoiceEntityName,
                $itemObjInvoiceRepository,
                $itemObjItemObjIdField,
                $itemObjId,
                $itemObjPaymentLogCount,
                $itemObjInvoiceCount);
        }
        return $returnValue;
    }
}
