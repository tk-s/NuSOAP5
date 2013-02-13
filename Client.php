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
*
* [nu]soapclient higher level class for easy usage.
*
* usage:
*
* // instantiate client with server info
* $soapclient = new Client( string path [ ,mixed wsdl] );
*
* // call method, get results
* echo $soapclient->call( string methodname [ ,array parameters] );
*
* // bye bye client
* unset($soapclient);
*
* @author   Dietrich Ayala <dietrich@ganx4.com>
* @author   Scott Nichol <snichol@users.sourceforge.net>
* @author   Daniel Carbone <daniel.p.carbone@gmail.com>
*/
class Client extends Base
{

    /**
     * Username for HTTP authentication
     * @public string
     */
    public $username = '';
    
    /**
     * Password for HTTP authentication
     * @public string
     */
    public $password = '';
    
    /**
     * Type of HTTP authentication
     * @public string
     */
    public $authtype = '';

    /**
     * Certification for HTTP SSL authentication
     * @var array
     */
    public $certRequest = array();
    
    /**
     * SOAP headers in request (text)
     * @var boolean
     */
    public $requestHeaders = false;
    
    /**
     * SOAP headers from response (incomplete namespace resolution)
     * @var string
     */
    public $responseHeaders = '';

    /**
     * SOAP Header from response
     * @var [type]
     */
    public $responseHeader = NULL;
    
    /**
     * SOAP body response portion (incomplete namespace resolution)
     * @var string
     */
    public $document = '';
    
    /**
     * [$endPoint description]
     * @var [type]
     */
    public $endPoint;
    
    /**
     * overrides WSDL endPoint
     * @var string
     */
    public $forceEndpoint = '';
    
    /**
     * [$proxyHost description]
     * @var string
     */
    public $proxyHost = '';
    
    /**
     * [$proxyPort description]
     * @var string
     */
    public $proxyPort = '';
    
    /**
     * [$proxyUsername description]
     * @var string
     */
    public $proxyUsername = '';
    
    /**
     * [$proxyPassword description]
     * @var string
     */
    public $proxyPassword = '';
    
    /**
     * port name used in WSDL
     * @var string
     */
    public $portName = '';
    
    /**
     * character set encoding of incoming (response) messages
     * @var string
     */
    public $xml_encoding = '';
    
    /**
     * [$httpEncoding description]
     * @var boolean
     */
    public $httpEncoding = false;
    
    /**
     * HTTP connection timeout
     * @var integer
     */
    public $timeout = 0;
    
    /**
     * HTTP response timeout
     * @var integer
     */
    public $responseTimeout = 30;

    /**
     * soap|wsdl, empty for WSDL initialization error
     * @var string
     */
    public $endPointType = '';
    
    /**
     * [$persistentConnection description]
     * @var boolean
     */
    public $persistentConnection = false;
    
    /**
     * HTTP request
     * @var string
     */
    public $request = '';
    
    /**
     * HTTP response
     * @var string
     */
    public $response = '';
    
    /**
     * SOAP payload of response
     * @var string
     */
    public $responseData = '';
    
    /**
     * Cookies from response or for request
     * @var array
     */
    public $cookies = array();
    
    /**
     * toggles whether the parser decodes element content with utf8_decode()
     * @var boolean
     */
    public $decode_utf8 = true;
    
    /**
     * WSDL operations, empty for WSDL initialization error
     * @var array
     */
    public $operations = array();
    
    /**
     * User-specified cURL options
     * @var array
     */
    public $curlOptions = array();
    
    /**
     * WSDL operation binding type
     * @var string
     */
    public $bindingType = '';
    
    /**
     * whether to always try to use cURL
     * @var boolean
     */
    public $useCurl = false;

    /**
     * NuSOAP\Fault object
     * @var [type]
     */
    public $fault = null;

