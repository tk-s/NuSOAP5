<?php

/*
====================================================================
Original EULA
====================================================================

NuSOAP - Web Services Toolkit for PHP

Copyright (c) 2002 NuSphere Corporation

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

The NuSOAP project home is:
http://sourceforge.net/projects/nusoap/

The primary support for NuSOAP is the Help forum on the project home page.

If you have any questions or comments, please email:

Dietrich Ayala
dietrich@ganx4.com
http://dietrich.ganx4.com/nusoap

NuSphere Corporation
http://www.nusphere.com
*/

/*
 *  Some of the standards implmented in whole or part by NuSOAP:
 *
 *  SOAP 1.1 (http://www.w3.org/TR/2000/NOTE-SOAP-20000508/)
 *  WSDL 1.1 (http://www.w3.org/TR/2001/NOTE-wsdl-20010315)
 *  SOAP Messages With Attachments (http://www.w3.org/TR/SOAP-attachments)
 *  XML 1.0 (http://www.w3.org/TR/2006/REC-xml-20060816/)
 *  Namespaces in XML 1.0 (http://www.w3.org/TR/2006/REC-xml-names-20060816/)
 *  XML Schema 1.0 (http://www.w3.org/TR/xmlschema-0/)
 *  RFC 2045 Multipurpose Internet Mail Extensions (MIME) Part One: Format of Internet Message Bodies
 *  RFC 2068 Hypertext Transfer Protocol -- HTTP/1.1
 *  RFC 2617 HTTP Authentication: Basic and Digest Access Authentication
 */

/**
 * =======================================================================
 * PHP 5 Rewrite
 * =======================================================================
 * 
 * @author  Daniel Carbone (daniel.p.carbone@gmail.com)
 * @version  1.0
 * @link  https://github.com/dcarbone
 * 
 * This rewrite is intended to bring the NuSOAP library up to more modern PHP
 * standards, including the removal of the use of $GLOBALS and same-name
 * class constructors.
 * 
 * It also implements Namespacing to keep things clean
 * 
 * For now additional functionality is not the focus, this is a
 * modernization effort only.
 * 
 */

namespace NuSOAP;

/**
* transport class for sending/receiving data via HTTP and HTTPS
* NOTE: PHP must be compiled with the CURL extension for HTTPS support
*
* @author   Dietrich Ayala <dietrich@ganx4.com>
* @author   Scott Nichol <snichol@users.sourceforge.net>
* @author   Daniel Carbone <daniel.p.carbone@gmail.com>
* @access public
*/
class TransportHTTP extends Base
{
    /**
     * [$url description]
     * @var string
     */
    public $url = '';

    /**
     * [$uri description]
     * @var string
     */
    public $uri = '';

    /**
     * [$digetURI description]
     * @var string
     */
    public $digetURI = '';

    /**
     * [$scheme description]
     * @var string
     */
    public $scheme = '';

    /**
     * [$host description]
     * @var string
     */
    public $host = '';

    /**
     * [$port description]
     * @var string
     */
    public $port = '';

    /**
     * [$path description]
     * @var string
     */
    public $path = '';

    /**
     * [$requestMethod description]
     * @var string
     */
    public $requestMethod = 'POST';

    /**
     * [$protocolVersion description]
     * @var string
     */
    public $protocolVersion = '1.0';

    /**
     * [$encoding description]
     * @var string
     */
    public $encoding = '';

    /**
     * [$outgoingHeaders description]
     * @var array
     */
    public $outgoingHeaders = array();

    /**
     * [$incomingHeaders description]
     * @var array
     */
    public $incomingHeaders = array();

    /**
     * [$incomingCookies description]
     * @var array
     */
    public $incomingCookies = array();

    /**
     * [$outgoingPayload description]
     * @var string
     */
    public $outgoingPayload = '';

    /**
     * [$incomingPayload description]
     * @var string
     */
    public $incomingPayload = '';

    /**
     *  HTTP response status line
     * @var [type]
     */
    public $responseStatusLine;

    /**
     * [$useSOAPAction description]
     * @var boolean
     */
    public $useSOAPAction = true;
    
    /**
     * [$persistentConnection description]
     * @var boolean
     */
    public $persistentConnection = false;
    
    /**
     * cURL handle
     * @var boolean
     */
    public $ch = false;
    
    /**
     * cURL custom options
     * @var array
     */
    public $chOptions = array();

    /**
     * force cURL use
     * @var boolean
     */
    public $useCurl = false;
    
    /**
     * proxy information (associative array)
     * @var [type]
     */
    public $proxy = null;

    /**
     * [$username description]
     * @var string
     */
    public $username = '';

    /**
     * [$password description]
     * @var string
     */
    public $password = '';

    /**
     * [$authtype description]
     * @var string
     */
    public $authtype = '';

    /**
     * [$digestRequest description]
     * @var array
     */
    public $digestRequest = array();

    /**
     * keys must be cainfofile (optional), sslcertfile, sslkeyfile, passphrase, certpassword (optional), verifypeer (optional), verifyhost (optional)
     * cainfofile: certificate authority file, e.g. '$pathToPemFiles/rootca.pem'
     * sslcertfile: SSL certificate file, e.g. '$pathToPemFiles/mycert.pem'
     * sslkeyfile: SSL key file, e.g. '$pathToPemFiles/mykey.pem'
     * passphrase: SSL key password/passphrase
     * certpassword: SSL certificate password
     * verifypeer: default is 1
     * verifyhost: default is 1
     * @var array
     */
    public $certRequest = array();

    /**
    * constructor
    *
    * @param string $url The URL to which to connect
    * @param array $curl_options User-specified cURL options
    * @param boolean $useCurl Whether to try to force cURL use
    * @access public
    */
    function __construct($url, $curl_options = NULL, $useCurl = false)
    {
        parent::__construct();
        $this->_debug("ctor url=$url useCurl=$useCurl curl_options:");
        $this->appendDebug($this->varDump($curl_options));
        $this->_setURL($url);
        
        if (is_array($curl_options))
        {
            $this->chOptions = $curl_options;
        }
        $this->useCurl = $useCurl;
        preg_match('/\$Revisio' . 'n: ([^ ]+)/', $this->revision, $rev);
        $this->_setHeader('User-Agent', $this->title.'/'.$this->version.' ('.$rev[1].')');
    }

    /**
    * sets a cURL option
    *
    * @param    mixed $option The cURL option (always integer?)
    * @param    mixed $value The cURL option value
    * @access   protected
    */
    protected function _setCurlOption($option, $value)
    {
        $this->_debug("setCurlOption option=$option, value=");
        $this->appendDebug($this->varDump($value));
        curl_setopt($this->ch, $option, $value);
    }

