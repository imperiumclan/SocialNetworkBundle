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

    protected function __construct(ContainerInterface $container,string $socialNetworkName)
    {
        $store = new Store($container->getParameter('kernel.project_dir').'/var/cache/WebServices/'.$socialNetworkName.'/');
        $this->client=new CurlHttpClient();

        $this->headers =[
            'headers' => [
                'Cache-Control' => 'no-cache',
                'Connection' => 'keep-alive',
                'User-Agent' =>'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:83.0) Gecko/20100101 Firefox/83.0',
                'content-type'=> 'application/json; charset=utf-8',
            ],
        ];
        //$this->headers=[];

        $this->client = new CachingHttpClient($this->client, $store);
        $this->container = $container;
    }

    public function getApiUrl($url)
    {
        $response=$this->client->request('GET',$url);
        if($response->getStatusCode()== 200)
        {
            return json_decode($response->getContent());
        }

        return false;
    }

    abstract public function search(String $seacrh,$verifiedOnly=false);
}