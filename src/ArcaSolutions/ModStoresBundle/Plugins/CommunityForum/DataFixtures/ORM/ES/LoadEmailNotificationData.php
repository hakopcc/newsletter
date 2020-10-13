<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\DataFixtures\ORM\ES;

use ArcaSolutions\WebBundle\Entity\EmailNotification;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadEmailNotificationData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /* These are the standard data of the system */
        $standardInserts = [
            [
                'id'           => 100,
                'email'        => 'Nueva publicación de conversación',
                'days'         => 0,
                'deactivate'   => '0',
                'updated'      => null,
                'bcc'          => '',
                'subject'      => '[EDIRECTORY_TITLE] Nueva publicación de conversación',
                'content_type' => 'text/html',
                'body'         => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns=http://www.w3.org/1999/xhtml><head>    <meta http-equiv=Content-Type content=text/html; charset=utf-8>    <meta name=robots content="noindex, nofollow">    <meta property=og:title content="EDIRECTORY_TITLE"></head><body style="margin: 0; mso-line-height-rule: exactly;  padding: 0; min-width: 100%; background-color: #fbfbfb">    <center class=wrapper style="display: table;table-layout: fixed;width: 100%;min-width: 620px;-webkit-text-size-adjust: 100%;-ms-text-size-adjust: 100%;background-color: #fbfbfb">        <table class=gmail style="border-collapse: collapse;border-spacing: 0;width: 650px;min-width: 650px">            <tbody>                <tr>                    <td style="padding: 0;vertical-align: top;font-size: 1px;line-height: 1px">&nbsp;                    </td>                </tr>            </tbody>        </table>        <table class="preheader centered" style="border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto">            <tbody>                <tr>                    <td style="padding: 0;vertical-align: top"> </td>                </tr>            </tbody>        </table>        <table class="header centered" style="border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto;width: 602px">            <tbody>                <tr>                    <td class=border style="padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e9e9e9;width: 1px">&nbsp;                    </td>                </tr>                <tr>                    <td class=logo style="padding: 32px 0;vertical-align: top;mso-line-height-rule: at-least">                        <div class=logo-center style="font-size: 26px;font-weight: 700;letter-spacing: -0.02em;line-height: 32px;color: #6E6D79;font-family: sans-serif;text-align: center" align=center>                             <img class="brand-logo" style="max-width: 100%" itemprop="logo" alt="EDIRECTORY_TITLE" src="LOGO">                        </div>                    </td>                </tr>            </tbody>        </table>        <table class=border style="border-collapse: collapse;border-spacing: 0;font-size: 1px;line-height: 1px;background-color: #e9e9e9;Margin-left: auto;Margin-right: auto" width=602>            <tbody>                <tr>                    <td style="padding: 0;vertical-align: top">&#8203;                    </td>                </tr>            </tbody>        </table>        <table class=centered style="border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto">            <tbody>                <tr>                    <td class=border style="padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e9e9e9;width: 1px">&#8203;                    </td>                    <td style="padding: 0;vertical-align: top">                        <table class=one-col style="border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto;width: 600px;background-color: #ffffff;font-size: 14px;table-layout: fixed;border-top: 10px solid #42414f;">                            <tbody>                                <tr>                                    <td class=column style="padding: 0;vertical-align: top;text-align: left">                                        <div>                                            <div class=column-top style="font-size: 32px;line-height: 32px">&nbsp;                                            </div>                                        </div>                                        <table class=contents style="border-collapse: collapse;border-spacing: 0;table-layout: fixed;width: 100%">                                            <tbody>                                                <tr>                                                    <td class=padded style="padding: 0;vertical-align: top;padding-left: 32px;padding-right: 32px;word-break: break-word;word-wrap: break-word">                                                        <table class=divider style="border-collapse: collapse;border-spacing: 0;width: 100%">                                                            <tbody>                                                                <tr>                                                                    <td class=inner style="padding: 0;vertical-align: top;padding-bottom: 24px" align=center>                                                                        <table style="border-collapse: collapse;border-spacing: 0;background-color: #e9e9e9;font-size: 2px;line-height: 2px;width: 60px">                                                                            <tbody>                                                                                <tr>                                                                                    <td style="padding: 0;vertical-align: top">&nbsp;                                                                                    </td>                                                                                </tr>                                                                            </tbody>                                                                        </table>                                                                    </td>                                                                </tr>                                                            </tbody>                                                        </table>                                                    </td>                                                </tr>                                            </tbody>                                        </table>                                        <table class=contents style="border-collapse: collapse;border-spacing: 0;table-layout: fixed;width: 100%">                                            <tbody>                                                <tr>                                                    <td class=padded style="padding: 0;vertical-align: top;padding-left: 32px;padding-right: 32px;word-break: break-word;word-wrap: break-word">                                                        <p style="Margin-top: 0;color: #565656;font-family: Georgia, serif;font-size: 16px;line-height: 25px;Margin-bottom: 25px">                                                            Querido ACCOUNT_NAME,                                                        </p>                                                        <p style="Margin-top: 0;color: #565656;font-family: Georgia, serif;font-size: 16px;line-height: 25px;Margin-bottom: 25px">                                                           Tu conversación en EDIRECTORY_TITLE recibió el siguiente mensaje.                                                        </p>                                                        <p style="Margin-top: 0;color: #565656;font-family: Georgia, serif;font-size: 16px;line-height: 25px;Margin-bottom: 25px">                                                           TOPIC_ANSWER                                                        </p>                                                        <p style="Margin-top: 0;color: #565656;font-family: Georgia, serif;font-size: 16px;line-height: 25px;Margin-bottom: 25px">                                                           Puedes verlo en ITEM_URL                                                        </p>                           <p style="Margin-top: 0;color: #565656;font-family: Georgia, serif;font-size: 16px;line-height: 25px;Margin-bottom: 25px">                                                            Saludos,<br>                                                            El equipo de EDIRECTORY_TITLE                                                        </p>                                                    </td>                                                </tr>                                            </tbody>                                        </table>                                        <table class=divider style="border-collapse: collapse;border-spacing: 0;width: 100%">                                            <tbody>                                                <tr>                                                    <td class=inner style="padding: 0;vertical-align: top;padding-bottom: 24px" align=center>                                                        <table style="border-collapse: collapse;border-spacing: 0;background-color: #e9e9e9;font-size: 2px;line-height: 2px;width: 60px">                                                            <tbody>                                                                <tr>                                                                    <td style="padding: 0;vertical-align: top">&nbsp;                                                                    </td>                                                                </tr>                                                            </tbody>                                                        </table>                                                    </td>                                                </tr>                                            </tbody>                                        </table>                                        <div class=column-bottom style="font-size: 8px;line-height: 8px">&nbsp;                                        </div>                                    </td>                                </tr>                            </tbody>                        </table>                    </td>                    <td class=border style="padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e9e9e9;width: 1px">&#8203;                    </td>                </tr>            </tbody>        </table>        <table class=border style="border-collapse: collapse;border-spacing: 0;font-size: 1px;line-height: 1px;background-color: #e9e9e9;Margin-left: auto;Margin-right: auto" width=602>            <tbody>                <tr class=border style="font-size: 1px;line-height: 1px;background-color: #e9e9e9;height: 1px">                    <td class=border style="padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e9e9e9;width: 1px">&#8203;                    </td>                    <td style="padding: 0;vertical-align: top;line-height: 1px">&#8203;                    </td>                    <td class=border style="padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e9e9e9;width: 1px">&#8203;                    </td>                </tr>            </tbody>        </table>        <table class="footer centered" style="border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto;width: 602px">            <tbody>                <tr> </tr>                <tr>                    <td class=border style="padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e9e9e9;width: 1px">&nbsp;                    </td>                </tr>                <tr>                    <td style="padding: 0;vertical-align: top">                        <table style="border-collapse: collapse;border-spacing: 0">                            <tbody>                                <tr>                                    <td class=address style="padding: 0;vertical-align: top;width: 250px;padding-top: 32px;padding-bottom: 64px">                                        <table class=contents style="border-collapse: collapse;border-spacing: 0;table-layout: fixed;width: 100%">                                            <tbody>                                                <tr>                                                    <td class=padded style="padding: 0;vertical-align: top;padding-left: 0;padding-right: 10px;word-break: break-word;word-wrap: break-word;text-align: left;font-size: 12px;line-height: 20px;color: #999;font-family: Georgia, serif">                                                        <div>EDIRECTORY_TITLE</div>                                                    </td>                                                </tr>                                            </tbody>                                        </table>                                    </td>                                    <td class=subscription style="padding: 0;vertical-align: top;width: 620px;padding-top: 32px;padding-bottom: 64px">                                        <table class=contents style="border-collapse: collapse;border-spacing: 0;table-layout: fixed;width: 100%">                                            <tbody>                                                <tr>                                                    <td class=padded style="padding: 0;vertical-align: top;padding-left: 10px;padding-right: 0;word-break: break-word;word-wrap: break-word;font-size: 12px;line-height: 20px;color: #999;font-family: Georgia, serif;text-align: right">                                                        <div>Este es un correo electronico generado automaticamente, por favor no responda &nbsp; &nbsp;</div>                                                    </td>                                                </tr>                                            </tbody>                                        </table>                                    </td>                                </tr>                            </tbody>                        </table>                    </td>                </tr>            </tbody>        </table>        <table class="header centered" style="border-collapse: collapse;border-spacing: 0;Margin-left: auto;Margin-right: auto;width: 602px">            <tbody>                <tr>                    <td class=border style="padding: 0;vertical-align: top;font-size: 1px;line-height: 1px;background-color: #e9e9e9;width: 1px">&nbsp;                    </td>                </tr>            </tbody>        </table>    </center></body></html>',
                'useVariables' => 'ACCOUNT_NAME,ACCOUNT_USERNAME,DEFAULT_URL,SITEMGR_EMAIL,EDIRECTORY_TITLE,TOPIC_ANSWER,PROFILE_URL,ITEM_URL,LOGO',
            ],
        ];

        $repository = $manager->getRepository('WebBundle:EmailNotification');

        foreach ($standardInserts as $emailNotificationInsert) {
            $query = $repository->findOneBy([
                'id' => $emailNotificationInsert['id'],
            ]);

            $emailNotification = new EmailNotification();

            /* checks if the email_notification already exist so they can be updated or added */
            if ($query) {
                $emailNotification = $query;
            }

            $emailNotification->setId($emailNotificationInsert['id']);
            $emailNotification->setEmail($emailNotificationInsert['email']);
            $emailNotification->setDays($emailNotificationInsert['days']);
            $emailNotification->setDeactivate($emailNotificationInsert['deactivate']);
            $emailNotification->setUpdated($emailNotificationInsert['updated']);
            $emailNotification->setBcc($emailNotificationInsert['bcc']);
            $emailNotification->setSubject($emailNotificationInsert['subject']);
            $emailNotification->setContentType($emailNotificationInsert['content_type']);
            $emailNotification->setBody($emailNotificationInsert['body']);
            $emailNotification->setUseVariables($emailNotificationInsert['useVariables']);

            $manager->persist($emailNotification);
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1;
    }
}
