<?php

namespace ICS\SocialNetworkBundle\Service;

use ICS\SocialNetworkBundle\Entity\Instagram\AbstractInstagramMedia;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramAccount;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramSimpleAccount;
use ICS\MediaBundle\Service\MediaClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class TiktokClient extends AbstractSocialClient
{
    public function __construct(ContainerInterface $container, HttpClientInterface $httpclient)
    {
        parent::__construct(
            $container,
            "TikTok",
            $httpclient
        );
        //$this->mediaClient = $client;
    }

    public function search(String $search, $verifiedOnly = false)
    {
        $url = "https://www.tiktok.com/node/share/user/@yellz0";
        return $this->getApiUrl($url);
    }

    public function getUser($username)
    {
        $url = "https://www.tiktok.com/node/share/user/@" . trim($username);
        return $this->getApiUrl($url);
    }

    public function getItems($username)
    {

        $user = $this->getUser($username);

        // $secUid = 'MS4wLjABAAAAP6CQvUZKmh5Aiy_6fgxksnKrhci4y0P_nZYg7dmTxKBTsvYg0HXoFbZHn8_UtFu3';
        $secUid = $user->userInfo->user->secUid;
        $url = "https://m.tiktok.com/api/post/item_list/";
        $url .= "?aid=1988";
        $url .= "&secUid=" . $secUid;
        $url .= "&count=30";
        $url .= "&cursor=0";
        $url .= "&uid=" . $user->userInfo->user->id;
        $response = $this->client->request('GET', $url);
        $results = array();

        if ($response->getStatusCode() == 200) {
            $results = json_decode($response->getContent());
        }

        return $results;
    }
}