    /**
    * sets an HTTP header
    *
    * @param string $name The name of the header
    * @param string $value The value of the header
    * @access protected
    */
    function _setHeader($name, $value)
    {
        $this->outgoingHeaders[$name] = $value;
        $this->_debug("set header $name: $value");
    }

    /**
    * unsets an HTTP header
    *
    * @param string $name The name of the header
    * @access protected
    */
    protected function _unsetHeader($name)
    {
        if (isset($this->outgoingHeaders[$name]))
        {
            $this->_debug("unset header $name");
            unset($this->outgoingHeaders[$name]);
        }
    }

    /**
    * sets the URL to which to connect
    *
    * @param string $url The URL to which to connect
    * @access protected
    */
    protected function _setURL($url)
    {
        $this->url = $url;

        $u = parse_url($url);
        foreach ($u as $k => $v)
        {
            $this->_debug("parsed URL $k = $v");
            $this->$k = $v;
        }
        
        // add any GET params to path
        if (isset($u['query']) && $u['query'] != '')
        {
            $this->path .= '?' . $u['query'];
        }
        
        // set default port
        if (!isset($u['port']))
        {
            if ($u['scheme'] == 'https')
            {
                $this->port = 443;
            }
            else
            {
                $this->port = 80;
            }
        }
        
        $this->uri = $this->path;
        $this->digetURI = $this->uri;
        
        // build headers
        if (!isset($u['port']))
        {
            $this->_setHeader('Host', $this->host);
        }
        else
        {
            $this->_setHeader('Host', $this->host.':'.$this->port);
        }

        if (isset($u['user']) && $u['user'] != '')
        {
            $this->setCredentials(urldecode($u['user']), isset($u['pass']) ? urldecode($u['pass']) : '');
        }
    }

    /**
    * gets the I/O method to use
    *
    * @return   string  I/O method to use (socket|curl|unknown)
    * @access   protected
    */
    protected function _IOMethod()
    {
        if ($this->useCurl ||
            $this->scheme == 'https' ||
            ($this->scheme == 'http' && $this->authtype == 'ntlm') ||
            ($this->scheme == 'http' && is_array($this->proxy) &&
            $this->proxy['authtype'] == 'ntlm'))
        {
            return 'curl';
        }
        if (($this->scheme == 'http' || $this->scheme == 'ssl') &&
            $this->authtype != 'ntlm' &&
            !is_array($this->proxy) ||
            $this->proxy['authtype'] != 'ntlm')
        {
            return 'socket';
        }

        return 'unknown';
    }

