<?php

namespace ArcaSolutions\CoreBundle\Services;

use ArcaSolutions\CoreBundle\Entity\AccountConsent;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ConsentService
 *
 * @package ArcaSolutions\CoreBundle\Services
 */
class ConsentService
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * function return type consent
     * @param $consentBody
     * @return object|null
     */
    public function getIdConsent($consentBody)
    {
        //select what id consent
        if ($consentBody['signup'] == "on") {
            $name = 'signup';
        } else if ($consentBody['payment'] == "on") {
            $name = 'payment';
        } else if ($consentBody['review'] == "on") {
            $name = 'review';
        } else if ($consentBody['contactUs'] == "on") {
            $name = 'contactUs';
        } else if ($consentBody['lead'] == "on") {
            $name = 'lead';
        }else if ($consentBody['newsletter'] == "on") {
            $name = 'newsletter';
        }

        $repository = $this->container->get('doctrine')->getRepository('CoreBundle:Consent', 'main');
        return $consent = $repository->findOneBy(array('value' => $name));
    }

    /**
     * 06/03/2019
     * Mateus Cabana
     * Insert AccountConsent if register person
     * @param $consentBody
     * @return int|null
     * @throws \Exception
     */
    public function saveConsent($consentBody)
    {
        $consent = $this->getIdConsent($consentBody);
        $repository = $this->container->get('doctrine')->getRepository('CoreBundle:Account', 'main');
        $account = $repository->findOneBy(['username' => $consentBody['username']]);
        if ($consent != null && $account != null) {
            try {
                $accountConsent = new AccountConsent();
                $accountConsent->setAccountId($account);
                $accountConsent->setConsentId($consent);
                $accountConsent->setDate(new \DateTime("now"));
                $entityManager = $this->container->get('doctrine')->getManager('main');
                $entityManager->persist($accountConsent);
                $entityManager->flush();
                return true;
            } catch (Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * 06/03/2020
     * Mateus Cabana
     * Function insert Account Consent if register not exist
     * @param $id
     * @param $consentBody
     * @return AccountConsent|null
     * @throws \Exception
     */
    public function  insertAccountConsent($id, $consentBody){
        if ($id && $consentBody) {
            $consent = $this->getIdConsent($consentBody);
            $repository = $this->container->get('doctrine')->getRepository('CoreBundle:Account', 'main');
            //if id account not null search by id
            if($id){
                $account = $repository->findOneBy(array('id' => $id));
            }else{
                $account = $repository->findOneBy(array('username' => $consentBody['username']));
            }
            if($consent && $account) {
                $accountConsent = new AccountConsent();
                $accountConsent->setAccountId($account);
                $accountConsent->setConsentId($consent);
                $accountConsent->setDate(new \DateTime("now"));
                $entityManager = $this->container->get('doctrine')->getManager('main');
                $entityManager->persist($accountConsent);
                $entityManager->flush();
                return $accountConsent;
            }
        }
        return null;
    }
    /**
     * 06/03/2020
     * MAteus Cabana
     * Function get consent from an id and body requisition
     * @param $id -> id account
     * @param $consentBody -> bodyRequisition post
     * @return |null
     */
    public function getConsent($id, $consentBody)
    {
        if ($id && $consentBody) {
            $consent = $this->getIdConsent($consentBody);
            if($consent) {
                try {
                    $repository = $this->container->get('doctrine')->getRepository('CoreBundle:AccountConsent', 'main');
                    return $repository->find(array("account_id" => $id, "consent_id" => $consent->getId()));
                } catch (Exception $e) {
                    return null;
                }
            }
        }
        return null;
    }


    /**
     * 06/03/2019
     * Mateus Cabana
     * Function updated accountConsent
     * @param AccountConsent $accountConsent
     * @return AccountConsent|null
     * @throws \Exception
     */
    public function updateAccountConsent(AccountConsent $accountConsent)
    {
        try {
            $accountConsent->setDate(new \DateTime("now"));
            $entityManager = $this->container->get('doctrine')->getManager('main');
            $entityManager->persist($accountConsent);
            $entityManager->flush();
            return $accountConsent;
        } catch (Exception $e) {
            return null;
        }
        return null;
    }

    /**
     * 09/03/2020
     * Mateus Cabana
     * Function remove account consent to db
     * @param AccountConsent $accountConsent
     * @return int|null
     * @throws \Exception
     */
    public function removeAccountConsent(AccountConsent $accountConsent)
    {
        try {
            $entityManager = $this->container->get('doctrine')->getManager('main');
            $entityManager->remove($accountConsent);
            $entityManager->flush();
            return 1;
        } catch (Exception $e) {
            return null;
        }
        return null;
    }

    public function getConsentByAccount($idAccount){
        $repository = $this->container->get('doctrine')->getRepository('CoreBundle:Account', 'main');
        $account = $repository->find($idAccount);
        if($account){
            //query get consent
            $repository = $this->container->get('doctrine')->getRepository('CoreBundle:Consent', 'main');
            $consent = $repository->findOneBy(['value' => 'lead']);
            if($consent){
                //query get account consent except leads
                $repository = $this->container->get('doctrine')->getRepository('CoreBundle:AccountConsent', 'main');
                $query = $repository->createQueryBuilder('a')
                    ->where('a.account_id = :account_id')
                    ->andWhere('a.consent_id <> :consent_id')
                    ->setParameter('account_id', $account->getId())
                    ->setParameter('consent_id', $consent->getId())
                    ->orderBy('a.date', 'ASC')
                    ->getQuery();
                return  $query->getResult();
            }
        }
        return null;
    }
    public function getLeadConsentByAccount($idAccount){
        $repository = $this->container->get('doctrine')->getRepository('CoreBundle:Account', 'main');
        $account = $repository->find($idAccount);
        if($account){
            //query get leads
            $repository = $this->container->get('doctrine')->getRepository('WebBundle:Leads', 'domain');
            $query = $repository->createQueryBuilder('l')
                ->where('l.memberId = :member_id')
                ->andWhere('l.consent_id  != :consent_id')
                ->setParameter('member_id', $account->getId())
                ->setParameter('consent_id', 0)
                ->orderBy('l.datetimeConsent', 'ASC')
                ->getQuery();
            $leads = $query->getResult();
            for ($i = 0;$i<count($leads);$i++){
                //lead form
                if($leads[$i]->getItemId() == 0 && $leads[$i]->getType()=="general"){
                    $leads[$i]->title = 'leadForm';
                }//listing
                else if($leads[$i]->getItemId() != 0 && $leads[$i]->getType()=="listing"){
                    $repository = $this->container->get('doctrine')->getRepository('ListingBundle:Listing', 'domain');
                    $aux = $repository ->find($leads[$i]->getItemId());
                }//event
                else if($leads[$i]->getItemId() != 0 && $leads[$i]->getType()=="event"){
                    $repository = $this->container->get('doctrine')->getRepository('EventBundle:Event', 'domain');
                    $aux = $repository ->find($leads[$i]->getItemId());
                }//classified
                else if($leads[$i]->getItemId() != 0 && $leads[$i]->getType()=="classified"){
                    $repository = $this->container->get('doctrine')->getRepository('ClassifiedBundle:Classified', 'domain');
                    $aux = $repository ->find($leads[$i]->getItemId());
                }//article
                else if($leads[$i]->getItemId() != 0 && $leads[$i]->getType()=="article"){
                    $repository = $this->container->get('doctrine')->getRepository('ArticleBundle:Article', 'domain');
                    $aux = $repository ->find($leads[$i]->getItemId());
                }
                if($aux){
                    $leads[$i]->title =$aux->getTitle();
                }

            }
            return $leads;
        }
        return null;
    }

}