    /**
    * constructor
    *
    * @param    mixed $endPoint SOAP server or WSDL URL (string), or wsdl instance (object)
    * @param    mixed $wsdl optional, set to 'wsdl' or true if using WSDL
    * @param    string $proxyHost optional
    * @param    string $proxyPort optional
    * @param    string $proxyUsername optional
    * @param    string $proxyPassword optional
    * @param    integer $timeout set the connection timeout
    * @param    integer $responseTimeout set the response timeout
    * @param    string $portName optional portName in WSDL document
    * @access   public
    */
    public function __construct(
        $endPoint,
        $wsdl = false,
        $proxyHost = false,
        $proxyPort = false,
        $proxyUsername = false,
        $proxyPassword = false,
        $timeout = 0,
        $responseTimeout = 30,
        $portName = '')
    {
        parent::__construct();
        $this->endPoint = $endPoint;
        $this->proxyHost = $proxyHost;
        $this->proxyPort = $proxyPort;
        $this->proxyUsername = $proxyUsername;
        $this->proxyPassword = $proxyPassword;
        $this->timeout = $timeout;
        $this->responseTimeout = $responseTimeout;
        $this->portName = $portName;

        $this->debug("ctor wsdl=$wsdl timeout=$timeout responseTimeout=$responseTimeout");
        $this->appendDebug('endPoint=' . $this->varDump($endPoint));

        // make values
        if ($wsdl)
        {
            if (is_object($endPoint) && (get_class($endPoint) == 'wsdl'))
            {
                $this->wsdl = $endPoint;
                $this->endPoint = $this->wsdl->wsdl;
                $this->wsdlFile = $this->endPoint;
                $this->debug('existing wsdl instance created from ' . $this->endPoint);
                $this->checkWSDL();
            }
            else
            {
                $this->wsdlFile = $this->endPoint;
                $this->wsdl = null;
                $this->debug('will use lazy evaluation of wsdl from ' . $this->endPoint);
            }
            $this->endPointType = 'wsdl';
        }
        else
        {
            $this->debug("instantiate SOAP with endPoint at $endPoint");
            $this->endPointType = 'soap';
        }
    }

