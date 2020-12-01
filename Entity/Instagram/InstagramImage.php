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
    public function __construct($jsonResult = null)
    {
        parent::__construct($jsonResult);
    }
}
