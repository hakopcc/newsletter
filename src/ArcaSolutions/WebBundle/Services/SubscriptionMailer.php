<?php
namespace ArcaSolutions\WebBundle\Services;
use ArcaSolutions\CoreBundle\Entity\AccountConsent;
use ArcaSolutions\CoreBundle\Services\ConsentService;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use ArcaSolutions\CoreBundle\Services\Settings;
use ArcaSolutions\WebBundle\Entity\MailappSubscribers;
use GuzzleHttp\Client;

class SubscriptionMailer
{
    const URL_API = 'http://arcamailer.com/api/api.php';
    const TIMEOUT = 60;
    /**
     * @var DoctrineRegistry
     */
    private $doctrine;
    /**
     * @var mixed
     */
    protected $arcamailerCustomerListId;

    /**
     * @var mixed
     */
    protected $action;

    /**
     * @var mixed
     */
    protected $subscriberName;

    /**
     * @var mixed
     */
    protected $subscriberEmail;

    /**
     * @var mixed
     */
    protected $subscriberType;

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var ConsentService
     */
    protected $consentService;
    /**
     * @var Array
     */
    protected $messageErrors;

    /**
     * @var consent_Id
     */
    protected $consent_id;

    /**
     * @var $account_id
     */
    protected $account_id;

    /**
     * @var datetime_consent
     */
    protected $datetime_consent;

    /**
     * @return mixed
     */
    public function getAccountId()
    {
        return $this->account_id;
    }

    /**
     * @param mixed $account_id
     */
    public function setAccountId($account_id): void
    {
        $this->account_id = $account_id;
    }

    /**
     * @return consent_Id
     */
    public function getConsentId(): consent_Id
    {
        return $this->consent_id;
    }

    /**
     * @param consent_Id $consent_id
     */
    public function setConsentId($consent_id): void
    {
        $this->consent_id = $consent_id;
    }

    /**
     * @return datetime_consent
     */
    public function getDatetimeConsent(): datetime_consent
    {
        return $this->datetime_consent;
    }

    /**
     * @param datetime_consent $datetime_consent
     */
    public function setDatetimeConsent($datetime_consent): void
    {
        $this->datetime_consent = $datetime_consent;
    }


    /**
     * SubscriptionMailer constructor.
     *
     * @param Settings $settings
     * @param DoctrineRegistry $doctrine
     */
    public function __construct(Settings $settings, DoctrineRegistry $doctrine, ConsentService $consentService)
    {
        $this->settings = $settings;
        $this->setArcamailerCustomerListId($this->settings->getDomainSetting('arcamailer_customer_listid'));
        $this->doctrine = $doctrine;
        $this->consentService = $consentService;
    }

    /**
     * Send a Request to ArcaMailer API
     *
     * It is called to save a new visitor ArcaMailer API
     *
     * @return bool
     */
    public function sendSubscription()
    {
        $mailApp = new MailappSubscribers();
        $mailApp->setSubscriberName($this->subscriberName);
        $mailApp->setSubscriberEmail($this->subscriberEmail);
        $mailApp->setSubscriberType($this->subscriberType);
        $mailApp->setListId($this->arcamailerCustomerListId);
        $mailApp->setConsentId($this->consent_id);
        $mailApp->setDatetimeConsent($this->datetime_consent);
        $mailApp->setAccountId($this->account_id);
        $entityManager = $this->doctrine->getManager('domain');
        $entityManager->persist($mailApp);
        $entityManager->flush();


        if($this->consent_id!=null && $this->account_id!=null){
            $repository = $this->doctrine->getRepository('CoreBundle:AccountConsent', 'main');
            $accountConsent = $repository->find(array("account_id" => $this->account_id, "consent_id" => $this->consent_id));

            if($accountConsent){
                $this->consentService->updateAccountConsent($accountConsent);
            }else{
                $consent = $this->doctrine->getRepository('CoreBundle:Consent', 'main')->find($this->consent_id);
                $account = $this->doctrine->getRepository('CoreBundle:Account', 'main')->find($this->account_id);

                if($consent && $account){
                    $accountConsent = new AccountConsent();
                    $accountConsent->setAccountId($account);
                    $accountConsent->setConsentId($consent);
                    $accountConsent->setDate(new \DateTime("now"));

                    $entityManager = $this->doctrine->getManager('main');
                    $entityManager->persist($accountConsent);
                    $entityManager->flush();

                }

            }

        }


        $client = new Client();
        $response = $client->post(self::URL_API, [
            'body'    => [
                'action'           => $this->action,
                'listID'           => $this->arcamailerCustomerListId,
                'subscriber_name'  => $this->subscriberName,
                'subscriber_email' => $this->subscriberEmail,
                'subscriber_type'  => $this->subscriberType,
            ],
            'timeout' => self::TIMEOUT
        ]);
        $body = unserialize($response->getBody()->getContents());

        if (false === $body['success']) {
            $this->setMessageErrors($body['arrayReturn']);
        }

        return $body['success'];
    }

    /**
     * @return mixed
     */
    public function getArcamailerCustomerListId()
    {
        return $this->arcamailerCustomerListId;
    }

    /**
     * @param mixed $arcamailerCustomerListId
     */
    public function setArcamailerCustomerListId($arcamailerCustomerListId)
    {
        $this->arcamailerCustomerListId = $arcamailerCustomerListId;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getSubscriberName()
    {
        return $this->subscriberName;
    }

    /**
     * @param mixed $subscriberName
     */
    public function setSubscriberName($subscriberName)
    {
        $this->subscriberName = $subscriberName;
    }

    /**
     * @return mixed
     */
    public function getSubscriberEmail()
    {
        return $this->subscriberEmail;
    }

    /**
     * @param mixed $subscriberEmail
     */
    public function setSubscriberEmail($subscriberEmail)
    {
        $this->subscriberEmail = $subscriberEmail;
    }

    /**
     * @return mixed
     */
    public function getSubscriberType()
    {
        return $this->subscriberType;
    }

    /**
     * @param mixed $subscriberType
     */
    public function setSubscriberType($subscriberType)
    {
        $this->subscriberType = $subscriberType;
    }

    /**
     * @return mixed
     */
    public function getMessageErrors()
    {
        return $this->messageErrors;
    }

    /**
     * @param mixed $messageErrors
     */
    public function setMessageErrors($messageErrors)
    {
        $this->messageErrors = $messageErrors;
    }

}