    /**
    * calls method, returns PHP native type
    *
    * @param    string $operation SOAP server URL or path
    * @param    mixed $params An array, associative or simple, of the parameters
    *                         for the method call, or a string that is the XML
    *                         for the call.  For rpc style, this call will
    *                         wrap the XML in a tag named after the method, as
    *                         well as the SOAP Envelope and Body.  For document
    *                         style, this will only wrap with the Envelope and Body.
    *                         IMPORTANT: when using an array with document style,
    *                         in which case there
    *                         is really one parameter, the root of the fragment
    *                         used in the call, which encloses what programmers
    *                         normally think of parameters.  A parameter array
    *                         *must* include the wrapper.
    * @param    string $namespace optional method namespace (WSDL can override)
    * @param    string $soapAction optional SOAPAction value (WSDL can override)
    * @param    mixed $headers optional string of XML with SOAP header content, or array of soapval objects for SOAP headers, or associative array
    * @param    boolean $rpcParams optional (no longer used)
    * @param    string  $style optional (rpc|document) the style to use when serializing parameters (WSDL can override)
    * @param    string  $use optional (encoded|literal) the use when serializing parameters (WSDL can override)
    * @return   mixed   response from SOAP call, normally an associative array mirroring the structure of the XML response, false for certain fatal errors
    * @access   public
    */
    public function call(
        $operation,
        $params = array(),
        $namespace = 'http://tempuri.org',
        $soapAction = '',
        $headers = false,
        $rpcParams = null,
        $style = 'rpc',
        $use = 'encoded')
    {
        $this->operation = $operation;
        $this->fault = false;
        $this->setError('');
        $this->request = '';
        $this->response = '';
        $this->responseData = '';
        $this->faultstring = '';
        $this->faultcode = ''; 
        $this->opData = array();
        
        $this->debug("call: operation=$operation, namespace=$namespace, soapAction=$soapAction, rpcParams=$rpcParams, style=$style, use=$use, endPointType=$this->endPointType");
        $this->appendDebug('params=' . $this->varDump($params));
        $this->appendDebug('headers=' . $this->varDump($headers));
        if ($headers)
        {
            $this->requestHeaders = $headers;
        }
        if ($this->endPointType == 'wsdl' && is_null($this->wsdl))
        {
            $this->loadWSDL();
            if ($this->getError())
            {
                return false;
            }
        }
        // serialize parameters
        if ($this->endPointType == 'wsdl' && $opData = $this->getOperationData($operation))
        {
            // use WSDL for operation
            $this->opData = $opData;
            $this->debug("found operation");
            $this->appendDebug('opData=' . $this->varDump($opData));
            if (isset($opData['soapAction']))
            {
                $soapAction = $opData['soapAction'];
            }
            if (! $this->forceEndpoint)
            {
                $this->endPoint = $opData['endpoint'];
            }
            else
            {
                $this->endPoint = $this->forceEndpoint;
            }
            $namespace = isset($opData['input']['namespace']) ? $opData['input']['namespace'] : $namespace;
            $style = $opData['style'];
            $use = $opData['input']['use'];
            // add ns to ns array
            if ($namespace != '' && !isset($this->wsdl->namespaces[$namespace]))
            {
                $nsPrefix = 'ns' . rand(1000, 9999);
                $this->wsdl->namespaces[$nsPrefix] = $namespace;
            }
            $nsPrefix = $this->wsdl->getPrefixFromNamespace($namespace);
            
            // serialize payload
            if (is_string($params))
            {
                $this->debug("serializing param string for WSDL operation $operation");
                $payload = $params;
            }
            else if (is_array($params))
            {
                $this->debug("serializing param array for WSDL operation $operation");
                $payload = $this->wsdl->serializeRPCParameters($operation,'input',$params,$this->bindingType);
            }
            else
            {
                $this->debug('params must be array or string');
                $this->setError('params must be array or string');
                return false;
            }
            $usedNamespaces = $this->wsdl->usedNamespaces;
            if (isset($opData['input']['encodingStyle']))
            {
                $encodingStyle = $opData['input']['encodingStyle'];
            }
            else
            {
                $encodingStyle = '';
            }
            $this->appendDebug($this->wsdl->getDebug());
            $this->wsdl->clearDebug();
            if ($errstr = $this->wsdl->getError())
            {
                $this->debug('got wsdl error: '.$errstr);
                $this->setError('wsdl error: '.$errstr);
                return false;
            }
        }
        else if ($this->endPointType == 'wsdl')
        {
            // operation not in WSDL
            $this->appendDebug($this->wsdl->getDebug());
            $this->wsdl->clearDebug();
            $this->setError('operation '.$operation.' not present in WSDL.');
            $this->debug("operation '$operation' not present in WSDL.");
            return false;
        }
        else
        {
            // no WSDL
            //$this->namespaces['ns1'] = $namespace;
            $nsPrefix = 'ns' . rand(1000, 9999);
            // serialize 
            $payload = '';
            if (is_string($params))
            {
                $this->debug("serializing param string for operation $operation");
                $payload = $params;
            }
            else if (is_array($params))
            {
                $this->debug("serializing param array for operation $operation");
                foreach ($params as $k => $v)
                {
                    $payload .= $this->serialize_val($v,$k,false,false,false,false,$use);
                }
            }
            else
            {
                $this->debug('params must be array or string');
                $this->setError('params must be array or string');
                return false;
            }
            $usedNamespaces = array();
            if ($use == 'encoded')
            {
                $encodingStyle = 'http://schemas.xmlsoap.org/soap/encoding/';
            }
            else
            {
                $encodingStyle = '';
            }
        }
        // wrap RPC calls with method element
        if ($style == 'rpc')
        {
            if ($use == 'literal')
            {
                $this->debug("wrapping RPC request with literal method element");
                if ($namespace)
                {
                    // http://www.ws-i.org/Profiles/BasicProfile-1.1-2004-08-24.html R2735 says rpc/literal accessor elements should not be in a namespace
                    $payload = "<$nsPrefix:$operation xmlns:$nsPrefix=\"$namespace\">" .
                                $payload .
                                "</$nsPrefix:$operation>";
                }
                else
                {
                    $payload = "<$operation>" . $payload . "</$operation>";
                }
            }
            else
            {
                $this->debug("wrapping RPC request with encoded method element");
                if ($namespace)
                {
                    $payload = "<$nsPrefix:$operation xmlns:$nsPrefix=\"$namespace\">" .
                                $payload .
                                "</$nsPrefix:$operation>";
                }
                else
                {
                    $payload = "<$operation>" .
                                $payload .
                                "</$operation>";
                }
            }
        }
        // serialize envelope
        $soapmsg = $this->serializeEnvelope($payload,$this->requestHeaders,$usedNamespaces,$style,$use,$encodingStyle);
        $this->debug("endPoint=$this->endPoint, soapAction=$soapAction, namespace=$namespace, style=$style, use=$use, encodingStyle=$encodingStyle");
        $this->debug('SOAP message length=' . strlen($soapmsg) . ' contents (max 1000 bytes)=' . substr($soapmsg, 0, 1000));
        // send
        $return = $this->send($this->getHTTPBody($soapmsg),$soapAction,$this->timeout,$this->responseTimeout);
        if ($errstr = $this->getError())
        {
            $this->debug('Error: '.$errstr);
            return false;
        }
        else
        {
            $this->return = $return;
            $this->debug('sent message successfully and got a(n) '.gettype($return));
            $this->appendDebug('return=' . $this->varDump($return));
            
            // fault?
            if (is_array($return) && isset($return['faultcode']))
            {
                $this->debug('got fault');
                $this->setError($return['faultcode'].': '.$return['faultstring']);
                $this->fault = true;
                foreach ($return as $k => $v)
                {
                    $this->$k = $v;
                    $this->debug("$k = $v<br>");
                }
                return $return;
            }
            else if ($style == 'document')
            {
                // NOTE: if the response is defined to have multiple parts (i.e. unwrapped),
                // we are only going to return the first part here...sorry about that
                return $return;
            }
            else
            {
                // array of return values
                if (is_array($return))
                {
                    // multiple 'out' parameters, which we return wrapped up
                    // in the array
                    if (sizeof($return) > 1)
                    {
                        return $return;
                    }
                    // single 'out' parameter (normally the return value)
                    $return = array_shift($return);
                    $this->debug('return shifted value: ');
                    $this->appendDebug($this->varDump($return));
                    return $return;
                // nothing returned (ie, echoVoid)
                }
                else
                {
                    return "";
                }
            }
        }
    }

