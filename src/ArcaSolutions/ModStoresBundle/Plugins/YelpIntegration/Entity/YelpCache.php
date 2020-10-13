<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\YelpIntegration\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Example
 *
 * @ORM\Table(name="YelpCache")
 * @ORM\Entity(repositoryClass="ArcaSolutions\ModStoresBundle\Plugins\YelpIntegration\Repository\YelpCacheRepository")
 * @ORM\HasLifecycleCallbacks
 */
class YelpCache
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
     * @var string
     *
     * @ORM\Column(name="search_criteria", type="string", length=255, nullable=false)
     */
    private $searchCriteria;

    /**
     * @var string
     *
     * @ORM\Column(name="response", type="json_array")
     */
    private $response;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        return $this->id = $id;
    }

    /**
     * @return string
     */
    public function getSearchCriteria()
    {
        return $this->searchCriteria;
    }

    /**
     * @param string $searchCriteria
     */
    public function setSearchCriteria($searchCriteria)
    {
        return $this->searchCriteria = $searchCriteria;
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param string $response
     */
    public function setResponse($response)
    {
        return $this->response = $response;
    }
}