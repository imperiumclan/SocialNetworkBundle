<?php

namespace ICS\SocialNetworkBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use ICS\MediaBundle\Service\MediaClient;
use ICS\SocialNetworkBundle\Entity\Instagram\AbstractInstagramMedia;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramAccount;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramImage;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramSideCar;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramSimpleAccount;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramVideo;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InstagramClient extends AbstractSocialClient
{
    /**
     * Client for Media Download.
     *
     * @var ICS\MediaBundle\Service\MediaClient
     */
    private $mediaClient;
    /**
     * Orm.
     *
     * @var Doctrine\ORM\EntityManagerInterface
     */
    private $doctrine;

    /**
     * Class Constructor.
     */
    public function __construct(ContainerInterface $container, MediaClient $client, EntityManagerInterface $doctrine)
    {
        parent::__construct($container, 'Instagram');
        $this->mediaClient = $client;
        $this->doctrine = $doctrine;
    }

    /**
     * Search account on instagram.
     */
    public function search(string $search, $verifiedOnly = false)
    {
        $response = $this->getApiUrl('https://www.instagram.com/web/search/topsearch/?query='.$search);

        $results = [];

        if (false != $response) {
            $content = $response;
            foreach ($content->users as $user) {
                $result = new InstagramSimpleAccount($user->user);
                if ($verifiedOnly) {
                    if ($result->isVerified()) {
                        $results[] = $result;
                    }
                } else {
                    $results[] = $result;
                }
            }
        }

        return $results;
    }

    public function getAccount(string $username)
    {
        $account = $this->getAccountInfos($username);

        return $account;
    }

    public function updateAccountPublications(InstagramAccount $account, int $nbpublications = 50)
    {
        $publications = $this->getPublicationList($account, 0);

        foreach ($publications as $publication) {
            if (!$this->publicationExist($account, $publication)) {
                $account->getPublications()->add($publication);
            }
        }

        return $account;
    }

    public function getFullPublications(InstagramAccount $account)
    {
        $publications = $this->getPublicationList($account, 0);

        foreach ($publications as $publication) {
            if (!$this->publicationExist($account, $publication)) {
                $account->getPublications()->add($publication);
            }
        }

        return $account;
    }

    public function publicationExist(InstagramAccount $account, AbstractInstagramMedia $media)
    {
        foreach ($account->getPublications() as $pub) {
            if ($pub->getId() == $media->getId()) {
                return true;
            }
        }

        return false;
    }

    public function saveAccount(InstagramSimpleAccount $account)
    {
        // Get full account infos
        $account = $this->getAccountInfos($account->getUsername());
        // Create account path for medias
        $paths = $this->createAccountPath($account);
        $accountBasePath = $paths['base'];
        $imageBasePath = $paths['images'];
        $sidecarBasePath = $paths['sidecars'];
        $videoBasePath = $paths['videos'];

        // Download Profile Picture
        $account->setProfilePic($this->mediaClient->DownloadImage($account->getProfilePicUrl(), $accountBasePath.'/profile_pic.jpeg'));

        // Save publications data
        foreach ($account->getPublications() as $key => $publication) {
            if (is_a($publication, InstagramVideo::class)) {
                $url = $publication->getVideoUrl();
                if ('https://static.cdninstagram.com/rsrc.php/null.mp4' != $url && null != $url) {
                    $publication->setVideo($this->mediaClient->DownloadVideo($url, $videoBasePath.'/'.$publication->getId().'.mp4'));
                } else {
                    $account->getPublications()->remove($key);
                }
            } elseif (is_a($publication, InstagramSideCar::class)) {
                $i = 1;
                foreach ($publication->getimagesUrls() as $imgUrl) {
                    if ('https://static.cdninstagram.com/rsrc.php/null.jpg' != $imgUrl && null != $imgUrl) {
                        $path = $sidecarBasePath.'/'.$publication->getId();
                        if (!file_exists($path)) {
                            mkdir($path, 0777, true);
                        }
                        $publication->getImages()->add($this->mediaClient->DownloadImage($imgUrl, $path.'/'.$i.'.jpg'));
                        ++$i;
                    } else {
                        $account->getPublications()->remove($key);
                    }
                }
            }
            if (null != $publication->getPreviewUrl() && '' != $publication->getPreviewUrl() && 'https://static.cdninstagram.com/rsrc.php/null.jpg' != $publication->getPreviewUrl()) {
                $publication->setImage($this->mediaClient->DownloadImage($publication->getPreviewUrl(), $imageBasePath.'/'.$publication->getId().'.jpg'));
            } else {
                $account->getPublications()->remove($key);
            }
        }

        $this->doctrine->persist($account);
        $this->doctrine->flush();
    }

    private function createAccountPath(InstagramSimpleAccount $account)
    {
        $basepath = $this->mediaClient->getBasePath();
        $path['base'] = $basepath.'/socialNetwork/Instagram/'.$account->getUsername().'/';
        $path['images'] = $basepath.'/socialNetwork/Instagram/'.$account->getUsername().'/images';
        $path['sidecars'] = $basepath.'/socialNetwork/Instagram/'.$account->getUsername().'/sidecars';
        $path['videos'] = $basepath.'/socialNetwork/Instagram/'.$account->getUsername().'/videos';

        foreach ($path as $p) {
            if (!file_exists($p)) {
                mkdir($p, 0777, true);
            }
        }

        return $path;
    }

    public function getAccountInfos($username)
    {
        $finalSearchAccount = null;
        $result = null;
        $username = trim(strtolower($username));

        foreach ($this->search($username) as $account) {
            if ($account->getUsername() == $username) {
                $finalSearchAccount = $account;
            }
        }

        if (null != $finalSearchAccount) {
            $response = $this->getApiUrl($finalSearchAccount->getApiUrl());
            if (false != $response) {
                $result = new InstagramAccount($response->graphql->user);
                $publications = $this->getPublicationList($account);

                dump($publications);

                foreach ($publications as $publication) {
                    if (null != $publication && !$this->publicationExist($account, $publication)) {
                        $account->getPublications()->add($publication);
                    }
                }
            }
        }

        return $result;
    }

    public function getPublicationList(InstagramSimpleAccount $account, $nbpublications = 12)
    {
        return $this->getPublicationsPage($account, $nbpublications);
    }

    public function getPublicationsPage(InstagramSimpleAccount $account, int $nbpublications, string $endpointer = null)
    {
        $publications = [];

        $variables = '{"id":"'.$account->getId().'","first":"'.$nbpublications.'"';

        if (null != $endpointer) {
            $variables .= ',"after":"'.$endpointer.'"';
        }

        $variables .= '}';
        $options = [
            'variables' => $variables,
            'query_hash' => '472f257a40c653c64c666ce877d59d2b',
        ];

        $response = $this->getApiUrl('https://www.instagram.com/graphql/query/', $options);

        if (false != $response) {
            foreach ($response->data->user->edge_owner_to_timeline_media->edges as $pub) {
                $p = $this->getPublication($pub->node->shortcode);

                if (null != $p) {
                    $publications[] = $p;
                }
            }

            if (0 == $nbpublications) {
                $nextPublications = $this->getPublicationsPage($account, $nbpublications, $response->data->user->edge_owner_to_timeline_media->page_info->end_cursor);
                $publications = array_merge($publications, $nextPublications);
            }
        }

        return $publications;
    }

    public function getPublication($shortCode): ?AbstractInstagramMedia
    {
        $options = [
            // 'query_hash' => '472f257a40c653c64c666ce877d59d2b',
            '__a' => '1',
            'type' => 'json',
        ];

        $response = $this->getApiUrl('https://www.instagram.com/p/'.$shortCode.'/', $options);

        if (false != $response) {
            switch ($response->shortcode_media->__typename) {
                case AbstractInstagramMedia::INSTAGRAM_MEDIA_SIDECAR:
                    return new InstagramVideo($response->shortcode_media);
                break;
                case AbstractInstagramMedia::INSTAGRAM_MEDIA_SIDECAR:
                    return new InstagramSideCar($response->shortcode_media);
                break;
                default:
                    return new InstagramImage($response->shortcode_media);
                break;
            }
        }

        return null;
    }

    public static function TransformToLink(string $text = null)
    {
        if (null != $text) {
            $test = 'tioietpozitepo';

            if (true) {
                $test = 'toto';
            }

            // Gestion des #tag
            preg_match_all("/(\s#\w+)/u", $text, $matches);
            foreach ($matches[0] as $tag) {
                $text = str_replace($tag, '<a href="https://www.instagram.com/explore/tags/'.trim(substr($tag, 1), '#').'" target="_blank">'.$tag.'</a>', $text);
            }

            // Gestion des @person
            preg_match_all("/(\s@\w+)/u", $text, $matches);
            foreach ($matches[0] as $person) {
                $text = str_replace($person, '<a href="https://www.instagram.com/'.trim(substr($person, 1), '@').'" target="_blank">'.$person.'</a>', $text);
            }

            $text = str_replace("\n", '<br/>', $text);
        }

        return $text;
    }
}
