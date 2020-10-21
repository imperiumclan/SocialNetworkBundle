<?php

namespace ICS\SocialNetworkBundle\Entity\Instagram;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(schema="socialnetwork")
 * @ORM\MappedSuperclass
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
*/
class InstagramSimpleAccount
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    protected $id;
    /**
     * @ORM\Column(type="string")
     */
    protected $username;
    /**
     * @ORM\Column(type="string")
     */
    protected $fullname;
    /**
     * @ORM\Column(type="string")
     */
    protected $profilePicUrl;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $private;
    /**
     * @ORM\Column(type="boolean")
     */
    protected $verified;

    public function __construct($jsonResult)
    {
        if(property_exists($jsonResult,'id'))
        {
            $this->id=$jsonResult->id;
        }
        else
        {
            $this->id=$jsonResult->pk;
        }
        $this->username=$jsonResult->username;
        $this->fullname=$jsonResult->full_name;
        $this->profilePicUrl=$jsonResult->profile_pic_url;
        $this->private=$jsonResult->is_private=='true';
        $this->verified=$jsonResult->is_verified=='true';
    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of username
     */ 
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get the value of fullname
     */ 
    public function getFullname()
    {
        return $this->fullname;
    }

    /**
     * Get the value of profilePicUrl
     */ 
    public function getProfilePicUrl()
    {
        return $this->profilePicUrl;
    }

    /**
     * Get the value of isPrivate
     */ 
    public function isPrivate()
    {
        return $this->private;
    }

    /**
     * Get the value of isVefified
     */ 
    public function isVerified()
    {
        return $this->verified;
    }

    public function getUrl()
    {
        return 'https://www.instagram.com/'.$this->getUsername();
    }

    public function getApiUrl()
    {
        return 'https://www.instagram.com/'.$this->getUsername()."?__a=1";
    }
}