    /**
    * check WSDL passed as an instance or pulled from an endPoint
    *
    * @access   protected
    */
    protected function checkWSDL()
    {
        $this->appendDebug($this->wsdl->getDebug());
        $this->wsdl->clearDebug();
        $this->debug('_checkWSDL');
        
        // catch errors
        if ($errstr = $this->wsdl->getError())
        {
            $this->appendDebug($this->wsdl->getDebug());
            $this->wsdl->clearDebug();
            $this->debug('got wsdl error: '.$errstr);
            $this->setError('wsdl error: '.$errstr);
        }
        else if ($this->operations = $this->wsdl->getOperations($this->portName, 'soap'))
        {
            $this->appendDebug($this->wsdl->getDebug());
            $this->wsdl->clearDebug();
            $this->bindingType = 'soap';
            $this->debug('got '.count($this->operations).' operations from wsdl '.$this->wsdlFile.' for binding type '.$this->bindingType);
        }
        else if ($this->operations = $this->wsdl->getOperations($this->portName, 'soap12'))
        {
            $this->appendDebug($this->wsdl->getDebug());
            $this->wsdl->clearDebug();
            $this->bindingType = 'soap12';
            $this->debug('got '.count($this->operations).' operations from wsdl '.$this->wsdlFile.' for binding type '.$this->bindingType);
            $this->debug('**************** WARNING: SOAP 1.2 BINDING *****************');
        }
        else
        {
            $this->appendDebug($this->wsdl->getDebug());
            $this->wsdl->clearDebug();
            $this->debug('getOperations returned false');
            $this->setError('no operations defined in the WSDL document!');
        }
    }

    /**
     * instantiate wsdl object and parse wsdl file
     *
     * @access  public
     */
    public function loadWSDL()
    {
        $this->debug('instantiating wsdl class with doc: '.$this->wsdlFile);
        $this->wsdl = new wsdl('',$this->proxyHost,$this->proxyPort,$this->proxyUsername,$this->proxyPassword,$this->timeout,$this->responseTimeout,$this->curlOptions,$this->useCurl);
        $this->wsdl->setCredentials($this->username, $this->password, $this->authtype, $this->certRequest);
        $this->wsdl->fetchWSDL($this->wsdlFile);
        $this->checkWSDL();
    }

    /**
    * get available data pertaining to an operation
    *
    * @param    string $operation operation name
    * @return   array array of data pertaining to the operation
    * @access   public
    */
    public function getOperationData($operation)
    {
        if ($this->endPointType == 'wsdl' && $this->wsdl === null)
        {
            $this->loadWSDL();
            if ($this->getError())
            {
                return false;
            }
        }
        if (isset($this->operations[$operation]))
        {
            return $this->operations[$operation];
        }
        $this->debug("No data for operation: $operation");
    }

