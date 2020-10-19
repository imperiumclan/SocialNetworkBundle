<?php

namespace ICS\SocialNetworkBundle\Entity\Instagram;

use Doctrine\Common\Collections\ArrayCollection;
use ICS\SocialNetworkBundle\Service\InstagramClient;

class InstagramSideCar extends AbstractInstagramMedia
{
    private $images;
    private $imagesUrls;

    public function __construct($jsonResult,InstagramClient $client=null)
    {
        parent::__construct($jsonResult);
        $this->images = new ArrayCollection();
        $this->imagesUrls = new ArrayCollection();

        if($client!=null && $response=$client->getApiUrl($this->getMediaApiUrl()))
        {
            foreach($response->graphql->shortcode_media->edge_sidecar_to_children->edges as $sidecar)
            {
                $this->imagesUrls->add($sidecar->node->display_url);
            }
            //dump($response);
        }
    }
}