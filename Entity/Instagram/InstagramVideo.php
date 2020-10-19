<?php

namespace ICS\SocialNetworkBundle\Entity\Instagram;

use ICS\SocialNetworkBundle\Service\InstagramClient;

class InstagramVideo extends AbstractInstagramMedia
{
    private $videoUrl;
    private $video;

    public function __construct($jsonResult,InstagramClient $client=null)
    {
        parent::__construct($jsonResult);

        if($client!=null && $response=$client->getApiUrl($this->getMediaApiUrl()))
        {
            $this->videoUrl=$response->graphql->shortcode_media->video_url;
            //dump($response);
        }
    }
}