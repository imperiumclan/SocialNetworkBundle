<?php

namespace ICS\SocialNetworkBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use ICS\MediaBundle\Service\MediaClient;
use ICS\SocialNetworkBundle\Entity\Instagram\AbstractInstagramMedia;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramAccount;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramSideCar;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramSimpleAccount;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramVideo;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InstagramClient extends AbstractSocialClient
{
    /**
     * Instagram Api EndPoint.
     *
     * @var string
     */
    private $apiEndPoint = 'https://www.instagram.com/graphql/query/';
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
        $finalSearchAccount = null;
        $result = null;
        $username = trim(strtolower($username));

        foreach ($this->search($username) as $account) {
            if ($account->getUsername() == $username) {
                $finalSearchAccount = $account;
            }
        }

        if (null != $finalSearchAccount) {
            $url = $finalSearchAccount->getApiUrl();
            $response = $this->client->request('GET', $url, $this->headers);

            if (200 == $response->getStatusCode()) {
                $instagramResponse = json_decode($response->getContent());

                try {
                    $result = new InstagramAccount($instagramResponse->graphql->user);
                    $result = $this->updateAccountPublications($result, 12);
                } catch (Exception $ex) {
                    dump($ex->getMessage());
                }
            }
        }

        return $result;
    }

    public function updateAccountPublications(InstagramAccount $account, int $nbpublications = 50)
    {
        $options = [
            'variables' => '{"id":"'.$account->getId().'","first":"'.$nbpublications.'"}',
        ];
        $url = $this->prepareRequest($options);

        $response = $this->client->request('GET', $url, $this->headers);

        if (200 == $response->getStatusCode()) {
            $accountPublications = json_decode($response->getContent());

            foreach ($accountPublications->data->user->edge_owner_to_timeline_media->edges as $medias) {
                $media = AbstractInstagramMedia::getMedia($medias->node, $this);
                if (null != $media && !$this->publicationExist($account, $media)) {
                    $account->getPublications()->add($media);
                }
            }
        }

        return $account;
    }

    public function getFullPublications(InstagramAccount $account, int $nbpublications = 50, string $endpointer = null)
    {
        $variables = '{"id":"'.$account->getId().'","first":"'.$nbpublications.'"';

        if (null != $endpointer) {
            $variables .= ',"after":"'.$endpointer.'"';
        }

        $variables .= '}';
        $options = [
            'variables' => $variables,
        ];
        $url = $this->prepareRequest($options);

        $response = $this->client->request('GET', $url, $this->headers);

        if (200 == $response->getStatusCode()) {
            $accountPublications = json_decode($response->getContent());

            foreach ($accountPublications->data->user->edge_owner_to_timeline_media->edges as $medias) {
                $media = AbstractInstagramMedia::getMedia($medias->node, $this);
                if (!$this->publicationExist($account, $media)) {
                    $account->getPublications()->add($media);
                }
            }

            if ($accountPublications->data->user->edge_owner_to_timeline_media->page_info->has_next_page) {
                $this->getFullPublications($account, $nbpublications, $accountPublications->data->user->edge_owner_to_timeline_media->page_info->end_cursor);
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

    public function updateAccount(InstagramAccount $account)
    {
        $basepath = $this->mediaClient->getBasePath();

        $this->createAccountPath($account);

        $accountBasePath = $basepath.'/socialNetwork/Instagram/'.$account->getUsername().'/';
        $imageBasePath = $basepath.'/socialNetwork/Instagram/'.$account->getUsername().'/images';
        $sidecarBasePath = $basepath.'/socialNetwork/Instagram/'.$account->getUsername().'/sidecars';
        $videoBasePath = $basepath.'/socialNetwork/Instagram/'.$account->getUsername().'/videos';

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

    private function createAccountPath(InstagramAccount $account)
    {
        $basepath = $this->mediaClient->getBasePath();
        $path[] = $basepath.'/socialNetwork/Instagram/'.$account->getUsername().'/';
        $path[] = $basepath.'/socialNetwork/Instagram/'.$account->getUsername().'/images';
        $path[] = $basepath.'/socialNetwork/Instagram/'.$account->getUsername().'/sidecars';
        $path[] = $basepath.'/socialNetwork/Instagram/'.$account->getUsername().'/videos';

        foreach ($path as $p) {
            if (!file_exists($p)) {
                mkdir($p, 0777, true);
            }
        }
    }

    private function prepareRequest($requestOptions)
    {
        //For All Request
        $options['query_hash'] = '472f257a40c653c64c666ce877d59d2b';

        $options = array_merge($options, $requestOptions);

        return $this->makeRequest($options);
    }

    private function makeRequest($requestOptions)
    {
        $options = '?';

        foreach ($requestOptions as $key => $opt) {
            $options .= $key.'='.$opt.'&';
        }

        $options = substr($options, 0, strlen($options) - 1);

        return $this->apiEndPoint.$options;
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
                $result = $this->updateAccountPublications($result, 12);
            }
        }

        return $result;
    }

    public function getPublicationList(InstagramSimpleAccount $account)
    {
    }

    public function getPublication($shortcode)
    {
    }
}
