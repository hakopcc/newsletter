<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\EnhancedLead;

use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use ArcaSolutions\ModStoresBundle\Plugins\EnhancedLead\Entity\ListingLevelFieldLeads;
use ArcaSolutions\WebBundle\Services\EmailNotificationService;
use ArcaSolutions\WebBundle\Services\LeadHandler;
use ArcaSolutions\WebBundle\Services\TimelineHandler;
use Contact;
use Exception;
use Listing;
use pageBrowsing;

class EnhancedLeadBundle extends Bundle
{
    private $devEnvironment = false;
    /**
     * Boots the Bundle.
     */
    public function boot()
    {

        $logger = $this->container->get('logger');
        $notLoggedCriticalException = null;
        try {
            $this->devEnvironment = Kernel::ENV_DEV === $this->container->getParameter('kernel.environment');

            if ($this->isSitemgr()) {

                /*
                 * Register sitemgr only bundle hooks
                 */
                Hooks::Register('formpricing_after_add_fields', function (&$params = null) {
                    return $this->getFormPricingAfterAddFields($params);
                });
                Hooks::Register('paymentgateway_after_save_levels', function (&$params = null) {
                    return $this->getPaymentGatewayAfterSaveLevels($params);
                });
                Hooks::Register('formlevels_render_fields', function (&$params = null) {
                    return $this->getFormLevelsRenderFields($params);
                });
                Hooks::Register('classlisting_after_makerow', function (&$params = null) {
                    return $this->getClassListingAfterMakeRow($params);
                });
                Hooks::Register('classlisting_after_save', function (&$params = null) {
                    return $this->getClassListingAfterExecuteSave($params);
                });
                Hooks::Register('classlisting_before_delete', function (&$params = null) {
                    return $this->getClassListingBeforeDelete($params);
                });

            } else {

                /*
                 * Register front only bundle hooks
                 */
                Hooks::Register('classlisting_after_makerow', function (&$params = null) {
                    return $this->getClassListingAfterMakeRow($params);
                });
                Hooks::Register('classlisting_after_save', function (&$params = null) {
                    return $this->getClassListingAfterExecuteSave($params);
                });
                Hooks::Register('classlisting_before_delete', function (&$params = null) {
                    return $this->getClassListingBeforeDelete($params);
                });

            }

            // Todo: revise hooks names
            Hooks::Register('sendmail_controller_enhancedlead', function (&$params = null) {
                return $this->getSendmailControllerEnhancedlead($params);
            });
            Hooks::Register('sendmail_enhancedlead', function (&$params = null) {
                return $this->getSendmailEnhancedlead($params);
            });
            Hooks::Register('class_listing_level_leads', function (&$params = null) {
                return $this->getClassListingLevelLeads($params);
            });
            Hooks::Register('listing_leads_max', function (&$params = null) {
                return $this->getListingLeadsMax($params);
            });
            Hooks::Register('member_dashboard_reports', function (&$params = null) {
                return $this->getMemberDashboardReports($params);
            });
            Hooks::Register('item_hasActivity', function (&$params = null) {
                return $this->getItemHasActivity($params);
            });
            Hooks::Register('item_hasemail', function (&$params = null) {
                return $this->getItemHasEmail($params);
            });
            Hooks::Register('member_dashboard_item_leads', function (&$params = null) {
                return $this->getMemberDashboardItemLeads($params);
            });
            Hooks::Register('view_member_dashboard_leads', function (&$params = null) {
                return $this->getViewMemberDashboardLeads($params);
            });
            Hooks::Register('view_member_dashboard_leads_listing_hasemail', function (&$params = null) {
                return $this->getViewMemberListingEmail($params);
            });
            Hooks::Register('ajax_enhanced_lead', function (&$params = null) {
                return $this->getAjaxEnhancedLead($params);
            });
            Hooks::Register('scripts_enhanced_leads', function (&$params = null) {
                return $this->getScriptsEnhancedLeads($params);
            });
            Hooks::Register('scripts_enhanced_lead_functions', function (&$params = null) {
                return $this->getScriptsEnhacedLeadFunctions($params);
            });
            Hooks::Register('sitemgr_form_listing_enhancedlead', function (&$params = null) {
                return $this->getSitemgrFormListingEnhancedLead($params);
            });
            Hooks::Register('enhanced_lead_reply', function (&$params = null) {
                return $this->getEnhanceLeadReply($params);
            });
            parent::boot();
        } catch (Exception $e) {
            if (!empty($logger)) {
                $logger->critical('Unexpected error on boot method of EnhancedLeadBundle.php', ['exception' => $e]);
            } else {
                $notLoggedCriticalException = $e;
            }
        } finally {
            unset($logger);
            if (!empty($notLoggedCriticalException)) {
                throw $notLoggedCriticalException;
            }
        }
    }

