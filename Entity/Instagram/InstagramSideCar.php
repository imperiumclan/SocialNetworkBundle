<?php

namespace ICS\SocialNetworkBundle\Entity\Instagram;

use Doctrine\Common\Collections\ArrayCollection;
use ICS\SocialNetworkBundle\Service\InstagramClient;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(schema="socialnetwork")
 */
class InstagramSideCar extends AbstractInstagramMedia
{
    /**
     * @ORM\ManyToMany(targetEntity="ICS\MediaBundle\Entity\MediaImage",cascade={"persist","remove"})
     */
    private $images;
    /**
     * @ORM\Column(type="json")
     */
    private $imagesUrls;

    public function __construct($jsonResult=null,InstagramClient $client=null)
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
        }
    }

    /**
     * Get the value of images
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Get the value of imagesUrls
     */
    public function getImagesUrls()
    {
        return $this->imagesUrls;
    }
}