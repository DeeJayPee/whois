<?php

require_once __DIR__.'/../vendor/autoload.php';

date_default_timezone_set("Europe/Madrid");

use Arall\Whois;

class WhoisTest extends PHPUnit_Framework_TestCase
{

    public function testInvalidDomain()
    {
        $this->setExpectedException('InvalidArgumentException');

        new Whois('invalid-domain');
    }

    public function testResolver()
    {
        $whois = new Whois('eff.org');

        $this->assertEquals('1990-10-10 05:00:00', $whois->getCreationDate());
        $this->assertEquals('2013-08-26 20:31:10', $whois->getUpdateDate());
        $this->assertEquals('2022-10-09 06:00:00', $whois->getExpirationDate());
        $this->assertEquals('Gandi SAS (R42-LROR)', $whois->getRegistrar());
        $this->assertEquals('D2234962-LROR', $whois->getId());
        $this->assertFalse($whois->allowTransfers());
        $this->assertEquals(array('NS1.EFF.ORG', 'NS2.EFF.ORG', 'NS6.EFF.ORG'), $whois->getDns());

        // Registrant
        $contact = new \stdClass();
        $contact->name         = 'Shari Steele';
        $contact->organization = 'Electronic Frontier Foundation';
        $contact->city         = 'San Francisco';
        $contact->state        = null;
        $contact->postal_code  = '94110';
        $contact->country      = 'US';
        $contact->phone        = '+1.4154369333';
        $contact->phone_ext    = null;
        $contact->fax          = null;
        $contact->fax_ext      = null;
        $contact->email        = 'whois@eff.org';

        $this->assertEquals($contact, $whois->getRegistrant());

        // Admin
        $contact->name        = 'System Administrator';
        $contact->state       = 'CA';
        $contact->postal_code = '94109';
        $contact->fax         = '+33.1';

        $this->assertEquals($contact, $whois->getAdmin());

        // Tech
        $contact->name         = 'Service Technique';
        $contact->organization = 'GANDI SARL';
        $contact->city         = 'Paris';
        $contact->state        = '';
        $contact->postal_code  = '75013';
        $contact->country      = 'FR';
        $contact->phone        = '+33.143737851';
        $contact->phone_ext    = '';
        $contact->fax          = '';
        $contact->fax_ext      = null;
        $contact->email        = 'support@gandi.net';

        $this->assertEquals($contact, $whois->getTech());
    }
}
