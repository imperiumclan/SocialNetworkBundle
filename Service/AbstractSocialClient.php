<?php

namespace ICS\SocialNetworkBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpClient\CachingHttpClient;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpKernel\HttpCache\Store;

abstract class AbstractSocialClient
{
    protected $client;

    protected $container;

    protected $headers;

    protected function __construct(ContainerInterface $container, string $socialNetworkName)
    {
        $store = new Store($container->getParameter('kernel.project_dir').'/var/cache/WebServices/'.$socialNetworkName.'/');
        $this->client = new CurlHttpClient();

        $this->headers = [
            'headers' => [
                'Cache-Control' => 'no-cache',
                'Connection' => 'keep-alive',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:83.0) Gecko/20100101 Firefox/83.0',
            ],
        ];
        //$this->headers=[];

        $this->client = new CachingHttpClient($this->client, $store);
        $this->container = $container;
    }

    public function getApiUrl($url, $requestOptions = [])
    {
        $options = '?';

        foreach ($requestOptions as $key => $opt) {
            $options .= $key.'='.$opt.'&';
        }
        $options = substr($options, 0, strlen($options) - 1);

        $response = $this->client->request('GET', $url.$options);
        $contentType = $response->getHeaders()['content-type'][0];
        if (200 == $response->getStatusCode() && 'application/json; charset=utf-8' == $contentType) {
            return json_decode($response->getContent());
        }

        return;
    }

    abstract public function search(string $seacrh, $verifiedOnly = false);
}
