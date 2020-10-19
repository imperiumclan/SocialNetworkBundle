<?php

namespace ICS\SocialNetworkBundle\Service;

use ICS\SocialNetworkBundle\Entity\Instagram\InstagramAccount;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ICS\SocialNetworkBundle\Entity\Instagram\SearchResult;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramSimpleAccount;


class InstagramClient extends AbstractSocialClient
{

    /**
     * Instagram Api EndPoint
     *
     * @var string
     */
    private $apiEndPoint='https://www.instagram.com/graphql/query/';
    
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container,"Instagram");
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
        $results=array();
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

                dump($result);
                
                
            }
        }

        return $result;
    }


    public function updateAccountPublications(InstagramAccount $account)
    {
        $options=array(
            'variables' => '{"id":"'.$account->getId().'","first":"50"}'
        );
        $url=$this->prepareRequest($options);
        
        $response=$this->client->request('GET',$url);

        if($response->getStatusCode()== 200) {

            $accountPublications=json_decode($response->getContent());
            dump($accountPublications);

        }

        return $account;
    }


    static public function TransformToLink(string $text)
    {

        //TODO : Code Tag and Account transformation text

        return $text;
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