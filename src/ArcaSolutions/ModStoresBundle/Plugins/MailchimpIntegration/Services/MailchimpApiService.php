<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\MailchimpIntegration\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;

class MailchimpApiService
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $mailchimpKey;
    /**
     * @var string
     */
    private $mailchimpEndpoint;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setKey($key = '')
    {
        if ($key) {
            $this->mailchimpKey = $key;
            list(, $str_api) = explode('-', $this->mailchimpKey);

            $this->mailchimpEndpoint = 'https://'.$str_api.'.api.mailchimp.com/3.0';
        }
    }

    public function delete($method, $args = [], $timeout = 10)
    {
        return $this->makeRequest('delete', $method, $args, $timeout);
    }

    public function makeRequest($path, $method, $args = [], $timeout = 10)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->mailchimpEndpoint.'/'.$method);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-MCAPI/MailChimp-API/3.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/vnd.api+json',
            'Content-Type: application/vnd.api+json',
            'Authorization: apikey '.$this->mailchimpKey,
        ]);

        switch ($path) {
            case 'post':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($args, JSON_FORCE_OBJECT));
                break;
            case 'get':
                $query = http_build_query($args);
                curl_setopt($ch, CURLOPT_URL, $this->mailchimpEndpoint.'/'.$method.'?'.$query);
                break;
            case 'delete':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'patch':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($args, JSON_FORCE_OBJECT));
                break;
        }

        $result = curl_exec($ch);
        curl_close($ch);

        return $result ? json_decode($result, true) : false;
    }

    public function get($method, $args = [], $timeout = 10)
    {
        return $this->makeRequest('get', $method, $args, $timeout);
    }

    public function patch($method, $args = [], $timeout = 10)
    {
        return $this->makeRequest('patch', $method, $args, $timeout);
    }

    public function post($method, $args = [], $timeout = 10)
    {
        return $this->makeRequest('post', $method, $args, $timeout);
    }

    public function put($method, $args = [], $timeout = 10)
    {
        return $this->makeRequest('put', $method, $args, $timeout);
    }
}