    private function getFormPricingAfterAddFields(&$params = null)
    {
        if ($params['type'] == 'listing') {

            $tranlator = $this->container->get('translator');

            $params['levelOptions'][] = [
                'name'  => 'leads',
                'type'  => 'numeric',
                'title' => $tranlator->trans('Total Leads'),
                'tip'   => $tranlator->trans('How many leads can an owner view?'),
                'min'   => 0,
                'max'   => 999,
            ];
        }
    }

    private function getPaymentGatewayAfterSaveLevels(&$params = null)
    {
        if ($params['type'] == 'listing' && $params['levelOptionData']['leads']) {

            $doctrine = $this->container->get('doctrine');
            $manager = $this->container->get('doctrine')->getManager();

            foreach ($params['levelOptionData']['leads'] as $level => $field) {

                $listingLevel = $doctrine->getRepository('EnhancedLeadBundle:ListingLevelFieldLeads')->findOneBy([
                    'level' => $level,
                ]);

                if ($listingLevel) {
                    $listingLevel->setField($field);
                    $manager->persist($listingLevel);
                } else {
                    $listingLevel = new ListingLevelFieldLeads();
                    $listingLevel->setLevel($level);
                    $listingLevel->setField($field);
                    $manager->persist($listingLevel);
                }
            }

            $manager->flush();
        }
    }

    private function getFormLevelsRenderFields(&$params = null)
    {
        if (is_a($params['levelObj'], 'ListingLevel') && $params['option']['name'] == 'leads') {

            $params['levelObj']->leads = [];

            $resultLevel = $this->container->get('doctrine')->getRepository('EnhancedLeadBundle:ListingLevelFieldLeads')->findBy([],
                ['level' => 'DESC']);

            if ($resultLevel) {
                foreach ($resultLevel as $levelfield) {
                    $params['levelObj']->leads[] = $levelfield->getField();
                }
            }
        }
    }

    private function getClassListingAfterMakeRow(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $statement = $connection->prepare('SELECT * FROM Listing_EnhancedLead WHERE id = :id');
        $statement->bindValue('id', $params['that']->id);
        $statement->execute();

        $row = $statement->fetch();

        $params['that']->leads_max = (($row['leads_max'] != '') ? (is_numeric($row['leads_max']) ? $row['leads_max'] : (is_numeric($params['that']->leads_max) ? $params['that']->leads_max : 'NULL')) : 'NULL');
    }

    private function getClassListingAfterExecuteSave(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $statement = $connection->prepare('SELECT * FROM Listing_EnhancedLead WHERE id = :id');
        $statement->bindValue('id', $params['that']->id);
        $statement->execute();

        $row = $statement->fetch();

        if (!is_numeric($_POST['leads_max'])) {
            $_POST['leads_max'] = null;
        }

        if ($row) {
            $statement = $connection->prepare('UPDATE Listing_EnhancedLead set leads_max = :leads_max WHERE id = :id');
            $statement->bindValue('leads_max', $_POST['leads_max']);
            $statement->bindValue('id', $params['that']->id);
            $statement->execute();
        } else {
            $statement = $connection->prepare('INSERT INTO Listing_EnhancedLead(id,leads_max) VALUES(:id,:leads_max)');
            $statement->bindValue('id', $params['that']->id);
            $statement->bindValue('leads_max', $_POST['leads_max']);
            $statement->execute();
        }
    }

