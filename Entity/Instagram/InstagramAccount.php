<?php

namespace ICS\SocialNetworkBundle\Entity\Instagram;

use ICS\SocialNetworkBundle\Service\InstagramClient;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(schema="socialnetwork")
 */
class InstagramAccount extends InstagramSimpleAccount
{   
    /**
     * @ORM\ManyToOne(targetEntity="ICS\MediaBundle\Entity\MediaImage")
     */ 
    private $profilePic=null;
    /**
     * @ORM\Column(type="string")
     */
    private $facebookPage=null;
    /**
     * @ORM\Column(type="text")
     */
    private $biography;
    /**
     * @ORM\Column(type="string")
     */
    private $externalUrl;
    /**
     * @ORM\Column(type="integer")
     */
    private $FollowerCount;
    /**
     * @ORM\Column(type="json")
     */
    private $relatedProfiles;
    /**
     * @ORM\ManyToMany(targetEntity="ICS\SocialNetworkBundle\Entity\Instagram\AbstractInstagramMedia")
     */
    private $publications;

    public function __construct($jsonResult)
    {
        parent::__construct($jsonResult);

        // Create Collections
        $this->relatedProfiles = new ArrayCollection();
        $this->publications = new ArrayCollection();

        $this->biography = InstagramClient::TransformToLink($jsonResult->biography);
        
        $this->FollowerCount = $jsonResult->edge_followed_by->count;
        $this->externalUrl = $jsonResult->external_url;

        $this->facebookPage = $jsonResult->connected_fb_page;

        // Change profile Picture to HD version
        $this->profilePicUrl = $jsonResult->profile_pic_url_hd;

        // Related profiles management
        $related=$jsonResult->edge_related_profiles->edges;
        foreach($related as $relatedUser)
        {
            $this->relatedProfiles->add(new InstagramSimpleAccount($relatedUser->node));
        }
        
    }

    /**
     * Get the value of biography
     */ 
    public function getBiography()
    {
        return $this->biography;
    }

    /**
     * Get the value of externalUrl
     */ 
    public function getExternalUrl()
    {
        return $this->externalUrl;
    }

    /**
     * Get the value of FollowerCount
     */ 
    public function getFollowerCount()
    {
        return $this->FollowerCount;
    }

    /**
     * Get the value of facebookPage
     */ 
    public function getFacebookPage()
    {
        return $this->facebookPage;
    }

    /**
     * Get the value of publications
     */ 
    public function getPublications()
    {
        return $this->publications;
    }

    /**
     * Get the value of profilePic
     */ 
    public function getProfilePic()
    {
        return $this->profilePic;
    }

    /**
     * Set the value of profilePic
     *
     * @return  self
     */ 
    public function setProfilePic($profilePic)
    {
        $this->profilePic = $profilePic;

        return $this;
    }

    /**
     * Get the value of relatedProfiles
     */ 
    public function getRelatedProfiles()
    {
        return $this->relatedProfiles;
    }
}