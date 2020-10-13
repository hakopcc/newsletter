<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\YelpIntegration\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Client as HttpClient;
use stdClass;
use Symfony\Component\DependencyInjection\Container;

class ApiService
{
    /**
     * API host url
     *
     * @var string
     */
    protected $apiHost = 'api.yelp.com';

    /**
     * Default search term
     *
     * @var string
     */
    protected $defaultTerm = 'business';

    /**
     * Default location
     *
     * @var string
     */
    protected $defaultLocation = 'USA';

    /**
     * Default search limit
     *
     * @var integer
     */
    protected $searchLimit = 10;

    /**
     * Search path
     *
     * @var string
     */
    protected $searchPath = '/v3/businesses/search';

    /**
     * Business path
     *
     * @var string
     */
    protected $businessPath = '/v3/businesses/%s';

    /**
     * Phone search path
     *
     * @var string
     */
    protected $phoneSearchPath = '/v3/businesses/search/phone';

    /**
     * Reviews path
     *
     * @var string
     */
    protected $reviewsPath = '/v3/businesses/%s/reviews';

    /**
     * Transactions path
     *
     * @var string
     */
    protected $transactionsPath = '/v3/transactions/%s/search';

    /**
     * Autocomplete path
     *
     * @var string
     */
    protected $autocompletePath = '/v3/autocomplete';

    /**
     * [$httpClient description]
     *
     * @var Client
     */
    protected $httpClient;

    /**
     * @var Container
     */
    private $container;

    /**
     * Used to check yelp key on save
     *
     * @var string
     */
    private $yelpKey;

    /**
     * Create new client
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->createHttpClient();
    }

    /**
     * Builds and sets a preferred http client.
     *
     * @return ApiService
     */
    protected function createHttpClient()
    {
        return $this->setHttpClient(new HttpClient());
    }

    /**
     * Updates the yelp client's http client to the given http client. Client.
     *
     * @param HttpClient $client
     *
     * @return ApiService
     */
    public function setHttpClient(HttpClient $client)
    {
        $this->httpClient = $client;

        return $this;
    }

    /**
     * Get autocomplete suggestions
     *
     * @param array $attributes
     *
     * @return stdClass|array
     * @throws Exception
     */
    public function getAutocompleteSuggestions(array $attributes = [])
    {
        $path = $this->autocompletePath.'?'.$this->prepareQueryParams($attributes);

        return $this->request($path);
    }

    /**
     * Updates query params array to apply yelp specific formatting rules.
     *
     * @param array $params
     *
     * @return string
     * @throws Exception
     */
    protected function prepareQueryParams(array $params = [])
    {
        if(!array_key_exists('locale',$params)){
            $localeValue = null;
            if(!empty($this->container)) {
                $multiDomainInformation = $this->container->get('multi_domain.information');
                if(!empty($multiDomainInformation)) {
                    $localeFromDomain = $multiDomainInformation->getLocale();
                    if (!empty($localeFromDomain))
                    {
                        $languageLibrary = [
                            "en_us" => "en_US",
                            "pt_br" => "pt_BR",
                            "es_es" => "es_ES",
                            "tr_tr" => "tr_TR",
                            "ge_ge" => "de_DE",
                            "fr_fr" => "fr_FR",
                            "it_it" => "it_IT",
                        ];
                        if(array_key_exists($localeFromDomain,$languageLibrary)){
                            $localeValue = $languageLibrary[$localeFromDomain];
                        }
                        unset($languageLibrary);
                    }
                    unset($locale);
                }
                unset($multiDomainInformation);
            }
            $params['locale']=$localeValue;
        }

        array_walk($params, function ($value, $key) use (&$params) {
            if (is_bool($value)) {
                $params[$key] = $value ? 'true' : 'false';
            }
        });

        return http_build_query($params);
    }

    /**
     * Makes a request to the Yelp API and returns the response
     *
     * @param string $path The path of the APi after the domain
     * @return stdClass The JSON response from the request
     * @throws Exception
     */
    protected function request($path)
    {
        $privateKey = $this->yelpKey ?: $this->container->get('settings')->getDomainSetting('yelpAppSecret');
        $url = $this->buildUnsignedUrl($this->apiHost, $path);

        $request = $this->httpClient->createRequest(
            'get',
            $url,
            [
                'headers' => ['Authorization' => 'Bearer '.$privateKey],
            ]
        );

        try {
            $request = $this->httpClient->send($request)->json();
        } catch (Exception $e) {
            $logger = $this->container->get('logger');
            $logger->critical('Request YELP: '.$e->getMessage());

            return false;
        }

        return $request;
    }

    /**
     * Build unsigned url
     *
     * @param string $host
     * @param string $path
     *
     * @return string   Unsigned url
     */
    protected function buildUnsignedUrl($host, $path)
    {
        return 'https://'.$host.$path;
    }

    /**
     * Get reviews for business by ID string
     *
     * @param string $businessId
     *
     * @return stdClass|array
     * @throws Exception
     */
    public function getReviews($businessId)
    {
        $queryParameters = $this->prepareQueryParams();

        $path = sprintf($this->reviewsPath, urlencode($businessId)).'?'.$queryParameters;

        return $this->request($path);
    }

    const allowedRequestTypes = ['search','business','phone','reviews','transactions','autocomplete'];