    private function getClassListingBeforeDelete(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $statement = $connection->prepare('DELETE FROM Listing_EnhancedLead WHERE id = :id');
        $statement->bindValue('id', $params['that']->id);
        $statement->execute();
    }

    // Todo: methods to hooks revision names
    private function getSendmailControllerEnhancedlead(&$params = null)
    {
        $leadType = null;

        switch ($params['module']) {
            case 'listing':
                $leadType = LeadHandler::ITEMTYPE_LISTING;
                break;
            case 'event':
                $leadType = LeadHandler::ITEMTYPE_EVENT;
                break;
            case 'classified':
                $leadType = LeadHandler::ITEMTYPE_CLASSIFIED;
                break;
        }

        $this->container->get('modstore.storage.service')->store('leadType', $leadType);
    }

    private function getSendmailEnhancedlead(&$params = null)
    {
        $doctrine = $this->container->get('doctrine');
        $translator = $this->container->get('translator');

        $leadType = $this->container->get('modstore.storage.service')->retrieve('leadType');

        $account = [];
        if (!empty($params['item']->getAccountId())) {
            $owner = $doctrine->getRepository('CoreBundle:Contact', 'main')->findOneBy([
                'account' => $params['item']->getAccountId(),
            ]);
            $account = [
                'email' => $owner->getEmail(),
                'name'  => $owner->getFirstName().' '.$owner->getLastName(),
            ];
        }

        $notification = $this->container->get('email.notification.service')->getEmailMessage(EmailNotificationService::NEW_LEAD);

        if (!empty($notification) && !empty($params['item']->getEmail())) {

            $fromSitemgr = explode(',', $doctrine->getRepository('WebBundle:Setting')->getSetting('emailconf_email'));

            $body = '';
            if ($params['module'] == 'listing') {
                $body .= $translator->trans('Item').': '.$params['item']->getTitle();
                $body .= '<br><br>'.$translator->trans('Name').': '.$params['form']->get('name')->getData();
                $body .= '<br><br>'.$translator->trans('Email').': '.$params['form']->get('email')->getData();
            }

            $notification->setSubject($notification->getSubject().' '.$params['form']->get('subject')->getData());
            $notification->setTo($params['item']->getEmail());
            $notification->setFrom($fromSitemgr[0]);
            $notification->setPlaceholder('ACCOUNT_NAME', count($account) ? $account['name'] : 'Listing Owner');
            $notification->setPlaceholder('ACCOUNT_USERNAME', count($account) ? $account['email'] : '');
            $notification->setPlaceholder('LEAD_MESSAGE', $body);

            $sending = $notification->sendEmail();

            $params['response'] = [
                'status'  => false,
                'title'   => $translator->trans('Message'),
                'content' => $translator->trans('We can not send your email. Try again, please.'),
            ];

            if ($sending) {
                $names = explode(' ', trim($params['form']->get('name')->getData()));
                $firstName = array_pop($names);
                $lastName = implode(' ', $names);
                $email = $params['form']->get('email')->getData();
                $subject = $params['form']->get('subject')->getData();
                $message = $params['form']->get('text')->getData();

                $lead = $this->container->get('leadhandler')->add(
                    $leadType,
                    $params['id'],
                    $lastName,
                    $firstName,
                    $email,
                    '',
                    $subject,
                    $message
                );

                $this->container->get('timelinehandler')->add(
                    $lead->getId(),
                    TimelineHandler::ITEMTYPE_LEAD,
                    TimelineHandler::ACTION_NEW
                );
            }

            $params['response'] = [
                'status'  => true,
                'title'   => $translator->trans('Message'),
                'content' => $translator->trans('Your e-mail has been sent. Thank you.'),
            ];
        }
    }