    /**
    * send the SOAP message
    *
    * Note: if the operation has multiple return values
    * the return value of this method will be an array
    * of those values.
    *
    * @param    string $msg a SOAPx4 soapmsg object
    * @param    string $soapaction SOAPAction value
    * @param    integer $timeout set connection timeout in seconds
    * @param    integer $responseTimeout set response timeout in seconds
    * @return   mixed native PHP types.
    * @access   protected
    */
    protected function send($msg, $soapaction = '', $timeout = 0, $responseTimeout = 30)
    {
        $this->checkCookies();
        // detect transport
        switch (true)
        {
            // http(s)
            case preg_match('/^http/',$this->endPoint) :
                $this->debug('transporting via HTTP');
                
                if ($this->persistentConnection == true && is_object($this->persistentConnection))
                {
                    $http =& $this->persistentConnection;
                }
                else
                {
                    $http = new TransportHTTP($this->endPoint, $this->curlOptions, $this->useCurl);
                    if ($this->persistentConnection)
                    {
                        $http->usePersistentConnection();
                    }
                }
                
                $http->setContentType($this->getHTTPContentType(), $this->getHTTPContentTypeCharset());
                $http->setSOAPAction($soapaction);
                
                if ($this->proxyHost && $this->proxyPort)
                {
                    $http->setProxy($this->proxyHost,$this->proxyPort,$this->proxyUsername,$this->proxyPassword);
                }
                if ($this->authtype != '')
                {
                    $http->setCredentials($this->username, $this->password, $this->authtype, array(), $this->certRequest);
                }
                if ($this->httpEncoding != '')
                {
                    $http->setEncoding($this->httpEncoding);
                }
                
                $this->debug('sending message, length='.strlen($msg));
                if (preg_match('/^http:/',$this->endPoint))
                {
                    $this->responseData = $http->send($msg,$timeout,$responseTimeout,$this->cookies);
                }
                else if (preg_match('/^https/',$this->endPoint))
                {
                    $this->responseData = $http->send($msg,$timeout,$responseTimeout,$this->cookies);
                }
                else
                {
                    $this->setError('no http/s in endPoint url');
                }
                
                $this->request = $http->outgoing_payload;
                $this->response = $http->incoming_payload;
                $this->appendDebug($http->getDebug());
                $this->updateCookies($http->incoming_cookies);

                // save transport object if using persistent connections
                if ($this->persistentConnection)
                {
                    $http->clearDebug();
                    if (!is_object($this->persistentConnection))
                    {
                        $this->persistentConnection = $http;
                    }
                }
                
                if ($err = $http->getError())
                {
                    $this->setError('HTTP Error: '.$err);
                    return false;
                }
                else if ($this->getError())
                {
                    return false;
                }
                else
                {
                    $this->debug('got response, length='. strlen($this->responseData).' type='.$http->incoming_headers['content-type']);
                    return $this->parseResponse($http->incoming_headers, $this->responseData);
                }
            break;

            default:
                $this->setError('no transport found, or selected transport is not yet supported!');
            return false;
            break;
        }
    }

    /**
    * processes SOAP message returned from server
    *
    * @param    array   $headers    The HTTP headers
    * @param    string  $data       unprocessed response data from server
    * @return   mixed   value of the message, decoded into a PHP type
    * @access   protected
    */
    protected function parseResponse($headers, $data)
    {
        $this->debug('Entering _parseResponse() for data of length ' . strlen($data) . ' headers:');
        $this->appendDebug($this->varDump($headers));
        if (!isset($headers['content-type']))
        {
            $this->setError('Response not of type text/xml (no content-type header)');
            return false;
        }
        
        if (!strstr($headers['content-type'], 'text/xml'))
        {
            $this->setError('Response not of type text/xml: ' . $headers['content-type']);
            return false;
        }
        
        if (strpos($headers['content-type'], '='))
        {
            $enc = str_replace('"', '', substr(strstr($headers["content-type"], '='), 1));
            $this->debug('Got response encoding: ' . $enc);
            if (preg_match('/^(ISO-8859-1|US-ASCII|UTF-8)$/i',$enc))
            {
                $this->xml_encoding = strtoupper($enc);
            }
            else
            {
                $this->xml_encoding = 'US-ASCII';
            }
        }
        else
        {
            // should be US-ASCII for HTTP 1.0 or ISO-8859-1 for HTTP 1.1
            $this->xml_encoding = 'ISO-8859-1';
        }
        
        $this->debug('Use encoding: ' . $this->xml_encoding . ' when creating nusoap_parser');
        $parser = new nusoap_parser($data,$this->xml_encoding,$this->operation,$this->decodeUTF8);
        
        // add parser debug data to our debug
        $this->appendDebug($parser->getDebug());
        
        // if parse errors
        if ($errstr = $parser->getError())
        {
            $this->setError( $errstr);
            // destroy the parser object
            unset($parser);
            return false;
        }
        else
        {
            // get SOAP headers
            $this->responseHeaders = $parser->getHeaders();
            // get SOAP headers
            $this->responseHeader = $parser->get_soapheader();
            // get decoded message
            $return = $parser->get_soapbody();
            // add document for doclit support
            $this->document = $parser->document;
            // destroy the parser object
            unset($parser);
            // return decode message
            return $return;
        }
     }

