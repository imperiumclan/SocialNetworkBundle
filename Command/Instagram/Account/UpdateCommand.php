<?php
namespace ICS\SocialNetworkBundle\Command\Instagram\Account;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use ICS\SocialNetworkBundle\Entity\Instagram\InstagramAccount;
use ICS\SocialNetworkBundle\Service\InstagramClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UpdateCommand extends Command
{
    protected static $defaultName = 'Instagram:Update:Account';

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
        ->addArgument('nbimages', InputArgument::OPTIONAL, 'Number of images to update')
        // the short description shown while running "php bin/console list"

        ->addOption('full',null,InputOption::VALUE_OPTIONAL,"Update all accounts",false)
        ->addOption('reactivate',null,InputOption::VALUE_OPTIONAL,"Update unactive accounts",false)
        ->addOption('nbimages',null,InputOption::VALUE_OPTIONAL,"Update unactive accounts",50)

        ->setDescription('Update Instagram Accounts')

        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command update Instagram account from official Instagram website');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        
        $accounts=$this->getAccountList($input,$output);
        $io->title('Update '.count($accounts).' Instagram account');

        foreach($accounts as $account)
        {
            $io->text('Update '.$account->getUsername().' from Instagram');
            try
            {
                $this->client->updateAccountPublications($account);
                $this->client->updateAccount($account);
                $account->setLastUpdate(new DateTime());
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

        

        //;

        return Command::SUCCESS;
    }

    protected function getAccountList(InputInterface $input, OutputInterface $output)
    {
        if($input->getOption('reactivate')===null)
        {
            $output->writeln("Mise à jour des comptes désactivés.");
            $accounts=$this->doctrine
            ->getRepository(InstagramAccount::class)
            ->findBy(array(
                'active' => false,
            ), array(
                'lastUpdate' => 'ASC',
            ));

            foreach($accounts as $account)
            {
                $account->setActive(true);
            }

            $output->writeln("Mise à jour des ".count($accounts)." comptes");
        }
        else if($input->getOption('full')===null)
        {
            $output->writeln("Mise à jour de l'ensemble de la base de données.");
            $accounts=$this->doctrine
            ->getRepository(InstagramAccount::class)
            ->findBy(array(
                'active' => true,
            ), array(
                'lastUpdate' => 'ASC',
            ));
            $output->writeln("Mise à jour des ".count($accounts)." comptes");
        }
        else if ($input->getArgument('account') == null) {
            $accounts=$this->doctrine
                            ->getRepository(InstagramAccount::class)
                            ->findBy(array(
                                'active' => true
                            ), array(
                                'lastUpdate' => 'ASC',
                            ), 10);
                            $output->writeln("Mise à jour automatique");

        }
        elseif ($input->getArgument('account') == 'new'){
            $accounts=$this->doctrine
                            ->getRepository(InstagramAccount::class)
                            ->findBy(array(
                                'profilePic' => null,
                                'active' => true
                            ), array(
                                'lastUpdate' => 'ASC',
                            ), 10);
                            $output->writeln("Mise à jour des ".count($accounts)." nouveau comptes");

        }
        elseif ($input->getArgument('account') == 'restore'){
            $accounts=$this->doctrine
                            ->getRepository(InstagramAccount::class)
                            ->findBy(array(
                                'active' => false,
                            ), array(
                                'lastUpdate' => 'ASC',
                            ), 10);
                            $output->writeln("Mise à jour des ".count($accounts)." nouveau comptes");

        }

        else{
            $account=$this->doctrine
                            ->getRepository(InstagramAccount::class)
                            ->findOneBy(array(
                                'username' => $input->getArgument('account')
                            ));
                            $output->writeln("Mise à jour manuel du compte ".trim($input->getArgument('account')));
                            $account->setActive(true);
            $accounts[] = $account;
        }

        return $accounts;
    }


}