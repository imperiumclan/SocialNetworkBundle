<?php
namespace ICS\SocialNetworkBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="instagram_account",schema="public")
*/
class AtlantisInstaAccount {

    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     */
    private $id;
    /**
     * @ORM\Column(type="string")
     */
    private $username;




    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the value of username
     *
     * @return  self
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }
}