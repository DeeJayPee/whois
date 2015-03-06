<?php

require_once __DIR__.'/../../vendor/autoload.php';

$whois = new Arall\Whois(isset($argv[1]) ? $argv[1] : 'eff.org');

echo 'Creation date: ' . $whois->getCreationDate() . PHP_EOL;

echo 'Update date: ' . $whois->getUpdateDate() . PHP_EOL;

echo 'Expiration date: ' . $whois->getExpirationDate() . PHP_EOL;

echo 'Registrar: ' . $whois->getRegistrar() . PHP_EOL;

echo 'Domain ID: ' . $whois->getId() . PHP_EOL;

echo 'Allow transfers: '; echo $whois->allowTransfers() ? 'Yes' : 'No'; echo PHP_EOL;

echo 'DNS: ' . implode(', ', $whois->getDns()) . PHP_EOL;
