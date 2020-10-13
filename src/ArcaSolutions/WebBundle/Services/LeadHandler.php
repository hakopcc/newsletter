<?php

namespace ArcaSolutions\WebBundle\Services;

use ArcaSolutions\CoreBundle\Entity\AccountConsent;
use ArcaSolutions\CoreBundle\Services\ConsentService;
use ArcaSolutions\WebBundle\Entity\Leads;
use ArcaSolutions\WebBundle\Form\Builder\JsonFormBuilder;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\Session;

class LeadHandler
{
    const ITEMTYPE_GENERAL = 'general';
    const ITEMTYPE_CLASSIFIED = 'classified';
    const ITEMTYPE_EVENT = 'event';
    const ITEMTYPE_LISTING = 'listing';

    const STATUS_READ = 'A';
    const STATUS_UNREAD = 'P';

    /** @var Session */
    private $session;
    /** @var ManagerRegistry */
    private $doctrine;
    /** @var ConsentService */
    private $consentService;
    /** @var JsonFormBuilder */
    private $jsonFormBuilder;
    public function __construct(ManagerRegistry $doctrine, Session $session,JsonFormBuilder $jsonFormBuilder,ConsentService $consentService)
    {
        $this->doctrine = $doctrine;
        $this->session = $session;
        $this->jsonFormBuilder = $jsonFormBuilder;
        $this->consentService = $consentService;
    }

    /**
     * @param $type
     * @param int $itemId
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $phone
     * @param string $subject
     * @param string $message
     * @param null $consent_id
     * @param null $datetimeConsent
     * @return Leads
     * @throws \Exception
     */
    public function add(
        $type,
        $itemId = 0,
        $firstName = '',
        $lastName = '',
        $email = '',
        $phone = '',
        $subject = '',
        $message = '',
        $consent_id = null,
        $datetimeConsent = null
    ) {

        if($this->session->get('SESS_ACCOUNT_ID') && $consent_id!=null){

            $repository = $this->doctrine->getRepository('CoreBundle:AccountConsent', 'main');
            $accountConsent = $repository->find(array("account_id" => $this->session->get('SESS_ACCOUNT_ID', 0), "consent_id" => $consent_id));

            if($accountConsent){
                $this->consentService->updateAccountConsent($accountConsent);
            }else{

                $consent = $this->doctrine->getRepository('CoreBundle:Consent', 'main')->find($consent_id);
                $account = $this->doctrine->getRepository('CoreBundle:Account', 'main')->find($this->session->get('SESS_ACCOUNT_ID', 0));

                $accountConsent = new AccountConsent();
                $accountConsent->setAccountId($account);
                $accountConsent->setConsentId($consent);
                $accountConsent->setDate(new \DateTime("now"));
                $entityManager = $this->doctrine->getManager('main');
                $entityManager->persist($accountConsent);
                $entityManager->flush();

            }
        }
        $lead = new Leads();
        $lead->setItemId($itemId ? $itemId : 0);
        $lead->setMemberId($this->session->get('SESS_ACCOUNT_ID', 0));

        $lead->setType($type)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setEmail($email)
            ->setPhone($phone)
            ->setSubject($subject)
            ->setMessage($message)
            ->setEntered(new \DateTime())
            ->setStatus(self::STATUS_UNREAD)
            ->setNew('y');

        $lead->setConsentId($consent_id);
        $lead->setDatetimeConsent($datetimeConsent);

        $em = $this->doctrine->getManager();
        $em->persist($lead);
        $em->flush();

        return $lead;
    }
}