    /**
    * establish an HTTP connection
    *
    * @param    integer $timeout set connection timeout in seconds
    * @param    integer $response_timeout set response timeout in seconds
    * @return   boolean true if connected, false if not
    * @access   protected
    */
    protected function _connect($connection_timeout = 0, $response_timeout = 30)
    {
        $this->_debug("connect connection_timeout $connection_timeout, response_timeout $response_timeout, scheme $this->scheme, host $this->host, port $this->port");
      
        if ($this->_IOMethod() == 'socket')
        {
            if (!is_array($this->proxy))
            {
                $host = $this->host;
                $port = $this->port;
            }
            else
            {
                $host = $this->proxy['host'];
                $port = $this->proxy['port'];
            }

            // use persistent connection
            if ($this->persistentConnection && isset($this->fp) && is_resource($this->fp))
            {
                if (!feof($this->fp))
                {
                    $this->_debug('Re-use persistent connection');
                    return true;
                }
                fclose($this->fp);
                $this->_debug('Closed persistent connection at EOF');
            }

            // munge host if using OpenSSL
            if ($this->scheme == 'ssl')
            {
                $host = 'ssl://' . $host;
            }
            $this->_debug('calling fsockopen with host ' . $host . ' connection_timeout ' . $connection_timeout);

            // open socket
            if ($connection_timeout > 0)
            {
                $this->fp = @fsockopen( $host, $this->port, $this->errno, $this->error_str, $connection_timeout);
            }
            else
            {
                $this->fp = @fsockopen( $host, $this->port, $this->errno, $this->error_str);
            }
            
            // test pointer
            if (!$this->fp)
            {
                $msg = 'Couldn\'t open socket connection to server ' . $this->url;
                if ($this->errno)
                {
                    $msg .= ', Error ('.$this->errno.'): '.$this->error_str;
                }
                else
                {
                    $msg .= ' prior to connect().  This is often a problem looking up the host name.';
                }
                $this->_debug($msg);
                $this->_setError($msg);
                return false;
            }
            
            // set response timeout
            $this->_debug('set response timeout to ' . $response_timeout);
            socket_set_timeout( $this->fp, $response_timeout);

            $this->_debug('socket connected');
            return true;
        }
        else if ($this->_IOMethod() == 'curl')
        {
            if (!extension_loaded('curl'))
            {
    //          $this->_setError('cURL Extension, or OpenSSL extension w/ PHP version >= 4.3 is required for HTTPS');
                $this->_setError('The PHP cURL Extension is required for HTTPS or NLTM.  You will need to re-build or update your PHP to include cURL or change php.ini to load the PHP cURL extension.');
                return false;
            }
            // Avoid warnings when PHP does not have these options
            if (defined('CURLOPT_CONNECTIONTIMEOUT'))
                $CURLOPT_CONNECTIONTIMEOUT = CURLOPT_CONNECTIONTIMEOUT;
            else
                $CURLOPT_CONNECTIONTIMEOUT = 78;

            if (defined('CURLOPT_HTTPAUTH'))
                $CURLOPT_HTTPAUTH = CURLOPT_HTTPAUTH;
            else
                $CURLOPT_HTTPAUTH = 107;
            
            if (defined('CURLOPT_PROXYAUTH'))
                $CURLOPT_PROXYAUTH = CURLOPT_PROXYAUTH;
            else
                $CURLOPT_PROXYAUTH = 111;
            
            if (defined('CURLAUTH_BASIC'))
                $CURLAUTH_BASIC = CURLAUTH_BASIC;
            else
                $CURLAUTH_BASIC = 1;
            
            if (defined('CURLAUTH_DIGEST'))
                $CURLAUTH_DIGEST = CURLAUTH_DIGEST;
            else
                $CURLAUTH_DIGEST = 2;
            
            if (defined('CURLAUTH_NTLM'))
                $CURLAUTH_NTLM = CURLAUTH_NTLM;
            else
                $CURLAUTH_NTLM = 8;

            $this->_debug('connect using cURL');
            // init CURL
            $this->ch = curl_init();
            // set url
            $hostURL = ($this->port != '') ? "$this->scheme://$this->host:$this->port" : "$this->scheme://$this->host";
            // add path
            $hostURL .= $this->path;
            $this->_setCurlOption(CURLOPT_URL, $hostURL);
            
            // follow location headers (re-directs)
            if (ini_get('open_basedir'))
            {
                $this->_debug('open_basedir set, so do not set CURLOPT_FOLLOWLOCATION');
                $this->_debug('open_basedir = ');
                $this->appendDebug($this->varDump(ini_get('open_basedir')));
            }
            else
            {
                $this->_setCurlOption(CURLOPT_FOLLOWLOCATION, 1);
            }
            // ask for headers in the response output
            $this->_setCurlOption(CURLOPT_HEADER, 1);
            // ask for the response output as the return value
            $this->_setCurlOption(CURLOPT_RETURNTRANSFER, 1);
            // encode
            // We manage this ourselves through headers and encoding
    //      if (function_exists('gzuncompress')){
    //          $this->_setCurlOption(CURLOPT_ENCODING, 'deflate');
    //      }
            // persistent connection
            if ($this->persistentConnection)
            {
                // I believe the following comment is now bogus, having applied to
                // the code when it used CURLOPT_CUSTOMREQUEST to send the request.
                // The way we send data, we cannot use persistent connections, since
                // there will be some "junk" at the end of our request.
                //$this->_setCurlOption(CURL_HTTP_VERSION_1_1, true);
                $this->persistentConnection = false;
                $this->_setHeader('Connection', 'close');
            }
            
            // set timeouts
            if ($connection_timeout != 0)
            {
                $this->_setCurlOption($CURLOPT_CONNECTIONTIMEOUT, $connection_timeout);
            }
            
            if ($response_timeout != 0)
            {
                $this->_setCurlOption(CURLOPT_TIMEOUT, $response_timeout);
            }

            if ($this->scheme == 'https')
            {
                $this->_debug('set cURL SSL verify options');
                
                // recent versions of cURL turn on peer/host checking by default,
                // while PHP binaries are not compiled with a default location for the
                // CA cert bundle, so disable peer/host checking.
                
                $this->_setCurlOption(CURLOPT_SSL_VERIFYPEER, 0);
                $this->_setCurlOption(CURLOPT_SSL_VERIFYHOST, 0);
        
                // support client certificates (thanks Tobias Boes, Doug Anarino, Eryan Ariobowo)
                if ($this->authtype == 'certificate')
                {
                    $this->_debug('set cURL certificate options');
                    if (isset($this->certRequest['cainfofile']))
                    {
                        $this->_setCurlOption(CURLOPT_CAINFO, $this->certRequest['cainfofile']);
                    }
                    
                    if (isset($this->certRequest['verifypeer']))
                    {
                        $this->_setCurlOption(CURLOPT_SSL_VERIFYPEER, $this->certRequest['verifypeer']);
                    }
                    else
                    {
                        $this->_setCurlOption(CURLOPT_SSL_VERIFYPEER, 1);
                    }
                    
                    if (isset($this->certRequest['verifyhost']))
                    {
                        $this->_setCurlOption(CURLOPT_SSL_VERIFYHOST, $this->certRequest['verifyhost']);
                    }
                    else
                    {
                        $this->_setCurlOption(CURLOPT_SSL_VERIFYHOST, 1);
                    }
                    
                    if (isset($this->certRequest['sslcertfile']))
                    {
                        $this->_setCurlOption(CURLOPT_SSLCERT, $this->certRequest['sslcertfile']);
                    }
                    
                    if (isset($this->certRequest['sslkeyfile']))
                    {
                        $this->_setCurlOption(CURLOPT_SSLKEY, $this->certRequest['sslkeyfile']);
                    }
                    
                    if (isset($this->certRequest['passphrase']))
                    {
                        $this->_setCurlOption(CURLOPT_SSLKEYPASSWD, $this->certRequest['passphrase']);
                    }
                    
                    if (isset($this->certRequest['certpassword']))
                    {
                        $this->_setCurlOption(CURLOPT_SSLCERTPASSWD, $this->certRequest['certpassword']);
                    }
                }
            }
            if ($this->authtype && ($this->authtype != 'certificate'))
            {
                if ($this->username)
                {
                    $this->_debug('set cURL username/password');
                    $this->_setCurlOption(CURLOPT_USERPWD, "$this->username:$this->password");
                }
                
                if ($this->authtype == 'basic')
                {
                    $this->_debug('set cURL for Basic authentication');
                    $this->_setCurlOption($CURLOPT_HTTPAUTH, $CURLAUTH_BASIC);
                }
                
                if ($this->authtype == 'digest')
                {
                    $this->_debug('set cURL for digest authentication');
                    $this->_setCurlOption($CURLOPT_HTTPAUTH, $CURLAUTH_DIGEST);
                }
                
                if ($this->authtype == 'ntlm')
                {
                    $this->_debug('set cURL for NTLM authentication');
                    $this->_setCurlOption($CURLOPT_HTTPAUTH, $CURLAUTH_NTLM);
                }
            }

            if (is_array($this->proxy))
            {
                $this->_debug('set cURL proxy options');
                if ($this->proxy['port'] != '')
                {
                    $this->_setCurlOption(CURLOPT_PROXY, $this->proxy['host'].':'.$this->proxy['port']);
                }
                else
                {
                    $this->_setCurlOption(CURLOPT_PROXY, $this->proxy['host']);
                }
                
                if ($this->proxy['username'] || $this->proxy['password'])
                {
                    $this->_debug('set cURL proxy authentication options');
                    $this->_setCurlOption(CURLOPT_PROXYUSERPWD, $this->proxy['username'].':'.$this->proxy['password']);
                    if ($this->proxy['authtype'] == 'basic')
                    {
                        $this->_setCurlOption($CURLOPT_PROXYAUTH, $CURLAUTH_BASIC);
                    }
                    
                    if ($this->proxy['authtype'] == 'ntlm')
                    {
                        $this->_setCurlOption($CURLOPT_PROXYAUTH, $CURLAUTH_NTLM);
                    }
                }
            }
            $this->_debug('cURL connection set up');
            return true;
        }
        else
        {
            $this->_setError('Unknown scheme ' . $this->scheme);
            $this->_debug('Unknown scheme ' . $this->scheme);
            return false;
        }
    }

