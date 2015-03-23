<?php

namespace Arall;

class Whois
{

    /**
	 * Domain
	 *
	 * @var string
	 */
    private $domain;

    /**
     * Whois plain text result
     *
     * @var string
     */
    private $result;

    /**
     * Whois resolver server
     *
     * @var string
     */
    private $server = 'whois.domain.com';

    /**
	 * Construct
     *
     * @param  string                   $domain Domain name
     * @throws InvalidArgumentException If the domain is not valid
	 */
    public function __construct($domain)
    {
        // Is valid?
        if (preg_match("/^([-a-z0-9]{2,100})\.([a-z\.]{2,8})$/i", $domain)) {

            // Store
            $this->domain = $domain;

            // Run
            $this->execute();

        } else {

            // Invalid domain
            throw new \InvalidArgumentException('Invalid domain');
        }
    }

    /**
     * Query DNS server
     *
     * @return bool
     */
    private function execute()
    {
        // Connect
        if ($connection = fsockopen($this->server, 43)) {

            // Query
            fputs($connection, $this->domain."\r\n");

            // Store response
            $this->result = '';
            while (!feof($connection)) {
                $this->result .= fgets($connection);
            }

            return true;
        }

        return false;
    }

    /**
     * Get domain creation date
     *
     * @return string
     */
    public function getCreationDate()
    {
        return $this->parseDate($this->parseText('creation date', 1));
    }

    /**
     * Get domain update date
     *
     * @return string
     */
    public function getUpdateDate()
    {
        return $this->parseDate($this->parseText('(updated|update) date', 2));
    }

    /**
     * Get domain expiration date
     *
     * @return string
     */
    public function getExpirationDate()
    {
        return $this->parseDate($this->parseText('(expiration|expiry) date', 2));
    }

    /**
     * Get domain Registrar
     *
     * @return string
     */
    public function getRegistrar()
    {
        return $this->parseText('registrar', 1);
    }

    /**
     * Get domain ID
     *
     * @return string
     */
    public function getId()
    {
        return $this->parseText('domain id', 1);
    }

    /**
     * Check if domain is currenly allowing transfers
     *
     * @return bool
     */
    public function allowTransfers()
    {
        return $this->result && !strstr($this->result, 'clientTransferProhibited');
    }

    /**
     * Get domain DNS records
     *
     * @return array
     */
    public function getDns()
    {
        return $this->parseText('name server');
    }

    /**
     * Get domain Registrant contact
     *
     * @return stdClass
     */
    public function getRegistrant()
    {
        return $this->parseContact('registrant');
    }

    /**
     * Get domain Admin contact
     *
     * @return stdClass
     */
    public function getAdmin()
    {
        return $this->parseContact('admin');
    }

    /**
     * Get domain Tech contact
     *
     * @return stdClass
     */
    public function getTech()
    {
        return $this->parseContact('tech');
    }

    /**
     * Search text on result
     *
     * @param  string       $text
     * @param  integer      $index Match index
     * @return string|array
     */
    private function parseText($text, $index = null)
    {
        $regex = '/'.$text.': ?(.*)/i';

        // All results?
        if ($index === null) {
            preg_match_all($regex, $this->result, $matches);

            // Clean empty matches
            $matches = array_map('trim', $matches[1]);
            $matches = array_filter($matches);

            return isset($matches) ? $matches : null;

        // Result index?
        } else {
            preg_match($regex, $this->result, $matches);

            return isset($matches[$index]) ? trim($matches[$index]) : null;
        }
    }

    /**
     * Get contact
     *
     * @param  string   $type [Registrant | Admin | Tech]
     * @return stdClass
     */
    public function parseContact($type)
    {
        $contact = new \stdClass();

        $contact->name          = $this->parseText($type . ' name',             1);
        $contact->organization  = $this->parseText($type . ' organization',     1);
        $contact->city          = $this->parseText($type . ' city',             1);
        $contact->state         = $this->parseText($type . ' state\/province',  1);
        $contact->postalCode    = $this->parseText($type . ' postal code',      1);
        $contact->country       = $this->parseText($type . ' country',          1);
        $contact->phone         = $this->parseText($type . ' phone',            1);
        $contact->phoneExt      = $this->parseText($type . ' phone ext',        1);
        $contact->fax           = $this->parseText($type . ' fax',              1);
        $contact->faxExt        = $this->parseText($type . ' fax ext',          1);
        $contact->email         = $this->parseText($type . ' email',            1);

        return $contact;
    }

    /**
     * Parse date to Y-m-d H:i:s format
     *
     * @param  string $date
     * @return string
     */
    private function parseDate($date)
    {
        return $date ? date('Y-m-d H:i:s', strtotime($date)) : null;
    }

}
