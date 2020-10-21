<?php

namespace ICS\SocialNetworkBundle\Service;

use ICS\SocialNetworkBundle\Entity\Instagram\AbstractInstagramMedia;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramAccount;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramSimpleAccount;
use ICS\MediaBundle\Service\MediaClient;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class TwitterClient extends AbstractSocialClient
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container,"Twitter");
        //$this->mediaClient = $client;
    }

    public function search(String $search,$verifiedOnly=false)
    {
        $url="https://api.twitter.com/2/tweets/search/recent?query=from:".$search;
        $response=$this->client->request('GET',$url);

        $results=array();
        dump($response);
        if($response->getStatusCode()== 200) {

            dump($response->getContent());

        }

        return $results;
    }
}