    /**
    * sends the SOAP request and gets the SOAP response via HTTP[S]
    *
    * @param    string $data message data
    * @param    integer $timeout set connection timeout in seconds
    * @param    integer $response_timeout set response timeout in seconds
    * @param    array $cookies cookies to send
    * @return   string data
    * @access   public
    */
    public function send($data, $timeout=0, $response_timeout=30, $cookies=NULL)
    {
        $this->_debug('entered send() with data of length: '.strlen($data));

        $this->tryagain = true;
        $tries = 0;
        while ($this->tryagain)
        {
            $this->tryagain = false;
            if ($tries++ < 2)
            {
                // make connnection
                if (!$this->_connect($timeout, $response_timeout))
                {
                    return false;
                }
                
                // send request
                if (!$this->_sendRequest($data, $cookies))
                {
                    return false;
                }
                
                // get response
                $respdata = $this->_getResponse();
            }
            else
            {
                $this->_setError("Too many tries to get an OK response ($this->responseStatusLine)");
            }
        }       
        $this->_debug('end of send()');
        return $respdata;
    }
    
    /**
    * if authenticating, set user credentials here
    *
    * @param    string $username
    * @param    string $password
    * @param    string $authtype (basic|digest|certificate|ntlm)
    * @param    array $digestRequest (keys must be nonce, nc, realm, qop)
    * @param    array $certRequest (keys must be cainfofile (optional), sslcertfile, sslkeyfile, passphrase, certpassword (optional), verifypeer (optional), verifyhost (optional): see corresponding options in cURL docs)
    * @access   public
    */
    public function setCredentials(
        $username,
        $password,
        $authtype = 'basic',
        Array $digestRequest = array(),
        Array $certRequest = array())
    {
        $this->_debug("setCredentials username=$username authtype=$authtype digestRequest=");
        $this->appendDebug($this->varDump($digestRequest));
        $this->_debug("certRequest=");
        $this->appendDebug($this->varDump($certRequest));
        
        // cf. RFC 2617
        if ($authtype == 'basic')
        {
            $this->_setHeader('Authorization', 'Basic '.base64_encode(str_replace(':','',$username).':'.$password));
        }
        else if ($authtype == 'digest')
        {
            if (isset($digestRequest['nonce']))
            {
                $digestRequest['nc'] = isset($digestRequest['nc']) ? $digestRequest['nc']++ : 1;
                
                // calculate the Digest hashes (calculate code based on digest implementation found at: http://www.rassoc.com/gregr/weblog/stories/2002/07/09/webServicesSecurityHttpDigestAuthenticationWithoutActiveDirectory.html)
    
                // A1 = unq(username-value) ":" unq(realm-value) ":" passwd
                $A1 = $username. ':' . (isset($digestRequest['realm']) ? $digestRequest['realm'] : '') . ':' . $password;
    
                // H(A1) = MD5(A1)
                $HA1 = md5($A1);
    
                // A2 = Method ":" digest-uri-value
                $A2 = $this->requestMethod . ':' . $this->digetURI;
    
                // H(A2)
                $HA2 =  md5($A2);
    
                // KD(secret, data) = H(concat(secret, ":", data))
                // if qop == auth:
                // request-digest  = <"> < KD ( H(A1),     unq(nonce-value)
                //                              ":" nc-value
                //                              ":" unq(cnonce-value)
                //                              ":" unq(qop-value)
                //                              ":" H(A2)
                //                            ) <">
                // if qop is missing,
                // request-digest  = <"> < KD ( H(A1), unq(nonce-value) ":" H(A2) ) > <">
    
                $unhashedDigest = '';
                $nonce = isset($digestRequest['nonce']) ? $digestRequest['nonce'] : '';
                $cnonce = $nonce;
                if ($digestRequest['qop'] != '')
                {
                    $unhashedDigest = $HA1 . ':' . $nonce . ':' . sprintf("%08d", $digestRequest['nc']) . ':' . $cnonce . ':' . $digestRequest['qop'] . ':' . $HA2;
                }
                else
                {
                    $unhashedDigest = $HA1 . ':' . $nonce . ':' . $HA2;
                }
    
                $hashedDigest = md5($unhashedDigest);
    
                $opaque = '';   
                if (isset($digestRequest['opaque']))
                {
                    $opaque = ', opaque="' . $digestRequest['opaque'] . '"';
                }

                $this->_setHeader('Authorization', 'Digest username="' . $username . '", realm="' . $digestRequest['realm'] . '", nonce="' . $nonce . '", uri="' . $this->digetURI . $opaque . '", cnonce="' . $cnonce . '", nc=' . sprintf("%08x", $digestRequest['nc']) . ', qop="' . $digestRequest['qop'] . '", response="' . $hashedDigest . '"');
            }
        }
        else if ($authtype == 'certificate')
        {
            $this->certRequest = $certRequest;
            $this->_debug('Authorization header not set for certificate');
        }
        else if ($authtype == 'ntlm')
        {
            // do nothing
            $this->_debug('Authorization header not set for ntlm');
        }
        $this->username = $username;
        $this->password = $password;
        $this->authtype = $authtype;
        $this->digestRequest = $digestRequest;
    }
    
    /**
    * set the soapaction value
    *
    * @param    string $soapaction
    * @access   public
    */
    public function setSOAPAction($soapaction)
    {
        $this->_setHeader('SOAPAction', '"' . $soapaction . '"');
    }
    
    /**
    * use http encoding
    *
    * @param    string $enc encoding style. supported values: gzip, deflate, or both
    * @access   public
    */
    public function setEncoding($enc = 'gzip, deflate')
    {
        if (function_exists('gzdeflate'))
        {
            $this->protocolVersion = '1.1';
            $this->_setHeader('Accept-Encoding', $enc);
            if (!isset($this->outgoingHeaders['Connection']))
            {
                $this->_setHeader('Connection', 'close');
                $this->persistentConnection = false;
            }
            $this->encoding = $enc;
        }
    }
    
    /**
    * set proxy info here
    *
    * @param    string $proxyhost use an empty string to remove proxy
    * @param    string $proxyport
    * @param    string $proxyusername
    * @param    string $proxypassword
    * @param    string $proxyauthtype (basic|ntlm)
    * @access   public
    */
    public function setProxy(
        $proxyhost,
        $proxyport,
        $proxyusername = '',
        $proxypassword = '',
        $proxyauthtype = 'basic')
    {
        if ($proxyhost)
        {
            $this->proxy = array(
                'host' => $proxyhost,
                'port' => $proxyport,
                'username' => $proxyusername,
                'password' => $proxypassword,
                'authtype' => $proxyauthtype
            );
            if ($proxyusername != '' && $proxypassword != '' && $proxyauthtype = 'basic')
            {
                $this->_setHeader('Proxy-Authorization', ' Basic '.base64_encode($proxyusername.':'.$proxypassword));
            }
        }
        else
        {
            $this->_debug('remove proxy');
            $proxy = null;
            unsetHeader('Proxy-Authorization');
        }
    }
    

