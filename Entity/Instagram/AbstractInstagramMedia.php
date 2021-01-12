<?php

namespace ICS\SocialNetworkBundle\Entity\Instagram;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use ICS\SocialNetworkBundle\Service\InstagramClient;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(schema="socialnetwork")
 * @ORM\MappedSuperclass
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 */
abstract class AbstractInstagramMedia
{
    public const INSTAGRAM_MEDIA_IMAGE = 'GraphImage';
    public const INSTAGRAM_MEDIA_VIDEO = 'GraphVideo';
    public const INSTAGRAM_MEDIA_SIDECAR = 'GraphSidecar';

    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     */
    private $id;
    /**
     * @ORM\Column(type="string")
     */
    private $shortCode;
    /**
     * @ORM\Column(type="datetime")
     */
    private $takenDate;
    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $text;
    /**
     * @ORM\Column(type="string", length=2048)
     */
    private $previewUrl;
    /**
     * @ORM\Column(type="integer")
     */
    private $likeCount;
    /**
     * @ORM\Column(type="integer")
     */
    private $commentCount;
    /**
     * @ORM\ManyToOne(targetEntity="ICS\MediaBundle\Entity\MediaImage",cascade={"persist","remove"})
     */
    private $image;

    public function __construct($jsonResult = null)
    {
        if (null != $jsonResult) {
            $this->id = $jsonResult->id;
            $this->shortCode = $jsonResult->shortcode;
            $takenDate = new DateTime();
            $takenDate->setTimestamp($jsonResult->taken_at_timestamp);
            $this->takenDate = $takenDate;
            foreach ($jsonResult->edge_media_to_caption->edges as $text) {
                $this->text .= InstagramClient::TransformToLink(preg_replace("/U\+([0-9A-F]{4})/", '&#x\\1;', $text->node->text));
            }

            $this->text = utf8_encode($this->text);

            $this->previewUrl = $jsonResult->display_url;
            $this->likeCount = $jsonResult->edge_media_preview_like->count;
            if(property_exists($jsonResult,"edge_media_to_comment"))
            {
                $this->commentCount = $jsonResult->edge_media_to_comment->count;
            }
            else
            {
                $this->commentCount = 0;
            }

        }
    }

    public static function getMedia($jsonResult, InstagramClient $client)
    {
        $media = null;

        switch ($jsonResult->__typename) {
            case AbstractInstagramMedia::INSTAGRAM_MEDIA_VIDEO:
                $media = new InstagramVideo($jsonResult, $client);

            break;
            case AbstractInstagramMedia::INSTAGRAM_MEDIA_SIDECAR:
                $media = new InstagramSideCar($jsonResult, $client);

            break;
            default:
                $media = new InstagramImage($jsonResult);
            break;
        }

        return $media;
    }

    /**
     * Get the value of id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of shortCode.
     */
    public function getShortCode()
    {
        return $this->shortCode;
    }

    /**
     * Get the value of takenDate.
     */
    public function getTakenDate()
    {
        return $this->takenDate;
    }

    /**
     * Get the value of previewUrl.
     */
    public function getPreviewUrl()
    {
        return $this->previewUrl;
    }

    /**
     * Get the value of likeCount.
     */
    public function getLikeCount()
    {
        return $this->likeCount;
    }

    /**
     * Get the value of commentCount.
     */
    public function getCommentCount()
    {
        return $this->commentCount;
    }

    public function getMediaApiUrl()
    {
        return 'https://www.instagram.com/p/'.$this->shortCode.'/?__a=1';
    }

    /**
     * Get the value of text.
     */
    public function getText()
    {
        return utf8_decode($this->text);
    }

    /**
     * Get the value of image.
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set the value of image.
     *
     * @return self
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }
}
