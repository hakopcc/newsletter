<?php

namespace ArcaSolutions\ListingBundle\Services;

use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\ListingBundle\Entity\ListingFieldValue;
use ArcaSolutions\ListingBundle\Entity\ListingTField;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class ListingFieldValueService
{
    /**
     * ContainerInterface
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param null $listingId
     */
    public function saveListingFieldValues($listingId = null)
    {
        if(empty($listingId)) {
            $listingId = (int)$_POST['id'];
        }

        if(!empty($listingId)) {
            $em = $this->container->get('doctrine')->getManager();

            $checkBoxValues = $this->container->get('doctrine')->getRepository('ListingBundle:ListingFieldValue')->getCheckBoxFieldValuesByListingId($listingId);

            if(!empty($checkBoxValues)) {
                foreach ($checkBoxValues as $checkBoxValue) {
                    $em->remove($checkBoxValue);
                }

                $em->flush();
            }

            if (!empty($_POST['listingField'])) {
                foreach ($_POST['listingField'] as $fieldId => $fieldValue) {
                    $fieldValueEntity = $this->container->get('doctrine')->getRepository('ListingBundle:ListingFieldValue')->findOneBy([
                        'listingTFieldId' => $fieldId,
                        'listingId' => $listingId
                    ]);

                    /** @var ListingTField $listingTField */
                    $listingTField = $this->container->get('doctrine')->getRepository('ListingBundle:ListingTField')->find($fieldId);

                    if ($listingTField === null) {
                        continue;
                    }

                    if (empty($fieldValueEntity)) {
                        $fieldValueEntity = new ListingFieldValue();

                        $fieldValueEntity->setListingTField($listingTField);

                        /** @var Listing $listing */
                        $listing = $this->container->get('doctrine')->getRepository('ListingBundle:Listing')->find($listingId);

                        if ($listing === null) {
                            continue;
                        }

                        $fieldValueEntity->setListing($listing);
                    }

                    if($listingTField->getFieldType() === 'range') {
                        if($fieldValue > 0) {
                            $fieldValueEntity->setValue($fieldValue);
                            $em->persist($fieldValueEntity);
                        } else {
                            $em->remove($fieldValueEntity);
                        }
                    } else {
                        $fieldValueEntity->setValue($fieldValue);
                        $em->persist($fieldValueEntity);
                    }
                }

                $em->flush();
            }
        }
    }

    /**
     * @param $listingId
     * @return array
     */
    public function getFieldValues($listingId)
    {
        $fieldValues = [];

        if(!empty($listingId)) {
            $listingFieldValues = $this->container->get('doctrine')->getRepository('ListingBundle:ListingFieldValue')->findBy([
                'listingId' => $listingId
            ]);

            /** @var ListingFieldValue $fieldValue */

            if (is_array($listingFieldValues)) {
                foreach ($listingFieldValues as $fieldValue) {
                    if ($fieldValue->getListingTField()) {
                        $fieldValues[$fieldValue->getListingTField()->getId()] = $fieldValue->getValue();
                    }
                }
            }
        }

        return $fieldValues;
    }

    /**
     * @param $errors
     * @param $members
     * @param array $array_fields
     */
    public function validateFields(&$errors, $members, $array_fields = [])
    {
        $locale = null;
        if(!$members) {
            $locale = substr($this->container->get('settings')->getSetting('sitemgr_language'), 0, 2);
        }

        if(!empty($_POST['listingField']) && !empty($array_fields)) {
            foreach($_POST['listingField'] as $id => $value) {
                /** @var ListingTField $listingTField */
                $listingTField = $this->container->get('doctrine')->getRepository('ListingBundle:ListingTField')->find($id);
                if($array_fields['listingtfield_id'] && ($listingTField !== null) && ($listingTField->getFieldType()!="range"?empty($value):!isset($value)) && $listingTField->isRequired() && in_array($listingTField->getId(), $array_fields['listingtfield_id'])) {
                    $errors[] = '&#149;&nbsp;' . $this->container->get('translator')->trans('%field% is required', ['%field%' => $listingTField->getLabel()], 'administrator', $locale);
                }

                if(($listingTField !== null) && empty($value) && $array_fields['listingtfieldgroup_id'] && $listingTField->getListingTFieldGroup() && $listingTField->isRequired() && in_array($listingTField->getListingTFieldGroup()->getId(), $array_fields['listingtfieldgroup_id'])) {
                    $errors[] = '&#149;&nbsp;' . $this->container->get('translator')->trans('%field% is required', ['%field%' => $listingTField->getLabel()], 'administrator', $locale);
                }
            }
        }
    }
}