    /**
     * Test if the given string starts with a header that is to be skipped.
     * Skippable headers result from chunked transfer and proxy requests.
     *
     * @param   string $data The string to check.
     * @return boolean Whether a skippable header was found.
     * @access  protected
     */
    protected function _isSkippableCurlHeader(&$data)
    {
        $skipHeaders = array(   'HTTP/1.1 100',
                                'HTTP/1.0 301',
                                'HTTP/1.1 301',
                                'HTTP/1.0 302',
                                'HTTP/1.1 302',
                                'HTTP/1.0 401',
                                'HTTP/1.1 401',
                                'HTTP/1.0 200 Connection established');
        foreach ($skipHeaders as $hd)
        {
            $prefix = substr($data, 0, strlen($hd));
            if ($prefix == $hd) return true;
        }

        return false;
    }

    /**
    * decode a string that is encoded w/ "chunked' transfer encoding
    * as defined in RFC2068 19.4.6
    *
    * @param    string $buffer
    * @param    string $lb
    * @return  string
    * @access   public
    * @deprecated
    */
    public function decodeChunked($buffer, $lb)
    {
        // length := 0
        $length = 0;
        $new = '';
        
        // read chunk-size, chunk-extension (if any) and CRLF
        // get the position of the linebreak
        $chunkend = strpos($buffer, $lb);
        if ($chunkend == FALSE)
        {
            $this->_debug('no linebreak found in decodeChunked');
            return $new;
        }
        $temp = substr($buffer,0,$chunkend);
        $chunk_size = hexdec( trim($temp) );
        $chunkstart = $chunkend + strlen($lb);
        // while (chunk-size > 0) {
        while ($chunk_size > 0)
        {
            $this->_debug("chunkstart: $chunkstart chunk_size: $chunk_size");
            $chunkend = strpos( $buffer, $lb, $chunkstart + $chunk_size);
            
            // Just in case we got a broken connection
            if ($chunkend == FALSE)
            {
                $chunk = substr($buffer,$chunkstart);
                // append chunk-data to entity-body
                $new .= $chunk;
                $length += strlen($chunk);
                break;
            }
            
            // read chunk-data and CRLF
            $chunk = substr($buffer,$chunkstart,$chunkend-$chunkstart);
            // append chunk-data to entity-body
            $new .= $chunk;
            // length := length + chunk-size
            $length += strlen($chunk);
            // read chunk-size and CRLF
            $chunkstart = $chunkend + strlen($lb);
            
            $chunkend = strpos($buffer, $lb, $chunkstart) + strlen($lb);
            if ($chunkend == FALSE)
            {
                break; //Just in case we got a broken connection
            }
            $temp = substr($buffer,$chunkstart,$chunkend-$chunkstart);
            $chunk_size = hexdec( trim($temp) );
            $chunkstart = $chunkend;
        }
        return $new;
    }
    
    /**
     * Writes the payload, including HTTP headers, to $this->outgoingPayload.
     *
     * @param   string $data HTTP body
     * @param   string $cookie_str data for HTTP Cookie header
     * @return  void
     * @access  protected
     */
    protected function _buildPayload($data, $cookie_str = '')
    {
        // Note: for cURL connections, $this->outgoingPayload is ignored,
        // as is the Content-Length header, but these are still created as
        // debugging guides.

        // add content-length header
        if ($this->requestMethod != 'GET') {
            $this->_setHeader('Content-Length', strlen($data));
        }

        // start building outgoing payload:
        if ($this->proxy)
        {
            $uri = $this->url;
        }
        else
        {
            $uri = $this->uri;
        }
        
        $req = "$this->requestMethod $uri HTTP/$this->protocolVersion";
        $this->_debug("HTTP request: $req");
        $this->outgoingPayload = "$req\r\n";

        // loop thru headers, serializing
        foreach ($this->outgoingHeaders as $k => $v)
        {
            $hdr = $k.': '.$v;
            $this->_debug("HTTP header: $hdr");
            $this->outgoingPayload .= "$hdr\r\n";
        }

        // add any cookies
        if ($cookie_str != '')
        {
            $hdr = 'Cookie: '.$cookie_str;
            $this->_debug("HTTP header: $hdr");
            $this->outgoingPayload .= "$hdr\r\n";
        }

        // header/body separator
        $this->outgoingPayload .= "\r\n";
        
        // add data
        $this->outgoingPayload .= $data;
    }

    /**
    * sends the SOAP request via HTTP[S]
    *
    * @param    string $data message data
    * @param    array $cookies cookies to send
    * @return   boolean true if OK, false if problem
    * @access   protected
    */
    protected function _sendRequest($data, $cookies = NULL)
    {
        // build cookie string
        $cookie_str = $this->_getCookiesForRequest($cookies, (($this->scheme == 'ssl') || ($this->scheme == 'https')));

        // build payload
        $this->_buildPayload($data, $cookie_str);

        if ($this->_IOMethod() == 'socket')
        {
            // send payload
            if (!fputs($this->fp, $this->outgoingPayload, strlen($this->outgoingPayload))) {
                $this->_setError('couldn\'t write message data to socket');
                $this->_debug('couldn\'t write message data to socket');
                return false;
            }
            $this->_debug('wrote data to socket, length = ' . strlen($this->outgoingPayload));
            return true;
        }
        else if ($this->_IOMethod() == 'curl')
        {
            // set payload
            // cURL does say this should only be the verb, and in fact it
            // turns out that the URI and HTTP version are appended to this, which
            // some servers refuse to work with (so we no longer use this method!)
            //$this->_setCurlOption(CURLOPT_CUSTOMREQUEST, $this->outgoingPayload);
            $curl_headers = array();
            foreach ($this->outgoingHeaders as $k => $v)
            {
                switch($k)
                {
                    case "Connection" :
                    case "Content-Length" :
                    case "Host" :
                    case "Authorization" :
                    case "Proxy-Authorization" :
                        $this->_debug("Skip cURL header $k: $v");
                    break;

                    default : 
                        $curl_headers[] = "$k: $v";
                    break;
                }
            }
            if ($cookie_str != '')
            {
                $curl_headers[] = 'Cookie: ' . $cookie_str;
            }
            $this->_setCurlOption(CURLOPT_HTTPHEADER, $curl_headers);
            $this->_debug('set cURL HTTP headers');
            if ($this->requestMethod == "POST")
            {
                $this->_setCurlOption(CURLOPT_POST, 1);
                $this->_setCurlOption(CURLOPT_POSTFIELDS, $data);
                $this->_debug('set cURL POST data');
            }
            else
            {
                // (?)
            }
            // insert custom user-set cURL options
            foreach ($this->chOptions as $key => $val)
            {
                $this->_setCurlOption($key, $val);
            }

            $this->_debug('set cURL payload');
            return true;
        }
    }