    /**
    * sets user-specified cURL options
    *
    * @param    mixed $option The cURL option (always integer?)
    * @param    mixed $value The cURL option value
    * @access   public
    */
    public function setCurlOption($option, $value)
    {
        $this->debug("setCurlOption option=$option, value=");
        $this->appendDebug($this->varDump($value));
        $this->curlOptions[$option] = $value;
    }

    /**
    * sets the SOAP endPoint, which can override WSDL
    *
    * @param    string $endPoint The endPoint URL to use, or empty string or false to prevent override
    * @access   public
    */
    public function setEndpoint($endPoint)
    {
        $this->debug("setEndpoint(\"$endPoint\")");
        $this->forceEndpoint = $endPoint;
    }

    /**
    * set the SOAP headers
    *
    * @param    mixed $headers String of XML with SOAP header content, or array of soapval objects for SOAP headers
    * @access   public
    */
    public function setHeaders($headers)
    {
        $this->debug("setHeaders headers=");
        $this->appendDebug($this->varDump($headers));
        $this->requestHeaders = $headers;
    }

    /**
    * get the SOAP response headers (namespace resolution incomplete)
    *
    * @return   string
    * @access   public
    */
    public function getHeaders()
    {
        return $this->responseHeaders;
    }

    /**
    * get the SOAP response Header (parsed)
    *
    * @return   mixed
    * @access   public
    */
    public function getHeader()
    {
        return $this->responseHeader;
    }

    /**
    * set proxy info here
    *
    * @param    string $proxyHost
    * @param    string $proxyPort
    * @param    string $proxyUsername
    * @param    string $proxyPassword
    * @access   public
    */
    public function setHTTPProxy($proxyHost, $proxyPort, $proxyUsername = '', $proxyPassword = '')
    {
        $this->proxyHost = $proxyHost;
        $this->proxyPort = $proxyPort;
        $this->proxyUsername = $proxyUsername;
        $this->proxyPassword = $proxyPassword;
    }

    /**
    * if authenticating, set user credentials here
    *
    * @param    string $username
    * @param    string $password
    * @param    string $authtype (basic|digest|certificate|ntlm)
    * @param    array $certRequest (keys must be cainfofile (optional), sslcertfile, sslkeyfile, passphrase, verifypeer (optional), verifyhost (optional): see corresponding options in cURL docs)
    * @access   public
    */
    public function setCredentials($username, $password, $authtype = 'basic', $certRequest = array())
    {
        $this->debug("setCredentials username=$username authtype=$authtype certRequest=");
        $this->appendDebug($this->varDump($certRequest));
        $this->username = $username;
        $this->password = $password;
        $this->authtype = $authtype;
        $this->certRequest = $certRequest;
    }
    
    /**
    * use HTTP encoding
    *
    * @param    string $enc HTTP encoding
    * @access   public
    */
    public function setHTTPEncoding($enc='gzip, deflate')
    {
        $this->debug("setHTTPEncoding(\"$enc\")");
        $this->httpEncoding = $enc;
    }
    
    /**
    * Set whether to try to use cURL connections if possible
    *
    * @param    boolean $use Whether to try to use cURL
    * @access   public
    */
    public function setUseCURL($use)
    {
        $this->debug("setUseCURL($use)");
        $this->useCurl = $use;
    }

    /**
    * use HTTP persistent connections if possible
    *
    * @access   public
    */
    public function useHTTPPersistentConnection()
    {
        $this->debug("useHTTPPersistentConnection");
        $this->persistentConnection = true;
    }
    
