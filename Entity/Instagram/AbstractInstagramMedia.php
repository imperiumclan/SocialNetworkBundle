<?php

namespace ICS\SocialNetworkBundle\Entity\Instagram;

use DateTime;
use ICS\SocialNetworkBundle\Service\InstagramClient;

abstract class AbstractInstagramMedia {

    protected const INSTAGRAM_MEDIA_IMAGE = "GraphImage";
    protected const INSTAGRAM_MEDIA_VIDEO = "GraphVideo";
    protected const INSTAGRAM_MEDIA_SIDECAR = "GraphSidecar";

    private $id;
    private $shortCode;
    private $takenDate;
    private $text;
    private $previewUrl;
    private $likeCount;
    private $commentCount;

    public function __construct($jsonResult=null)
    {
        if($jsonResult!=null)
        {
            $this->id=$jsonResult->id;
            $this->shortCode=$jsonResult->shortcode;
            $takenDate = new DateTime();
            $takenDate->setTimestamp ($jsonResult->taken_at_timestamp);
            $this->takenDate=$takenDate;
            foreach($jsonResult->edge_media_to_caption->edges as $text)
            {
                $this->text .= InstagramClient::TransformToLink($text->node->text)."\n";
            }

            $this->previewUrl =$jsonResult->display_url;
            $this->likeCount = $jsonResult->edge_media_preview_like->count;
            $this->commentCount = $jsonResult->edge_media_to_comment->count;
        }
    }

    static public function getMedia($jsonResult,InstagramClient $client)
    {
        $media=null;

        switch($jsonResult->__typename)
        {
            case AbstractInstagramMedia::INSTAGRAM_MEDIA_VIDEO:
                $media=new InstagramVideo($jsonResult,$client);
            break;
            case AbstractInstagramMedia::INSTAGRAM_MEDIA_SIDECAR:
                $media=new InstagramSideCar($jsonResult,$client);
            break;
            default:
                $media=new InstagramImage($jsonResult);
            break;

        }

        return $media;
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of shortCode
     */
    public function getShortCode()
    {
        return $this->shortCode;
    }

    /**
     * Get the value of takenDate
     */
    public function getTakenDate()
    {
        return $this->takenDate;
    }

    /**
     * Get the value of previewUrl
     */
    public function getPreviewUrl()
    {
        return $this->previewUrl;
    }

    /**
     * Get the value of likeCount
     */
    public function getLikeCount()
    {
        return $this->likeCount;
    }

    /**
     * Get the value of commentCount
     */
    public function getCommentCount()
    {
        return $this->commentCount;
    }

    public function getMediaApiUrl()
    {
        return "https://www.instagram.com/p/".$this->shortCode."/?__a=1";
    }
}