    /**
    * gets the SOAP response via HTTP[S]
    *
    * @return   string the response (also sets member variables like incomingPayload)
    * @access   protected
    */
    protected function _getResponse()
    {
        $this->incomingPayload = '';
        
        if ($this->_IOMethod() == 'socket')
        {
            // loop until headers have been retrieved
            $data = '';
            while (!isset($lb))
            {
                // We might EOF during header read.
                if (feof($this->fp))
                {
                    $this->incomingPayload = $data;
                    $this->_debug('found no headers before EOF after length ' . strlen($data));
                    $this->_debug("received before EOF:\n" . $data);
                    $this->_setError('server failed to send headers');
                    return false;
                }

                $tmp = fgets($this->fp, 256);
                $tmplen = strlen($tmp);
                $this->_debug("read line of $tmplen bytes: " . trim($tmp));

                if ($tmplen == 0)
                {
                    $this->incomingPayload = $data;
                    $this->_debug('socket read of headers timed out after length ' . strlen($data));
                    $this->_debug("read before timeout: " . $data);
                    $this->_setError('socket read of headers timed out');
                    return false;
                }

                $data .= $tmp;
                $pos = strpos($data,"\r\n\r\n");
                if ($pos > 1)
                {
                    $lb = "\r\n";
                }
                else
                {
                    $pos = strpos($data,"\n\n");
                    if ($pos > 1)
                    {
                        $lb = "\n";
                    }
                }
                // remove 100 headers
                if (isset($lb) && preg_match('/^HTTP\/1.1 100/',$data))
                {
                    unset($lb);
                    $data = '';
                }
            }
            // store header data
            $this->incomingPayload .= $data;
            $this->_debug('found end of headers after length ' . strlen($data));
            // process headers
            $header_data = trim(substr($data,0,$pos));
            $header_array = explode($lb,$header_data);
            $this->incomingHeaders = array();
            $this->incomingCookies = array();
            foreach ($header_array as $header_line)
            {
                $arr = explode(':',$header_line, 2);
                if (count($arr) > 1)
                {
                    $header_name = strtolower(trim($arr[0]));
                    $this->incomingHeaders[$header_name] = trim($arr[1]);
                    if ($header_name == 'set-cookie')
                    {
                        // TODO: allow multiple cookies from parseCookie
                        $cookie = $this->parseCookie(trim($arr[1]));
                        if ($cookie)
                        {
                            $this->incomingCookies[] = $cookie;
                            $this->_debug('found cookie: ' . $cookie['name'] . ' = ' . $cookie['value']);
                        }
                        else
                        {
                            $this->_debug('did not find cookie in ' . trim($arr[1]));
                        }
                    }
                }
                else if (isset($header_name))
                {
                    // append continuation line to previous header
                    $this->incomingHeaders[$header_name] .= $lb . ' ' . $header_line;
                }
            }
            
            // loop until msg has been received
            if (isset($this->incomingHeaders['transfer-encoding']) && strtolower($this->incomingHeaders['transfer-encoding']) == 'chunked')
            {
                $content_length =  2147483647;  // ignore any content-length header
                $chunked = true;
                $this->_debug("want to read chunked content");
            }
            else if (isset($this->incomingHeaders['content-length']))
            {
                $content_length = $this->incomingHeaders['content-length'];
                $chunked = false;
                $this->_debug("want to read content of length $content_length");
            }
            else
            {
                $content_length =  2147483647;
                $chunked = false;
                $this->_debug("want to read content to EOF");
            }
            
            $data = '';
            
            do {
                if ($chunked)
                {
                    $tmp = fgets($this->fp, 256);
                    $tmplen = strlen($tmp);
                    $this->_debug("read chunk line of $tmplen bytes");
                    if ($tmplen == 0)
                    {
                        $this->incomingPayload = $data;
                        $this->_debug('socket read of chunk length timed out after length ' . strlen($data));
                        $this->_debug("read before timeout:\n" . $data);
                        $this->_setError('socket read of chunk length timed out');
                        return false;
                    }
                    $content_length = hexdec(trim($tmp));
                    $this->_debug("chunk length $content_length");
                }
                
                $strlen = 0;
                
                while (($strlen < $content_length) && (!feof($this->fp)))
                {
                    $readlen = min(8192, $content_length - $strlen);
                    $tmp = fread($this->fp, $readlen);
                    $tmplen = strlen($tmp);
                    $this->_debug("read buffer of $tmplen bytes");
                    if (($tmplen == 0) && (!feof($this->fp)))
                    {
                        $this->incomingPayload = $data;
                        $this->_debug('socket read of body timed out after length ' . strlen($data));
                        $this->_debug("read before timeout:\n" . $data);
                        $this->_setError('socket read of body timed out');
                        return false;
                    }
                    $strlen += $tmplen;
                    $data .= $tmp;
                }
                
                if ($chunked && ($content_length > 0))
                {
                    $tmp = fgets($this->fp, 256);
                    $tmplen = strlen($tmp);
                    $this->_debug("read chunk terminator of $tmplen bytes");
                    if ($tmplen == 0)
                    {
                        $this->incomingPayload = $data;
                        $this->_debug('socket read of chunk terminator timed out after length ' . strlen($data));
                        $this->_debug("read before timeout:\n" . $data);
                        $this->_setError('socket read of chunk terminator timed out');
                        return false;
                    }
                }
            } while ($chunked && ($content_length > 0) && (!feof($this->fp)));
            
            if (feof($this->fp))
            {
                $this->_debug('read to EOF');
            }
            
            $this->_debug('read body of length ' . strlen($data));
            $this->incomingPayload .= $data;
            $this->_debug('received a total of '.strlen($this->incomingPayload).' bytes of data from server');
            
            // close filepointer
            if ((isset($this->incomingHeaders['connection']) && strtolower($this->incomingHeaders['connection']) == 'close') || 
                (! $this->persistentConnection) || feof($this->fp))
            {
                fclose($this->fp);
                $this->fp = false;
                $this->_debug('closed socket');
            }
            
            // connection was closed unexpectedly
            if ($this->incomingPayload == '')
            {
                $this->_setError('no response from server');
                return false;
            }
            
            // decode transfer-encoding
    //      if (isset($this->incomingHeaders['transfer-encoding']) && strtolower($this->incomingHeaders['transfer-encoding']) == 'chunked'){
    //          if (!$data = $this->decodeChunked($data, $lb)){
    //              $this->_setError('Decoding of chunked data failed');
    //              return false;
    //          }
                //print "<pre>\nde-chunked:\n---------------\n$data\n\n---------------\n</pre>";
                // set decoded payload
    //          $this->incomingPayload = $header_data.$lb.$lb.$data;
    //      }
    
        }
        else if ($this->_IOMethod() == 'curl')
        {
            // send and receive
            $this->_debug('send and receive with cURL');
            $this->incomingPayload = curl_exec($this->ch);
            $data = $this->incomingPayload;

            $cErr = curl_error($this->ch);
            if ($cErr != '')
            {
                $err = 'cURL ERROR: '.curl_errno($this->ch).': '.$cErr.'<br>';
                // TODO: there is a PHP bug that can cause this to SEGV for CURLINFO_CONTENT_TYPE
                foreach (curl_getinfo($this->ch) as $k => $v)
                {
                    $err .= "$k: $v<br>";
                }
                $this->_debug($err);
                $this->_setError($err);
                curl_close($this->ch);
                return false;
            }
            else
            {
                //echo '<pre>';
                //var_dump(curl_getinfo($this->ch));
                //echo '</pre>';
            }

            // close curl
            $this->_debug('No cURL error, closing cURL');
            curl_close($this->ch);
            
            // try removing skippable headers
            $savedata = $data;
            while ($this->_isSkippableCurlHeader($data))
            {
                $this->_debug("Found HTTP header to skip");
                if ($pos = strpos($data,"\r\n\r\n"))
                {
                    $data = ltrim(substr($data,$pos));
                }
                else if ($pos = strpos($data,"\n\n"))
                {
                    $data = ltrim(substr($data,$pos));
                }
            }

            if ($data == '')
            {
                // have nothing left; just remove 100 header(s)
                $data = $savedata;
                while (preg_match('/^HTTP\/1.1 100/',$data))
                {
                    if ($pos = strpos($data,"\r\n\r\n"))
                    {
                        $data = ltrim(substr($data,$pos));
                    }
                    else if ($pos = strpos($data,"\n\n") )
                    {
                        $data = ltrim(substr($data,$pos));
                    }
                }
            }
            
            // separate content from HTTP headers
            if ($pos = strpos($data,"\r\n\r\n"))
            {
                $lb = "\r\n";
            }
            else if ( $pos = strpos($data,"\n\n"))
            {
                $lb = "\n";
            }
            else
            {
                $this->_debug('no proper separation of headers and document');
                $this->_setError('no proper separation of headers and document');
                return false;
            }
            
            $header_data = trim(substr($data,0,$pos));
            $header_array = explode($lb,$header_data);
            $data = ltrim(substr($data,$pos));
            $this->_debug('found proper separation of headers and document');
            $this->_debug('cleaned data, stringlen: '.strlen($data));
            
            // clean headers
            foreach ($header_array as $header_line)
            {
                $arr = explode(':',$header_line,2);
                if (count($arr) > 1)
                {
                    $header_name = strtolower(trim($arr[0]));
                    $this->incomingHeaders[$header_name] = trim($arr[1]);
                    if ($header_name == 'set-cookie')
                    {
                        // TODO: allow multiple cookies from parseCookie
                        $cookie = $this->parseCookie(trim($arr[1]));
                        if ($cookie)
                        {
                            $this->incomingCookies[] = $cookie;
                            $this->_debug('found cookie: ' . $cookie['name'] . ' = ' . $cookie['value']);
                        }
                        else
                        {
                            $this->_debug('did not find cookie in ' . trim($arr[1]));
                        }
                    }
                }
                else if (isset($header_name))
                {
                    // append continuation line to previous header
                    $this->incomingHeaders[$header_name] .= $lb . ' ' . $header_line;
                }
            }
        }

        $this->responseStatusLine = $header_array[0];
        $arr = explode(' ', $this->responseStatusLine, 3);
        $http_version = $arr[0];
        $http_status = intval($arr[1]);
        $http_reason = count($arr) > 2 ? $arr[2] : '';

        // see if we need to resend the request with http digest authentication
        if (isset($this->incomingHeaders['location']) && ($http_status == 301 || $http_status == 302))
        {
            $this->_debug("Got $http_status $http_reason with Location: " . $this->incomingHeaders['location']);
            $this->_setURL($this->incomingHeaders['location']);
            $this->tryagain = true;
            return false;
        }

        // see if we need to resend the request with http digest authentication
        if (isset($this->incomingHeaders['www-authenticate']) && $http_status == 401)
        {
            $this->_debug("Got 401 $http_reason with WWW-Authenticate: " . $this->incomingHeaders['www-authenticate']);
            if (strstr($this->incomingHeaders['www-authenticate'], "Digest "))
            {
                $this->_debug('Server wants digest authentication');
                // remove "Digest " from our elements
                $digestString = str_replace('Digest ', '', $this->incomingHeaders['www-authenticate']);
                
                // parse elements into array
                $digestElements = explode(',', $digestString);
                foreach ($digestElements as $val)
                {
                    $tempElement = explode('=', trim($val), 2);
                    $digestRequest[$tempElement[0]] = str_replace("\"", '', $tempElement[1]);
                }

                // should have (at least) qop, realm, nonce
                if (isset($digestRequest['nonce']))
                {
                    $this->setCredentials($this->username, $this->password, 'digest', $digestRequest);
                    $this->tryagain = true;
                    return false;
                }
            }
            $this->_debug('HTTP authentication failed');
            $this->_setError('HTTP authentication failed');
            return false;
        }
        
        if (($http_status >= 300 && $http_status <= 307) ||
            ($http_status >= 400 && $http_status <= 417) ||
            ($http_status >= 501 && $http_status <= 505))
        {
            $this->_setError("Unsupported HTTP response status $http_status $http_reason (soapclient->response has contents of the response)");
            return false;
        }

        // decode content-encoding
        if (isset($this->incomingHeaders['content-encoding']) && $this->incomingHeaders['content-encoding'] != '')
        {
            if (strtolower($this->incomingHeaders['content-encoding']) == 'deflate' || strtolower($this->incomingHeaders['content-encoding']) == 'gzip')
            {
                // if decoding works, use it. else assume data wasn't gzencoded
                if (function_exists('gzinflate'))
                {
                    // IIS 5 requires gzinflate instead of gzuncompress (similar to IE 5 and gzdeflate v. gzcompress)
                    // this means there are no Zlib headers, although there should be
                    $this->_debug('The gzinflate function exists');
                    $datalen = strlen($data);
                    if ($this->incomingHeaders['content-encoding'] == 'deflate')
                    {
                        if ($degzdata = @gzinflate($data))
                        {
                            $data = $degzdata;
                            $this->_debug('The payload has been inflated to ' . strlen($data) . ' bytes');
                            if (strlen($data) < $datalen)
                            {
                                // test for the case that the payload has been compressed twice
                                $this->_debug('The inflated payload is smaller than the gzipped one; try again');
                                if ($degzdata = @gzinflate($data))
                                {
                                    $data = $degzdata;
                                    $this->_debug('The payload has been inflated again to ' . strlen($data) . ' bytes');
                                }
                            }
                        }
                        else
                        {
                            $this->_debug('Error using gzinflate to inflate the payload');
                            $this->_setError('Error using gzinflate to inflate the payload');
                        }
                    }
                    else if ($this->incomingHeaders['content-encoding'] == 'gzip')
                    {
                        if ($degzdata = @gzinflate(substr($data, 10))) // do our best
                        {
                            $data = $degzdata;
                            $this->_debug('The payload has been un-gzipped to ' . strlen($data) . ' bytes');
                            if (strlen($data) < $datalen)
                            {
                                // test for the case that the payload has been compressed twice
                                $this->_debug('The un-gzipped payload is smaller than the gzipped one; try again');
                                if ($degzdata = @gzinflate(substr($data, 10))) {
                                    $data = $degzdata;
                                    $this->_debug('The payload has been un-gzipped again to ' . strlen($data) . ' bytes');
                                }
                            }
                        }
                        else
                        {
                            $this->_debug('Error using gzinflate to un-gzip the payload');
                            $this->_setError('Error using gzinflate to un-gzip the payload');
                        }
                    }
                    
                    // set decoded payload
                    $this->incomingPayload = $header_data.$lb.$lb.$data;
                }
                else
                {
                    $this->_debug('The server sent compressed data. Your php install must have the Zlib extension compiled in to support this.');
                    $this->_setError('The server sent compressed data. Your php install must have the Zlib extension compiled in to support this.');
                }
            }
            else
            {
                $this->_debug('Unsupported Content-Encoding ' . $this->incomingHeaders['content-encoding']);
                $this->_setError('Unsupported Content-Encoding ' . $this->incomingHeaders['content-encoding']);
            }
        }
        else
        {
            $this->_debug('No Content-Encoding header');
        }
        
        if (strlen($data) == 0)
        {
            $this->_debug('no data after headers!');
            $this->_setError('no data present after HTTP headers');
            return false;
        }
        
        return $data;
    }

