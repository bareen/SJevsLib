<?php

namespace SJevs\LibBundle;

use SJevs\LibBundle\Preg;

/*
 * Grabber with Tor functionality
 *
  @author Sergej Jevsejev
  @url http://sjevsejev.blogspot.com
 */

class Grabber
{
    private $ch;
    private $cookie = '';
    private $userAgent;

    /* @var $preg preg Preg */
    private $preg;   // https://github.com/sjevs/SJevsLib/blob/master/Preg.php

    private $torSettings = array(
        'ip' => '127.0.0.1',
        'port' => '9050',
        'pass' => '**********'
    );

    public function __construct()
    {
        $this->userAgent = $this->setNewUserAgent();

        $this->ch = curl_init();
        curl_setopt_array($this->ch, array(
            CURLOPT_PROXY => $this->torSettings['ip'].':'.$this->torSettings['port'],
            CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => $this->userAgent
        ));

    }

    public function __destruct()
    {
        curl_close($this->ch);
    }

    /*
     * @var $url string hyperlink http://www.somepage.com
     * @return string Response
     */
    public function get($url)
    {
        curl_setopt($this->ch,CURLOPT_POST, false);
        curl_setopt($this->ch, CURLOPT_URL, $url);

        return curl_exec($this->ch);
    }

    /*
    * @var $url string hyperlink http://www.somepage.com
    * @var $fields array Post data array('name'=>'theName','pass'=>'password');
    * @return string Response
    */
    public function post($url, $fields)
    {
        curl_setopt($this->ch,CURLOPT_POST,true);
        curl_setopt($this->ch,CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($this->ch, CURLOPT_URL, $url);
        return curl_exec($this->ch);
    }

    /*
     * Enable to use cookie
     */
    public function setCookie($cookie)
    {
        $this->cookie = $cookie;
        curl_setopt($this->ch, CURLOPT_COOKIE, $cookie);
    }

    /*
     * @var $preg string Regular expression with "()" to match substring
     * @var $text string Text to search in
     * @return string
     *
     * getOne('/sup(er)str/', 'my superstr')
     * returns: "er"
     */
    public function getPregOne($url, $preg)
    {
        $txt = $this->get($url);
        return $this->preg()->getOne($preg, $txt);
    }

    /*
    * @var $preg string Regular expression with "()" to match substring
    * @var $text string Text to search in
    * @return array
    *
    * getAll('/([\w]+s)/', 'this is my string');
    * returns: array([0]=>'this', [1]=>'is');
    */
    public function getPregAll($url, $preg)
    {
        $txt = $this->get($url);
        return $this->preg()->getAll($preg, $txt);
    }

    /*
     * return class Preg
     */
    public function preg()
    {
        if(empty($this->preg))
        {
            $this->preg = new Preg();
        }

        return $this->preg;
    }

    public function torSetNewIdentity()
    {
        $fp = fsockopen($this->torSettings['ip'], $this->torSettings['port']);
        if (!$fp) {
            return false;
        }

        fputs($fp, "AUTHENTICATE ".$this->torSettings['pass']."\r\n");
        $response = fread($fp, 1024);
        list($code, $text) = explode(' ', $response, 2);
        if ($code != '250') {
            return false;
        }

        fputs($fp, "signal NEWNYM\r\n");
        $response = fread($fp, 1024);
        list($code, $text) = explode(' ', $response, 2);
        if ($code != '250') {
            return false;
        }
        fclose($fp);

        $this->setNewUserAgent();

        return true;
    }

    /**
     * set useragent
     */
    private function setNewUserAgent()
    {
        //list of browsers
        $agentBrowser = array(
            'Firefox',
            'Safari',
            'Opera',
            'Flock',
            'Internet Explorer',
            'Seamonkey',
            'Konqueror',
            'GoogleBot'
        );
        //list of operating systems
        $agentOS = array(
            'Windows 3.1',
            'Windows 95',
            'Windows 98',
            'Windows 2000',
            'Windows NT',
            'Windows XP',
            'Windows Vista',
            'Redhat Linux',
            'Ubuntu',
            'Fedora',
            'AmigaOS',
            'OS 10.5'
        );

        //randomly generate UserAgent
        $this->userAgent = $agentBrowser[rand(0,7)].'/'.rand(1,8).'.'.rand(0,9).' (' .$agentOS[rand(0,11)].' '.rand(1,7).'.'.rand(0,9).'; en-US;)';
    }
}