    private function getClassListingLevelLeads(&$params = null)
    {
        $resultLevel = $this->container->get('doctrine')->getRepository('EnhancedLeadBundle:ListingLevelFieldLeads')->findOneBy([
            'level' => end($params['that']->value),
        ]);

        if(!empty($resultLevel)) {
            $params['that']->leads[$params['that']->value] = $resultLevel->getField();
        }
    }

    private function getListingLeadsMax(&$params = null)
    {
        if (!string_strpos($params['url_base'], '/'.SITEMGR_ALIAS.'') && !$_POST['leads_max']) {

            $leadsArray = $params['listingLevelObj']->union($params['listingLevelObj']->value,
                $params['listingLevelObj']->leads);
            $_POST['leads_max'] = isset($leadsArray[$_POST['level']]) ? $leadsArray[$_POST['level']] : $leadsArray[$params['listingLevelObj']->default];

        }
    }

    private function getMemberDashboardReports(&$params = null)
    {
        if (strtolower($params['item_type']) != 'listing') {

            $params['item_leads'] += $params['report']['email'];

        }
    }

    private function getItemHasActivity(&$params = null)
    {
        if ($params['item_hasDetail'] || $params['item_hasphone'] || $params['item_haswebsite'] || $params['item_hasfax'] || $params['item_hasreview'] || strtolower($params['item_type']) == 'banner') {
            $params['item_hasActivity'] = true;
        }
    }

    private function getItemHasEmail(&$params = null)
    {
        if (strtolower($params['item_type']) != 'listing') {
            $params['where'] = " Leads.type = '".strtolower($params['item_type'])."' AND Leads.item_id = '".$params['item_id']."' AND Leads.item_id = ".$params['leadTable'].'.id AND '.$params['leadTable'].".account_id = '".$params['acctId']."'";

            $params['pageObj'] = new pageBrowsing('Leads, '.$params['leadTable'].'', $params['screen'], false,
                'entered ASC', 'first_name', $params['letter'], $params['where'],
                'Leads.*');
        } else {

            $params['limit'] = false;
            $params['where'] = " Leads.type = '".strtolower($params['item_type'])."' AND Leads.item_id = '".$params['item_id']."' AND Leads.item_id =  ".$params['leadTable'].'.id AND '.$params['leadTable'].".account_id = '".$params['acctId']."'";

            if (strtolower($params['item_type']) == 'listing') {

                $resultLevel = $this->container->get('doctrine')->getRepository('EnhancedLeadBundle:ListingLevelFieldLeads')->findOneBy([
                    'level' => $params['itemObj']->getNumber('level'),
                ]);

                $params['show_leadsTables'] = true;

                $filter_year = date('Y');
                $filter_month = date('m');

                $params['where'] .= " AND YEAR(Leads.entered) = '{$filter_year}' AND MONTH(Leads.entered) = '{$filter_month}'";

                $params['limit'] = $params['itemObj']->getNumber('leads_max') != 'NULL' ? $params['itemObj']->getNumber('leads_max') : $resultLevel->getField();

                $count_leads = $this->getLeadsCount($filter_month, $filter_year, true, $params['itemObj']);

                if ($count_leads > $params['limit']) {
                    $params['show_upgrade_link'] = true;
                }

                $earliest_year = 1995;
                $drowdown_options = '';
                foreach (range(date('Y'), $earliest_year) as $x) {
                    $drowdown_options .= '<option value="'.$x.'"'.($x === $filter_year ? ' selected="selected"' : '').'>'.$x.'</option>';
                }
                $params['select_year'] = '<select name="filter_year" id="filter_year" class="select" style="height:40px">'.$drowdown_options.'</select>';

                $drowdown_options = '';
                for ($i = 1; $i <= 12; $i++) {
                    $month_num = str_pad($i, 2, 0, STR_PAD_LEFT);
                    $month_name = date('F', mktime(0, 0, 0, $i + 1, 0, 0));
                    $drowdown_options .= '<option value="'.$month_num.'"'.($month_num === $filter_month ? ' selected="selected"' : '').'>'.$month_name.'</option>';
                }
                $params['select_month'] = '<select name="filter_month" id="filter_month" class="select" style="height:40px">'.$drowdown_options.'</select>';
            }

            $params['count_unread_leads'] = $this->getUnreadLeadsCount(false, false, false, $params['itemObj']);
            $params['count_total_leads'] = $this->getLeadsCount(false, false, false, $params['itemObj']);
            if ($params['limit'] > 0) {
                $params['pageObj'] = new pageBrowsing('Leads, '.$params['leadTable'].'', $params['screen'],
                    $params['limit'],
                    'Leads.entered DESC', 'first_name', $params['letter'], $params['where'], 'Leads.*');
            }
        }
    }