    /**
     * sets the content-type for the SOAP message to be sent
     *
     * @param   string $type the content type, MIME style
     * @param   mixed $charset character set used for encoding (or false)
     * @access  public
     */
    function setContentType($type, $charset = false)
    {
        $this->_setHeader('Content-Type', $type . ($charset ? '; charset=' . $charset : ''));
    }

    /**
     * specifies that an HTTP persistent connection should be used
     *
     * @return  boolean whether the request was honored by this method.
     * @access  public
     */
    function usePersistentConnection()
    {
        if (isset($this->outgoingHeaders['Accept-Encoding']))
        {
            return false;
        }
        $this->protocolVersion = '1.1';
        $this->persistentConnection = true;
        $this->_setHeader('Connection', 'Keep-Alive');
        return true;
    }

    /**
     * parse an incoming Cookie into it's parts
     *
     * @param   string $cookie_str content of cookie
     * @return  array with data of that cookie
     * @access  private
     */
    /*
     * TODO: allow a Set-Cookie string to be parsed into multiple cookies
     */
    function parseCookie($cookie_str)
    {
        $cookie_str = str_replace('; ', ';', $cookie_str) . ';';
        $data = preg_split('/;/', $cookie_str);
        $value_str = $data[0];

        $cookie_param = 'domain=';
        $start = strpos($cookie_str, $cookie_param);
        if ($start > 0)
        {
            $domain = substr($cookie_str, $start + strlen($cookie_param));
            $domain = substr($domain, 0, strpos($domain, ';'));
        }
        else
        {
            $domain = '';
        }

        $cookie_param = 'expires=';
        $start = strpos($cookie_str, $cookie_param);
        if ($start > 0)
        {
            $expires = substr($cookie_str, $start + strlen($cookie_param));
            $expires = substr($expires, 0, strpos($expires, ';'));
        }
        else
        {
            $expires = '';
        }

        $cookie_param = 'path=';
        $start = strpos($cookie_str, $cookie_param);
        if ( $start > 0 )
        {
            $path = substr($cookie_str, $start + strlen($cookie_param));
            $path = substr($path, 0, strpos($path, ';'));
        }
        else
        {
            $path = '/';
        }
                        
        $cookie_param = ';secure;';
        
        if (strpos($cookie_str, $cookie_param) !== FALSE)
        {
            $secure = true;
        }
        else
        {
            $secure = false;
        }

        $sep_pos = strpos($value_str, '=');

        if ($sep_pos)
        {
            $name = substr($value_str, 0, $sep_pos);
            $value = substr($value_str, $sep_pos + 1);
            $cookie= array( 'name' => $name,
                            'value' => $value,
                            'domain' => $domain,
                            'path' => $path,
                            'expires' => $expires,
                            'secure' => $secure
                            );      
            return $cookie;
        }
        return false;
    }
  
