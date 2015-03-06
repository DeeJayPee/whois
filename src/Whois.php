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
     * Domain TLD
     *
     * @var string
     */
    private $tld;

    /**
     * Whois plain text result
     *
     * @var string
     */
    private $result;

    /**
     * Whois resolvers servers
     *
     * @var array
     */
    private $servers = array(
        'com'       => 'whois.verisign-grs.com',
        'net'       => 'whois.verisign-grs.com',
        'org'       => 'whois.publicinterestregistry.net',
        'info'      => 'whois.afilias.info',
        'biz'       => 'whois.biz',
        'uk'        => 'whois.nic.uk',
        'ca'        => 'whois.cira.ca',
        'au'        => 'whois.audns.net.au',
        '*'         => 'whois-servers.net',
    );

    /**
	 * Construct
     *
     * @throws InvalidArgumentException If the domain is not valid
	 * @param string $domain Domain name
	 */
    public function __construct($domain)
    {
        // Is valid?
        if (preg_match("/^([-a-z0-9]{2,100})\.([a-z\.]{2,8})$/i", $domain)) {

            // Store
            $this->domain = $domain;

            // TLD
            $parts = explode(".", $this->domain);
            $this->tld = strtolower(array_pop($parts));

            // Run
            $this->execute();
        }

        // Invalid domain
        if (!$this->domain) {
            throw new \InvalidArgumentException('Invalid domain');
        }
    }

    /**
     * Query DNS server
     *
     * @throws ResolveErrorException If the resver doesn't response
     * @return bool
     */
    private function execute()
    {
        try {

            // Connect
            try {
                $server = isset($this->servers[$this->tld]) ? $this->servers[$this->tld] : $this->tld . '.' . $this->servers['*'];
                $connection = fsockopen($server, 43);
            } catch (\Exception $e) {
                return false;
            }

            // Query
            fputs($connection, $this->domain."\r\n");

            // Store response
            $this->result = '';
            while (!feof($connection)) {
                $this->result .= fgets($connection);
            }

            return true;

        } catch (ResolveErrorException $e) {
            return false;
        }
    }

    /**
     * Get domain creation date
     *
     * @return string
     */
    public function getCreationDate()
    {
        return $this->parseDate($this->parseText('creation date'));
    }

    /**
     * Get domain update date
     *
     * @return string
     */
    public function getUpdateDate()
    {
        return $this->parseDate($this->parseText('(update|updated) date', 2));
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
        return $this->parseText('registrar');
    }

    /**
     * Get domain ID
     *
     * @return string
     */
    public function getId()
    {
        return $this->parseText('domain id');
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
     * Search text on result
     *
     * @param  string  $text
     * @param  integer $index Match index
     * @return string
     */
    private function parseText($text, $index = 1)
    {
        preg_match('/'.$text.': ?(.*)/i', $this->result, $match);

        return isset($match[$index]) ? trim(preg_replace('/\s+/', ' ', $match[$index])) : null;
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
