<?php

namespace Arall\Whois\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Arall\Whois as Tool;

libxml_use_internal_errors(true);

class Whois extends Command
{
    public function configure()
    {
        $this
            ->setName('domain:whois')
            ->setDescription('Client for the whois directory service')
            ->addArgument(
                'domain',
                InputArgument::REQUIRED,
                'Domain'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $domain = $input->getArgument('domain');

        try {
            $whois = new Tool($domain);
        } catch (\Exception $e) {
            return $output->writeln('<error>'.$e->getMessage().'</error>');
        }

        $output->writeln( 'Creation date: ' . $whois->getCreationDate());

        $output->writeln( 'Update date: ' . $whois->getUpdateDate());

        $output->writeln( 'Expiration date: ' . $whois->getExpirationDate());

        $output->writeln( 'Registrar: ' . $whois->getRegistrar());

        $output->writeln( 'Domain ID: ' . $whois->getId());

        $output->writeln( 'Allow transfers: ' . $whois->allowTransfers() ? 'Yes' : 'No');

        $output->writeln( 'DNS: ' . implode(', ', $whois->getDns()));

        $contacts = array(
            'Registrant' => $whois->getRegistrant(),
            'Admin' => $whois->getRegistrant(),
            'Tech' => $whois->getRegistrant(),
        );
        foreach ($contacts as $type => $contact) {
            foreach (get_object_vars($contact) as $name => $value) {
                $output->writeln( $type . ' ' . $name . ': ' . $value);
            }
        }

    }
}
