<?php

namespace ICS\SocialNetworkBundle\Entity\Instagram;


class InstagramImage extends AbstractInstagramMedia
{
    private $image;

    public function __construct($jsonResult)
    {
        parent::__construct($jsonResult);
    }
}