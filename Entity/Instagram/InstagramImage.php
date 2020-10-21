<?php

namespace ICS\SocialNetworkBundle\Entity\Instagram;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(schema="socialnetwork")
 */
class InstagramImage extends AbstractInstagramMedia
{
    /**
     * @ORM\ManyToOne(targetEntity="ICS\MediaBundle\Entity\MediaImage")
     */
    private $image;

    public function __construct($jsonResult=null)
    {
        parent::__construct($jsonResult);
    }
}