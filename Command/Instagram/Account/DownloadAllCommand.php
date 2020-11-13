<?php
namespace ICS\SocialNetworkBundle\Command\Instagram\Account;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramAccount;
use ICS\SocialNetworkBundle\Service\InstagramClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DownloadAllCommand extends Command
{
    protected static $defaultName = 'Instagram:DownloadAll:Account';

    protected $container;

    protected $doctrine;

    protected $client;

    protected $nbImages=50;

    public function __construct(InstagramClient $client,ContainerInterface $container,EntityManagerInterface $doctrine)
    {
        parent::__construct();

        $this->container = $container;
        $this->client = $client;
        $this->doctrine = $doctrine;
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
        $io = new SymfonyStyle($input, $output);

        $error=array();

        foreach($this->getAccountList($io,$input) as $account)
        {
            $io->title('Download all publications for account '.$account->getUsername().' from Instagram');
            
            try
            {
                $io->text('Get URLs of all publications (This may take a long time)');
                $this->client->getFullPublications($account);
                $io->success("Account ".$account->getUsername()." contains ".count($account->getPublications())." publications.");
                $io->text('Download all publications (This may take a long time)');
                $this->client->updateAccount($account);
                $io->text('Save data');
                $account->setlastUpdate(new DateTime());
                $this->doctrine->persist($account);
                $this->doctrine->flush();
                $io->success("Account ".$account->getUsername()." was up to date with ".count($account->getPublications())." publications.");
            }
            catch(Exception $ex)
            {
                $io->error($ex->getMessage());
                $error[]=$ex;
            }
        }

        if(count($error) > 0)
        {
            $io->error(count($error));
            foreach($error as $err)
            {
                $io->error($err->getMessage());
            }

            return Command::FAILURE;
        }
    $io->error(count($error));
        return Command::SUCCESS;
    }

    protected function getAccountList(SymfonyStyle $io,InputInterface $input)
    {
        if ($input->getArgument('account') == null)
        {
            $accounts=$this->doctrine->getRepository(InstagramAccount::class)->findBy(array(
                'lastUpdate' => null,
                'active' => true
            ));
        }
        else
        {
            $accounts=$this->doctrine->getRepository(InstagramAccount::class)->findBy(array(
                'username' => $input->getArgument('account')
            ));
        }

        return $accounts;

    }

}