    public function getLeadsCount($month_filter = false, $year_filter = false, $filter = true, $itemObj = null)
    {
        if (!$itemObj->id) {
            return 0;
        }

        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $where = " Leads.type = 'listing' AND Leads.item_id = :itemObj_id";

        if ($filter) {
            $where .= ' AND YEAR(Leads.entered) = '.($year_filter ? ':year_filter' : 'YEAR(NOW())');
            $where .= ' AND MONTH(Leads.entered) = '.($month_filter ? ':month_filter' : 'MONTH(NOW())');
        }

        $statement = $connection->prepare('SELECT COUNT(id) as count_leads FROM Leads WHERE'.$where);
        $statement->bindValue('itemObj_id', $itemObj->id);

        if ($filter) {
            $statement->bindValue('year_filter', $year_filter);
            $statement->bindValue('month_filter', $month_filter);
        }

        $statement->execute();
        $result = $statement->fetch();

        return $result['count_leads'];
    }

    public function getUnreadLeadsCount($month_filter = false, $year_filter = false, $filter = false, $itemObj = null)
    {
        if (!$itemObj->id) {
            return 0;
        }

        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $where = " Leads.type = 'listing' AND Leads.item_id = :itemObj ";
        $where .= "AND Leads.new = 'y'";

        if ($filter) {
            $where .= ' AND YEAR(Leads.entered) = '.($year_filter ? ':year_filter' : 'YEAR(NOW())');
            $where .= ' AND MONTH(Leads.entered) = '.($month_filter ? ':month_filter' : 'MONTH(NOW())');
        }

        $statement = $connection->prepare('SELECT COUNT(id) as count_unread_leads FROM Leads WHERE'.$where);
        $statement->bindValue('itemObj', $itemObj->id);
        if ($filter) {
            $statement->bindValue('year_filter', $year_filter);
            $statement->bindValue('month_filter', $month_filter);
        }
        $statement->execute();
        $row = $statement->fetch();

        return $row['count_unread_leads'];
    }

    private function getMemberDashboardItemLeads(&$params = null)
    {
        $params['item_leads'] = (is_array($params['leadsArr']) ? count($params['leadsArr']) : 0);
    }

    private function getViewMemberDashboardLeads(&$params = null)
    {
        if (strtolower($params['item_type']) == 'listing' || $params['item_hasemail']) {

            $item_type = strtolower($params['item_type']) == 'listing' ? $params['count_total_leads'] : count($params['leadsArr']);
            $lead_count = (count($params['leadsArr']) == 1) ? '' : 's';

            echo $this->container->get('templating')->render('EnhancedLeadBundle::view-member-dashboard-leads.html.twig',
                [
                    'item_type'  => $item_type,
                    'lead_count' => $lead_count,
                ]);

            if ((strtolower($params['item_type']) == 'listing' && $params['count_total_leads']) || (strtolower($params['item_type']) != 'listing' && count($params['leadsArr']))) {

                echo $this->container->get('templating')->render('EnhancedLeadBundle::view-member-dashboard-leads-scrollpage.html.twig',
                    [
                        'item_type'          => $params['item_type'],
                        'newLeads'           => $params['newLeads'],
                        'count_unread_leads' => $params['count_unread_leads'],
                        'newLeadsTip'        => $params['newLeadsTip'],
                    ]);
            }
        }
    }

