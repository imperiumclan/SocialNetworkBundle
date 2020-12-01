<?php

namespace ICS\SocialNetworkBundle\Entity\Instagram;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(schema="socialnetwork")
 */
class InstagramVideo extends AbstractInstagramMedia
{
    /**
     * @ORM\Column(type="string", length=2048)
     */
    private $videoUrl;
    /**
     * @ORM\ManyToOne(targetEntity="ICS\MediaBundle\Entity\MediaVideo",cascade={"persist","remove"})
     */
    private $video;

    public function __construct($jsonResult = null)
    {
        parent::__construct($jsonResult);

        if (null != $jsonResult) {
            $this->videoUrl = $jsonResult->video_url;
        }
    }

    /**
     * Get the value of videoUrl.
     */
    public function getVideoUrl()
    {
        return $this->videoUrl;
    }

    /**
     * Get the value of video.
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * Set the value of video.
     *
     * @return self
     */
    public function setVideo($video)
    {
        $this->video = $video;

        return $this;
    }
}