    /**
    * dynamically creates an instance of a proxy class,
    * allowing user to directly call methods from wsdl
    *
    * @return   object soap_proxy object
    * @access   public
    */
    public function getProxy()
    {
        $r = rand();
        $evalStr = $this->_getProxyClassCode($r);
        //$this->debug("proxy class: $evalStr");
        if ($this->getError())
        {
            $this->debug("Error from _getProxyClassCode, so return NULL");
            return null;
        }
        // eval the class
        eval($evalStr);
        // instantiate proxy object
        eval("\$proxy = new Proxy_$r('');");
        // transfer current wsdl data to the proxy thereby avoiding parsing the wsdl twice
        $proxy->endPointType = 'wsdl';
        $proxy->wsdlFile = $this->wsdlFile;
        $proxy->wsdl = $this->wsdl;
        $proxy->operations = $this->operations;
        $proxy->defaultRpcParams = $this->defaultRpcParams;
        // transfer other state
        $proxy->soapDefEncoding = $this->soapDefEncoding;
        $proxy->username = $this->username;
        $proxy->password = $this->password;
        $proxy->authtype = $this->authtype;
        $proxy->certRequest = $this->certRequest;
        $proxy->requestHeaders = $this->requestHeaders;
        $proxy->endPoint = $this->endPoint;
        $proxy->forceEndpoint = $this->forceEndpoint;
        $proxy->proxyHost = $this->proxyHost;
        $proxy->proxyPort = $this->proxyPort;
        $proxy->proxyUsername = $this->proxyUsername;
        $proxy->proxyPassword = $this->proxyPassword;
        $proxy->httpEncoding = $this->httpEncoding;
        $proxy->timeout = $this->timeout;
        $proxy->responseTimeout = $this->responseTimeout;
        $proxy->persistentConnection = &$this->persistentConnection;
        $proxy->decode_utf8 = $this->decodeUTF8;
        $proxy->curlOptions = $this->curlOptions;
        $proxy->bindingType = $this->bindingType;
        $proxy->useCurl = $this->useCurl;
        return $proxy;
    }