    /**
     * sort out cookies for the current request
     *
     * @param   array $cookies array with all cookies
     * @param   boolean $secure is the send-content secure or not?
     * @return  string for Cookie-HTTP-Header
     * @access  protected
     */
    protected function _getCookiesForRequest($cookies, $secure = false)
    {
        $cookie_str = '';
        if ($cookies !== null && is_array($cookies))
        {
            foreach ($cookies as $cookie)
            {
                if (! is_array($cookie))
                {
                    continue;
                }
                $this->_debug("check cookie for validity: ".$cookie['name'].'='.$cookie['value']);
                if (isset($cookie['expires']) && !empty($cookie['expires']))
                {
                    if (strtotime($cookie['expires']) <= time())
                    {
                        $this->_debug('cookie has expired');
                        continue;
                    }
                }

                if (isset($cookie['domain']) && !empty($cookie['domain']))
                {
                    $domain = preg_quote($cookie['domain']);
                    if (! preg_match("'.*$domain$'i", $this->host))
                    {
                        $this->_debug('cookie has different domain');
                        continue;
                    }
                }

                if (isset($cookie['path']) && !empty($cookie['path']))
                {
                    $path = preg_quote($cookie['path']);
                    if (! preg_match("'^$path.*'i", $this->path))
                    {
                        $this->_debug('cookie is for a different path');
                        continue;
                    }
                }
                if (!$secure && isset($cookie['secure']) && $cookie['secure'])
                {
                    $this->_debug('cookie is secure, transport is not');
                    continue;
                }
                $cookie_str .= $cookie['name'] . '=' . $cookie['value'] . '; ';
                $this->_debug('add cookie to Cookie-String: ' . $cookie['name'] . '=' . $cookie['value']);
            }
        }
        return $cookie_str;
    }
}
