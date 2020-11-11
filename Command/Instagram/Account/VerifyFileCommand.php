<?php

namespace ICS\SocialNetworkBundle\Command\Instagram\Account;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use ICS\MediaBundle\Service\MediaClient;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramAccount;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramSideCar;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramVideo;
use ICS\SocialNetworkBundle\Service\InstagramClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class VerifyFileCommand  extends Command
{

        protected static $defaultName = 'Instagram:VerifyFile:Account';

        protected $container;

        protected $doctrine;

        protected $client;

        protected $nbImages=50;

        private $mediaClient;

        public function __construct(InstagramClient $client,ContainerInterface $container,EntityManagerInterface $doctrine,MediaClient $mediaClient)
        {
            parent::__construct();

            $this->container = $container;
            $this->client = $client;
            $this->doctrine = $doctrine;
            $this->mediaClient= $mediaClient;

        }

        protected function configure()
        {
            $this
            ->addArgument('account', InputArgument::OPTIONAL, 'Instagram account')
            // the short description shown while running "php bin/console list"
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command download all publications of an Instagram account from official Instagram website');
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $basepath = $this->mediaClient->getBasePath();

            $io = new SymfonyStyle($input, $output);


            if($input->getArgument('account')!=null)
            {
                $accounts=$this->doctrine->getRepository(InstagramAccount::class)->findBy([
                    'username'=> $input->getArgument('account')
                ]);
            }
            else
            {
                $accounts=$this->doctrine->getRepository(InstagramAccount::class)->findAll();
            }

            foreach($accounts as $account)
            {
                $accountBasePath = $basepath."/socialNetwork/Instagram/".$account->getUsername()."/";
                $imageBasePath = $basepath."/socialNetwork/Instagram/".$account->getUsername()."/images";
                $sidecarBasePath = $basepath."/socialNetwork/Instagram/".$account->getUsername()."/sidecars";
                $videoBasePath = $basepath."/socialNetwork/Instagram/".$account->getUsername()."/videos";

                $io->title('Verify file for account '.$account->getUsername());
                try
                {
                    $io->text('Verify Medias');
                    $videos=0;
                    $sidecar=0;
                    $images=0;
                    foreach($account->getPublications() as $publication)
                    {
                        if(is_a($publication,InstagramVideo::class))
                        {
                            if($publication->getVideo()==null)
                            {
                                $response=$this->client->getApiUrl($publication->getMediaApiUrl());
                                $url=$response->graphql->shortcode_media->video_url;
                                $io->text('Download '.$url);
                                $publication->setVideo($this->mediaClient->DownloadVideo($url,$videoBasePath.'/'.$publication->getId().'.mp4'));
                            }
                            $videos++;

                        }
                        else if(is_a($publication,InstagramSideCar::class))
                        {
                        //     $i=1;
                        //     foreach($publication->getimagesUrls() as $imgUrl)
                        //     {
                        //         $path=$sidecarBasePath.'/'.$publication->getId();
                        //         if(!file_exists($path))
                        //         {
                        //             mkdir($path,0777,true);
                        //         }

                        //         if(count($publication->getImages())==0)
                        //         {
                        //             $response=$this->client->getApiUrl($publication->getMediaApiUrl());
                        //             $url=$response->graphql->shortcode_media->video_url;
                        //             $publication->setVideo($this->mediaClient->DownloadVideo($url,$videoBasePath.'/'.$publication->getId().'.mp4'));
                        //         }

                        //         $publication->getImages()->add($this->mediaClient->DownloadImage($imgUrl,$path.'/'.$i.'.jpg'));
                        //         $i++;
                        //     }
                            $sidecar++;
                        }
                        else
                        {
                            $images++;
                        }


                    }
                    $io->text('Videos : '.$videos);
                    $io->text('Sidecar : '.$sidecar);
                    $io->text('Images : '.$images);

                    $io->text('Save data');
                    $this->doctrine->persist($account);
                    $this->doctrine->flush();
                    $io->success("Account ".$account->getUsername()." was up to date with ".count($account->getPublications())." publications.");
                }
                catch(Exception $ex)
                {

                    $io->error($ex->getMessage());
                        return Command::FAILURE;
                }
            }
            return Command::SUCCESS;
        }
}