    private function getViewMemberListingEmail(&$params = null)
    {
        if ((strtolower($params['item_type']) == 'listing') || $params['item_hasemail']) {

            $itemObj = new Listing($params['item_id']);

            $translator = $this->container->get('translator');

            $leadsHtml = '';
            $countLead = 0;

            if ($params['leadsArr']) {
                $leadsArrTmp = $params['leadsArr'];

                foreach ($leadsArrTmp as $each_lead) {

                    $auxMessage = @unserialize($each_lead['message']);
                    if (is_array($auxMessage)) {
                        $each_lead['message'] = '';
                        foreach ($auxMessage as $key => $value) {
                            $each_lead['message'] .= (defined($key) ? constant($key) : $key).($value ? ': '.$value : '')."\n";
                        }
                    }

                    $titleIco = '';
                    $replied = false;
                    if ($each_lead['reply_date'] && $each_lead['reply_date'] != '0000-00-00 00:00:00') {
                        $replied = true;
                        $titleIco = $translator->trans('Replied').' ('.format_date($each_lead['reply_date'],
                                DEFAULT_DATE_FORMAT, 'datestring').')';
                    }
                    $titleIcoToday = $translator->trans('Replied').' ('.format_date(date('Y').'-'.date('m').'-'.date('d'),
                            DEFAULT_DATE_FORMAT, 'datestring').')';

                    $leadsHtml .= $this->container->get('templating')->render('EnhancedLeadBundle::view-member-dashboard-leads-listing-viewmore.html.twig',
                        [
                            'countLead'     => $countLead,
                            'maxItems'      => $params['maxItems'],
                            'each_lead'     => $each_lead,
                            'replied'       => $replied,
                            'titleIco'      => $titleIco,
                            'titleIcoToday' => $titleIcoToday,
                            'item_id'       => $params['item_id'],
                            'item_type'     => $params['item_type'],
                            'to'            => $params['to'],
                            'action'        => $params['action'],
                            'idLead'        => $params['idLead'],
                            'message'       => $each_lead['message'],
                            'itemObj'       => $itemObj,
                        ]);

                    $countLead++;

                }

            }

            echo $this->container->get('templating')->render('EnhancedLeadBundle::view-member-dashboard-leads-listing-hasemail.html.twig',
                [
                    'leadsHtml'         => $leadsHtml,
                    'show_leadsTables'  => $params['show_leadsTables'],
                    'item_status'       => $params['item_status'],
                    'shareFacebook'     => $params['shareFacebook'],
                    'shareTwitter'      => $params['shareTwitter'],
                    'leadsArr'          => $params['leadsArr'],
                    'limit'             => $params['limit'],
                    'item_id'           => $params['item_id'],
                    'show_upgrade_link' => $params['show_upgrade_link'],
                    'select_month'      => $params['select_month'],
                    'select_year'       => $params['select_year'],
                    'countLead'         => $countLead,
                    'maxItems'          => $params['maxItems'],
                    'filter_year'       => $params['filter_year'],
                    'filter_month'      => $params['filter_month'],
                    'countLeads'        => $countLead,
                ]);

        }
    }

