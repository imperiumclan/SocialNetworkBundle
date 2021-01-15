<?php

namespace ICS\SocialNetworkBundle\Service;

use ICS\SocialNetworkBundle\Entity\Instagram\AbstractInstagramMedia;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramAccount;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramSimpleAccount;
use ICS\MediaBundle\Service\MediaClient;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class TiktokClient extends AbstractSocialClient
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container,"TikTok");
        //$this->mediaClient = $client;
    }

    public function search(String $search,$verifiedOnly=false)
    {
        $url="https://www.tiktok.com/node/share/user/@yellz0";
        return $this->getApiUrl($url);
    }

    public function getUser($username)
    {
        $url="https://www.tiktok.com/node/share/user/@".trim($username);
        return $this->getApiUrl($url);
    }

    public function getItems($username)
    {
        $user=$this->getUser($username);
        $url="https://m.tiktok.com/api/item_list/";
        $url.="?aid=1988";
        $url.="&cookie_enabled=true";
        $url.="&screen_width=1920";
        $url.="&screen_height=1080";
        $url.="&browser_language=fr";
        $url.="&browser_platform=Win32";
        $url.="&browser_name=Mozilla";
        $url.="&browser_version=5.0+(Windows)&browser_online=true";
        $url.="&ac=&timezone_name=Europe%2FParis";
        $url.="&referer=https://www.tiktok.com/fr/";
        $url.="&user_agent=Mozilla/5.0+(Windows+NT+10.0%3B+Win64%3B+x64%3B+rv:82.0)+Gecko%2F20100101+Firefox/82.0";
        $url.="&app_name=tiktok_web";
        $url.="&device_platform=web";
        $url.="&page_referer=https://www.tiktok.com/discover?lang=fr&priority_region=FR";
        $url.="&verifyFp=verify_khg78lre_hUPatsYb_iAQl_4gcM_9pJb_upu6ENEs4dxA&appId=1233&region=FR";
        $url.="&appType=m";
        $url.="&isAndroid=false";
        $url.="&isMobile=false";
        $url.="&isIOS=false";
        $url.="&OS=windows";
        $url.="&did=6883045983853364742";
        $url.="&tt-web-region=FR";
        $url.="&uid=6817449658747700230";
        $url.="&count=30";
        // $url.="&id=".$user->userInfo->user->id;
        $url.="&id=6532047219451756546";
        $url.="&secUid=MS4wLjABAAAAP6CQvUZKmh5Aiy_6fgxksnKrhci4y0P_nZYg7dmTxKBTsvYg0HXoFbZHn8_UtFu3";
        // $url.="&secUid=".$user->userInfo->user->secUid;
        $url.="&maxCursor=0";
        $url.="&minCursor=0";
        $url.="&sourceType=8";
        $url.="&language=fr";
        $url.="&_signature=_02B4Z6wo00f01yK-1hQAAICDYzRsTnUHx1civtKAAJcKfe";

        $response=$this->client->request('GET',$url);
        $results=array();
        
        if($response->getStatusCode()== 200) {
            $results=json_decode($response->getContent());        
        }
        
        return $results;
    }
}