    /**
     * @param $path string
     * @param $type string
     * @throws Exception
     */
    public function injectLocaleQueryEntry(&$path, $type){
        if(!in_array($type, self::allowedRequestTypes)){
            return;
        }
        $localeValue = null;
        if(!empty($this->container)) {
            $multiDomainInformation = $this->container->get('multi_domain.information');
            if(!empty($multiDomainInformation)) {
                $localeFromDomain = $multiDomainInformation->getLocale();
                if (!empty($localeFromDomain))
                {
                    $languageLibrary = [
                        "en_us" => "en_US",
                        "pt_br" => "pt_BR",
                        "es_es" => "es_ES",
                        "tr_tr" => "tr_TR",
                        "ge_ge" => "de_DE",
                        "fr_fr" => "fr_FR",
                        "it_it" => "it_IT",
                    ];
                    if(array_key_exists($localeFromDomain,$languageLibrary)){
                        $localeValue = $languageLibrary[$localeFromDomain];
                    }
                    unset($languageLibrary);
                }
                unset($locale);
            }
            unset($multiDomainInformation);
        }
        switch ($type){
            case 'search':
                break;
            case 'phone':
                break;
            case 'transactions':
                break;
            case 'autocomplete':
                break;
            case 'reviews':
            case 'business':
                $path .= '?locale='.$localeValue;
                break;        }
        unset($localeValue);
    }

    /**
     * Get transactions - food delivery in the US
     *
     * @param array $attributes
     *
     * @return stdClass|array
     * @throws Exception
     */
    public function getTransactions(array $attributes = [])
    {
        $transactionType = 'delivery';

        if (isset($attributes['transaction_type'])) {
            $transactionType = $attributes['transaction_type'];
            unset($attributes['transaction_type']);
        }

        $path = $this->prepareQueryParams($attributes);

        $path = sprintf($this->transactionsPath, urlencode($transactionType)).'?'.$path;

        return $this->request($path);
    }

    /**
     * Query the Business API by business id
     *
     * @param string $businessId The ID of the business to query
     *
     * @return stdClass The JSON response from the request
     * @throws Exception
     */
    public function getBusiness($businessId)
    {
        $queryParameters = $this->prepareQueryParams();

        $businessPath = sprintf($this->businessPath, urlencode($businessId)).'?'.$queryParameters;

        return $this->request($businessPath);
    }

    /**
     * Query the Search API by a search term and location
     *
     * @param array $attributes Query attributes
     *
     * @return stdClass The JSON response from the request
     * @throws Exception
     */
    public function search(array $attributes = [])
    {
        $query_string = $this->buildQueryParamsForSearch($attributes);
        $searchPath = $this->searchPath.'?'.$query_string;

        return $this->request($searchPath);
    }

    /**
     * Build query stricing params using defaults for search() functionality
     *
     * @param array $attributes
     *
     * @return string
     * @throws Exception
     */
    public function buildQueryParamsForSearch(array $attributes = [])
    {
        $defaultAttributes = [
            'term'     => $this->defaultTerm,
            'location' => $this->defaultLocation,
            'limit'    => $this->searchLimit,
        ];
        if (!array_key_exists('location', $attributes) &&
            (
                array_key_exists('latitude', $attributes)&&
                array_key_exists('longitude', $attributes)&&
                is_numeric($attributes['latitude'])&&
                is_numeric($attributes['longitude'])
            ))
        {
            unset($defaultAttributes['location']);
        }

        return $this->prepareQueryParams(array_merge($defaultAttributes, $attributes));
    }

    /**
     * Search for businesses by phone number
     *
     * @param string phone
     *
     * @return stdClass The JSON response from the request
     * @throws Exception
     */
    public function searchByPhone($phone)
    {
        $path = $this->phoneSearchPath.'?'.$this->prepareQueryParams(['phone' => $phone]);

        return $this->request($path);
    }

    /**
     * Set default location
     *
     * @param string $location
     *
     * @return ApiService
     */
    public function setDefaultLocation($location)
    {
        $this->defaultLocation = $location;

        return $this;
    }

    /**
     * Set default term
     *
     * @param string $term
     *
     * @return ApiService
     */
    public function setDefaultTerm($term)
    {
        $this->defaultTerm = $term;

        return $this;
    }

    public function setHttpClientVerify($isSecure)
    {
        $this->httpClient->setDefaultOption('verify', $isSecure);

        return $this;
    }

    /**
     * Set search limit
     *
     * @param integer $limit
     *
     * @return ApiService
     */
    public function setSearchLimit($limit)
    {
        if (is_int($limit)) {
            $this->searchLimit = $limit;
        }

        return $this;
    }

    /**
     * Retrives the value of a given property from the client.
     *
     * @param string $property
     *
     * @return mixed|null
     */
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }

        return null;
    }

    /**
     * Check if private key is set
     *
     * @return boolean
     * @throws Exception
     */
    public function hasPrivateKey()
    {
        return !empty($this->container->get('settings')->getDomainSetting('yelpAppSecret'));
    }

    /**
     * @param string $yelpKey
     * @return $this
     */
    public function setYelpKey($yelpKey)
    {
        $this->yelpKey = $yelpKey;

        return $this;
    }
}
