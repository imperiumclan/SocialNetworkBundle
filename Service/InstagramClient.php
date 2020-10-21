<?php

namespace ICS\SocialNetworkBundle\Service;

use ICS\SocialNetworkBundle\Entity\Instagram\AbstractInstagramMedia;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramAccount;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramSimpleAccount;
use ICS\MediaBundle\Service\MediaClient;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class InstagramClient extends AbstractSocialClient
{

    /**
     * Instagram Api EndPoint
     *
     * @var string
     */
    private $apiEndPoint='https://www.instagram.com/graphql/query/';

    private $mediaClient;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container,"Instagram");
        //$this->mediaClient = $client;
    }

    public function search(String $search,$verifiedOnly=false)
    {
        $url="https://www.instagram.com/web/search/topsearch/?query=".$search;
        $response=$this->client->request('GET',$url);

        $results=array();

        if($response->getStatusCode()== 200) {

            $content = $response->getContent();
            $content=json_decode($content);

            foreach($content->users as $user)
            {
                $result=new InstagramSimpleAccount($user->user);
                if($verifiedOnly)
                {
                    if($result->isVerified())
                    {
                        $results[]=$result;
                    }
                }
                else
                {
                    $results[]=$result;
                }

            }

        }

        return $results;
    }

    public function getAccount(string $username)
    {
        $finalSearchAccount=null;
        
        $username=trim(strtolower($username));

        foreach($this->search($username) as $account)
        {
            if($account->getUsername() == $username)
            {
                $finalSearchAccount = $account;
            }
        }

        if($finalSearchAccount!=null)
        {

            $url=$finalSearchAccount->getApiUrl();
            $response=$this->client->request('GET',$url);

            if($response->getStatusCode()== 200) {

                $instagramResponse = json_decode($response->getContent());

                $result=new InstagramAccount($instagramResponse->graphql->user);

                $result=$this->updateAccountPublications($result);

                //dump($result);


            }
        }

        return $result;
    }


    public function updateAccountPublications(InstagramAccount $account)
    {
        $options=array(
            'variables' => '{"id":"'.$account->getId().'","first":"8"}'
        );
        $url=$this->prepareRequest($options);

        $response=$this->client->request('GET',$url);

        if($response->getStatusCode()== 200) {

            $accountPublications=json_decode($response->getContent());

            //dump($accountPublications);

            foreach($accountPublications->data->user->edge_owner_to_timeline_media->edges as $medias)
            {
                $account->getPublications()->add(AbstractInstagramMedia::getMedia($medias->node,$this));
            }
        }

        return $account;
    }


    static public function TransformToLink(string $text)
    {   
        // Gestion des #tag
        preg_match_all("/(#\w+)/u", $text, $matches);
        foreach($matches[0] as $tag)
        {
            $text=str_replace($tag,'<a href="https://www.instagram.com/explore/tags/'.substr($tag,1).'" target="_blank">'.$tag.'</a>',$text);
        }

        // Gestion des @person
        preg_match_all("/(@\w+)/u", $text, $matches);
        foreach($matches[0] as $person)
        {
            $text=str_replace($person,'<a href="https://www.instagram.com/'.substr($person,1).'" target="_blank">'.$person.'</a>',$text);
        }

        //TODO : Code Tag and Account transformation text

        return $text;
    }

    public function updateAccount(InstagramAccount $account)
    {
        // $basepath = $this->mediaClient->getBasePath();

        // $this->createAccountPath($account);

        // $accountBasePath = $basepath."/socialNetwork/Instagram/".$account->getUsername()."/";
        // $imageBasePath = $basepath."socialNetwork/Instagram/".$account->getUsername()."/images";
        // $sidecarBasePath = $basepath."socialNetwork/Instagram/".$account->getUsername()."/sidecars";
        // $videoBasePath = $basepath."socialNetwork/Instagram/".$account->getUsername()."/videos";

    
        // //Download Profile Picture
        // $account->setProfilePic($this->mediaClient->DownloadImage($account->getProfilePicUrl(),$accountBasePath.'/profile_pic.jpeg'));


    }

    private function createAccountPath(InstagramAccount $account)
    {
        $basepath = $this->mediaClient->getBasePath();
        $path[] = $basepath."/socialNetwork/Instagram/".$account->getUsername()."/";
        $path[] = $basepath."/socialNetwork/Instagram/".$account->getUsername()."/images";
        $path[] = $basepath."/socialNetwork/Instagram/".$account->getUsername()."/sidecars";
        $path[] = $basepath."/socialNetwork/Instagram/".$account->getUsername()."/videos";

        foreach($path as $p)
        {
            if(!file_exists($p))
            {
                mkdir($p,0777,true);
            }
        }
    }

    private function prepareRequest($requestOptions)
    {
        //For All Request
        $options['query_hash']='472f257a40c653c64c666ce877d59d2b';


        $options=array_merge($options,$requestOptions);

        return $this->makeRequest($options);
    }

    private function makeRequest($requestOptions)
    {
        $options="?";

        foreach($requestOptions as $key => $opt)
        {
            $options.=$key.'='.$opt."&";
        }
        $options=substr($options,0,strlen($options)-1);
        return $this->apiEndPoint.$options;
    }
}