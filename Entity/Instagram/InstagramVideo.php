<?php

namespace ICS\SocialNetworkBundle\Entity\Instagram;

use ICS\SocialNetworkBundle\Service\InstagramClient;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(schema="socialnetwork")
 */
class InstagramVideo extends AbstractInstagramMedia
{
    /**
     * @ORM\Column(type="string")
     */
    private $videoUrl;
    /**
     * @ORM\ManyToOne(targetEntity="ICS\MediaBundle\Entity\MediaVideo")
     */
    private $video;

    public function __construct($jsonResult=null,InstagramClient $client=null)
    {
        parent::__construct($jsonResult);

        if($client!=null && $response=$client->getApiUrl($this->getMediaApiUrl()))
        {
            $this->videoUrl=$response->graphql->shortcode_media->video_url;
            //dump($response);
        }
    }

    /**
     * Get the value of videoUrl
     */ 
    public function getVideoUrl()
    {
        return $this->videoUrl;
    }

    /**
     * Get the value of video
     */ 
    public function getVideo()
    {
        return $this->video;
    }
}