    /**
    * dynamically creates proxy class code
    *
    * @return   string PHP/NuSOAP code for the proxy class
    * @access   private
    */
    private function _getProxyClassCode($r)
    {
        $this->debug("in getProxy endPointType=$this->endPointType");
        $this->appendDebug("wsdl=" . $this->varDump($this->wsdl));
        if ($this->endPointType != 'wsdl')
        {
            $evalStr = 'A proxy can only be created for a WSDL client';
            $this->setError($evalStr);
            $evalStr = "echo \"$evalStr\";";
            return $evalStr;
        }
        if ($this->endPointType == 'wsdl' && is_null($this->wsdl))
        {
            $this->loadWSDL();
            if ($this->getError())
            {
                return "echo \"" . $this->getError() . "\";";
            }
        }
        $evalStr = '';
        foreach ($this->operations as $operation => $opData)
        {
            if ($operation != '')
            {
                // create param string and param comment string
                if (sizeof($opData['input']['parts']) > 0)
                {
                    $paramStr = '';
                    $paramArrayStr = '';
                    $paramCommentStr = '';
                    foreach ($opData['input']['parts'] as $name => $type)
                    {
                        $paramStr .= "\$$name, ";
                        $paramArrayStr .= "'$name' => \$$name, ";
                        $paramCommentStr .= "$type \$$name, ";
                    }
                    $paramStr = substr($paramStr, 0, strlen($paramStr)-2);
                    $paramArrayStr = substr($paramArrayStr, 0, strlen($paramArrayStr)-2);
                    $paramCommentStr = substr($paramCommentStr, 0, strlen($paramCommentStr)-2);
                }
                else
                {
                    $paramStr = '';
                    $paramArrayStr = '';
                    $paramCommentStr = 'void';
                }
                $opData['namespace'] = !isset($opData['namespace']) ? 'http://testuri.com' : $opData['namespace'];
                $evalStr .= "// $paramCommentStr
    function " . str_replace('.', '__', $operation) . "($paramStr) {
        \$params = array($paramArrayStr);
        return \$this->call('$operation', \$params, '".$opData['namespace']."', '".(isset($opData['soapAction']) ? $opData['soapAction'] : '')."');
    }
    ";
                unset($paramStr);
                unset($paramCommentStr);
            }
        }
        $evalStr = 'class Proxy_'.$r.' extends Client {
    '.$evalStr.'
}';
        return $evalStr;
    }

    /**
    * dynamically creates proxy class code
    *
    * @return   string PHP/NuSOAP code for the proxy class
    * @access   public
    */
    public function getProxyClassCode()
    {
        $r = rand();
        return $this->getProxyClassCode($r);
    }

    /**
    * gets the HTTP body for the current request.
    *
    * @param string $soapmsg The SOAP payload
    * @return string The HTTP body, which includes the SOAP payload
    * @access protected
    */
    protected function getHTTPBody($soapmsg)
    {
        return $soapmsg;
    }
    
    /**
    * gets the HTTP content type for the current request.
    *
    * Note: _getHTTPBody must be called before this.
    *
    * @return string the HTTP content type for the current request.
    * @access protected
    */
    protected function getHTTPContentType()
    {
        return 'text/xml';
    }
    
    /**
    * gets the HTTP content type charset for the current request.
    * returns false for non-text content types.
    *
    * Note: _getHTTPBody must be called before this.
    *
    * @return string the HTTP content type charset for the current request.
    * @access protected
    */
    protected function getHTTPContentTypeCharset()
    {
        return $this->soapDefEncoding;
    }

    /*
    * whether or not parser should decode utf8 element content
    *
    * @return   always returns true
    * @access   public
    */
    public function decodeUTF8($bool)
    {
        $this->decodeUTF8 = $bool;
        return true;
    }

    /**
     * adds a new Cookie into $this->cookies array
     *
     * @param   string $name Cookie Name
     * @param   string $value Cookie Value
     * @return  boolean if cookie-set was successful returns true, else false
     * @access  public
     */
    public function setCookie($name, $value)
    {
        if (strlen($name) == 0)
        {
            return false;
        }
        $this->cookies[] = array('name' => $name, 'value' => $value);
        return true;
    }

    /**
     * gets all Cookies
     *
     * @return   array with all internal cookies
     * @access   public
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * checks all Cookies and delete those which are expired
     *
     * @return   boolean always return true
     * @access   private
     */
    protected function checkCookies()
    {
        if (sizeof($this->cookies) == 0) {
            return true;
        }
        $this->debug('checkCookie: check ' . sizeof($this->cookies) . ' cookies');
        $curr_cookies = $this->cookies;
        $this->cookies = array();
        
        foreach ($curr_cookies as $cookie)
        {
            if (! is_array($cookie))
            {
                $this->debug('Remove cookie that is not an array');
                continue;
            }
            if ((isset($cookie['expires'])) && (! empty($cookie['expires'])))
            {
                if (strtotime($cookie['expires']) > time())
                {
                    $this->cookies[] = $cookie;
                }
                else
                {
                    $this->debug('Remove expired cookie ' . $cookie['name']);
                }
            }
            else
            {
                $this->cookies[] = $cookie;
            }
        }
        $this->debug('checkCookie: '.sizeof($this->cookies).' cookies left in array');
        return true;
    }

    /**
     * updates the current cookies with a new set
     *
     * @param   array $cookies new cookies with which to update current ones
     * @return  boolean always return true
     * @access  protected
     */
    protected function updateCookies($cookies)
    {
        if (sizeof($this->cookies) == 0)
        {
            // no existing cookies: take whatever is new
            if (sizeof($cookies) > 0)
            {
                $this->debug('Setting new cookie(s)');
                $this->cookies = $cookies;
            }
            return true;
        }
        if (sizeof($cookies) == 0)
        {
            // no new cookies: keep what we've got
            return true;
        }
        // merge
        foreach ($cookies as $newCookie)
        {
            if (!is_array($newCookie))
            {
                continue;
            }
            if (!isset($newCookie['name']) || isset($newCookie['value']))
            {
                continue;
            }
            $newName = $newCookie['name'];

            $found = false;
            for ($i = 0; $i < count($this->cookies); $i++)
            {
                $cookie = $this->cookies[$i];
                if (!is_array($cookie))
                {
                    continue;
                }
                if (!isset($cookie['name']))
                {
                    continue;
                }
                if ($newName != $cookie['name'])
                {
                    continue;
                }
                
                $newDomain = isset($newCookie['domain']) ? $newCookie['domain'] : 'NODOMAIN';
                $domain = isset($cookie['domain']) ? $cookie['domain'] : 'NODOMAIN';
                
                if ($newDomain != $domain)
                {
                    continue;
                }
                $newPath = isset($newCookie['path']) ? $newCookie['path'] : 'NOPATH';
                $path = isset($cookie['path']) ? $cookie['path'] : 'NOPATH';
                
                if ($newPath != $path)
                {
                    continue;
                }
                $this->cookies[$i] = $newCookie;
                $found = true;
                $this->debug('Update cookie ' . $newName . '=' . $newCookie['value']);
                
                break;
            }
            if (! $found)
            {
                $this->debug('Add cookie ' . $newName . '=' . $newCookie['value']);
                $this->cookies[] = $newCookie;
            }
        }
        return true;
    }
}

if (!extension_loaded('soap')) {
    /**
     *  For backwards compatiblity, define soapclient unless the PHP SOAP extension is loaded.
     */
    class soapclient extends Client {
        
    }
}
