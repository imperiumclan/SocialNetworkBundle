<?php

namespace ICS\SocialNetworkBundle\Command\Instagram\Account;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use ICS\MediaBundle\Service\MediaClient;
use ICS\SocialNetworkBundle\Entity\AtlantisInstaAccount;
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

class AtlantisImport  extends Command
{

        protected static $defaultName = 'Atlantis:Import';

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
            $io=new SymfonyStyle($input,$output);

            $olds=$this->doctrine->getRepository(AtlantisInstaAccount::class)->findAll();

            $pb=$io->createProgressBar(count($olds));
            $i=0;
            foreach($olds as $old)
            {
                $i++;
                $pb->setProgress($i);
                $account=$this->doctrine->getRepository(InstagramAccount::class)->findOneBy(array('username' => $old->getUsername()));

                if($account==null)
                {
                    $account=$this->client->getAccount($old->getUsername());
                    if($account != null)
                    {
                        $this->client->saveAccount($account);
                        $this->doctrine->persist($account);
                        $this->doctrine->flush();
                        $io->success('Account '.$account->getUsername().' imported');
                    }
                    else
                    {
                        $io->warning('The account '.$old->getUsername().' unknow on instagram site.');
                    }
                }
            }


            return Command::SUCCESS;
        }
}