    private function getAjaxEnhancedLead(&$params = null)
    {
        if ($params['ajax_type'] == 'sendOwnerNewLeads') {

            $translator = $this->container->get('translator');

            $listingObj = new Listing($params['listing_id']);
            $acctId = sess_getAccountIdFromSession();

            if ($acctId && $listingObj->getNumber('account_id') == $acctId) {

                $contactObj = new Contact($acctId);

                $resultLevel = $this->container->get('doctrine')->getRepository('EnhancedLeadBundle:ListingLevelFieldLeads')->findOneBy([
                    'level' => $listingObj->getNumber('level'),
                ]);

                $limit = $listingObj->getNumber('leads_max') != 'NULL' ? $listingObj->getNumber('leads_max') : $resultLevel->getField();

                $count_leads = $this->getLeadsCount(false, false, false, $listingObj);

                if ($count_leads > $limit) {

                    $sitemgr_account_email = $this->container->get('settings')->getDomainSetting('sitemgr_account_email');
                    $sitemgr_account_emails = explode(',', $sitemgr_account_email);

                    $subject = $translator->trans('Request more quantity of Leads.');
                    $body = $translator->trans('Hello Manager,\n\nI would like to view more leads for my listing LISTING_TITLE.\nActuallly I can view LIMIT_LEVEL leads, but i have COUNT_LEADS leads available for this listing this month.\n\nListing information:\nACCOUNT EMAIL: ACCOUNT_EMAIL\nACCOUNT NAME: ACCOUNT_NAME\nLISTING TITLE: LISTING_TITLE\nLEADS AVAILABLE: COUNT_LEADS\n\n');

                    $body = str_replace(array('\n', 'LISTING_TITLE', 'LISTING_TITLE', 'LIMIT_LEVEL', 'COUNT_LEADS'),
                        array(
                            '<br>',
                            $listingObj->getString('title'),
                            $listingObj->getString('title'),
                            $limit,
                            $count_leads
                        ), $body);
                    $body = str_replace(['ACCOUNT_EMAIL', 'ACCOUNT_NAME'],
                        [
                            ($contactObj->getString('email') ?: $contactObj->getString('username')),
                            $contactObj->getString('first_name').' '.$contactObj->getString('last_name')
                        ], $body);

                    system_notifySitemgr($sitemgr_account_emails, $subject, $body, true);

                    echo 'ok';
                }
            }
        }
    }

    private function getScriptsEnhancedLeads(&$params = null)
    {
        echo $this->container->get('templating')->render('EnhancedLeadBundle::js/lead_script.js.twig');
    }

    private function getScriptsEnhacedLeadFunctions(&$params = null)
    {
        echo $this->container->get('templating')->render('EnhancedLeadBundle::js/lead_requests.js.twig', [
            'screen' => $params['screen'],
            'letter' => $params['letter'],
        ]);
    }

    private function getSitemgrFormListingEnhancedLead(&$params = null)
    {
        echo $this->container->get('templating')->render('EnhancedLeadBundle::sitemgr-form-listing-leads.html.twig', [
            'leads_max' => $params['leads_max'],
        ]);
    }

    private function getEnhanceLeadReply(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $statement = $connection->prepare("UPDATE Leads SET status = 'A', reply_date = NOW() WHERE id = :id ");
        $statement->bindValue('id', $params['leadObj']->id);
        $statement->execute();

        $listingObj = new Listing($params['leadObj']->item_id);
        $sendEmail = false;

        if (!empty($listingObj->account_id)) {
            $contactObj = new Contact($listingObj->account_id);

            if (!empty($listingObj->email)) {
                $replyEmail = $listingObj->email;
            } else {
                $replyEmail = $contactObj->email;
            }

            $replyName = $contactObj->first_name.' '.$contactObj->last_name;
            $sendEmail = true;
        } else if (!empty($listingObj->email)) {
            $replyEmail = $listingObj->email;
            $replyName = $listingObj->title;
            $sendEmail = true;
        }

        if ($sendEmail) {
            try {
                if(!empty($replyEmail)) {
                    $this->container->get('core.mailer')
                        ->newMail($params['leadObj']->subject, $params['message'])
                        ->setTo($params['to'])
                        ->setReplyTo($replyEmail, $replyName)
                        ->send();
                }else{
                    $this->container->get('core.mailer')
                        ->newMail($params['leadObj']->subject, $params['message'])
                        ->setTo($params['to'])
                        ->send();
                }
            } catch (Exception $e) {
                $this->container->get('logger')->alert('error email lead:  '.$e->getMessage());
            }
        }
    }
}
