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
* nusoap_server allows the user to create a SOAP server
* that is capable of receiving messages and returning responses
*
* @author   Dietrich Ayala <dietrich@ganx4.com>
* @author   Scott Nichol <snichol@users.sourceforge.net>
* @author   Daniel Carbone <daniel.p.carbone@gmail.com>
* @access   public
*/
class Server extends Base
{
    /**
     * HTTP headers of request
     * @var array
     * @access protected
     */
    protected $_headers = array();

    /**
     * HTTP request
     * @var string
     * @access protected
     */
    protected $_request = '';

    /**
     * SOAP headers from request (incomplete namespace resolution; special characters not escaped) (text)
     * @var string
     * @access public
     */
    public $requestHeaders = '';

    /**
     * SOAP Headers from request (parsed)
     * @var mixed
     * @access public
     */
    public $requestHeader = NULL;

    /**
     * SOAP body request portion (incomplete namespace resolution; special characters not escaped) (text)
     * @var string
     * @access public
     */
    public $document = '';

    /**
     * SOAP payload for request (text)
     * @var string
     * @access public
     */
    public $requestSOAP = '';

    /**
     * requested method namespace URI
     * @var string
     * @access protected
     */
    protected $methodURI = '';
    
    /**
     * name of method requested
     * @var string
     * @access protected
     */
    protected $_methodName = '';
    
    /**
     * method parameters from request
     * @var array
     * @access protected
     */
    protected $_methodParams = array();
    
    /**
     * SOAP Action from request
     * @var string
     * @access protected
     */
    protected $_SOAPAction = '';
    
    /**
     * character set encoding of incoming (request) messages
     * @var string
     * @access public
     */
    public $xmlEncoding = '';

    /**
     * toggles whether the parser decodes element content w/ utf8_decode()
     * @var boolean
     * @access public
     */
    public $decodeUTF8 = true;

    /**
     * HTTP headers of response
     * @var array
     * @access public
     */
    public $outgoingHeaders = array();

    /**
     * HTTP response
     * @var string
     * @access protected
     */
    protected $_response = '';

    /**
     * SOAP headers for response (text or array of Val or associative array)
     * @var mixed
     * @access public
     */
    public $responseHeaders = '';

    /**
     * SOAP payload for response (text)
     * @var string
     * @access protected
     */
    protected $_responseSOAP = '';
    
    /**
     * method return value to place in response
     * @var mixed
     * @access protected
     */
    protected $_methodReturn = false;

    /**
     * whether $methodreturn is a string of literal XML
     * @var boolean
     * @access public
     */
    public $methodReturnIsLiteralXML = false;

    /**
     * SOAP fault for response (or false)
     * @var mixed
     * @access protected
     */
    protected $_fault = false;

    /**
     * text indication of result (for debugging)
     * @var string
     * @access protected
     */
    protected $_result = 'successful';

    /**
     * assoc array of operations => opData; operations are added by the register()
     * method or by parsing an external WSDL definition
     * @var array
     * @access protected
     */
    protected $_operations = array();

    /**
     * wsdl instance (if one)
     * @var mixed
     * @access protected
     */
    protected $_wsdl = false;

    /**
     * URL for WSDL (if one)
     * @var mixed
     * @access protected
     */
    protected $externalWSDLURL = false;

    /**
     * whether to append debug to response as XML comment
     * @var boolean
     * @access public
     */
    public $debugFlag = false;


    /**
    * constructor
    * the optional parameter is a path to a WSDL file that you'd like to bind the server instance to.
    *
    * @param mixed $wsdl file path or URL (string), or wsdl instance (object)
    * @access   public
    */
    public function __construct($wsdl = false, $debug = false)
    {
        parent::__construct();

        $this->appendDebug($this->varDump($_SERVER));    

        if (static::$_debug)
        {
            $this->_debug("In nusoap_server, set debugFlag=$debug based on global flag");
            $this->debugFlag = static::$_debug;
        }
        else if (isset($_SERVER['QUERY_STRING']))
        {
            $qs = explode('&', $_SERVER['QUERY_STRING']);
            foreach ($qs as $v)
            {
                if (substr($v, 0, 6) == 'debug=')
                {
                    $this->_debug("In nusoap_server, set debugFlag=" . substr($v, 6) . " based on query string #1");
                    $this->debugFlag = substr($v, 6);
                }
            }
        }

        // wsdl
        if ($wsdl)
        {
            $this->_debug("In nusoap_server, WSDL is specified");
            if (is_object($wsdl) && (get_class($wsdl) == 'wsdl'))
            {
                $this->wsdl = $wsdl;
                $this->_externalWSDLURL = $this->wsdl->wsdl;
                $this->_debug('Use existing wsdl instance from ' . $this->_externalWSDLURL);
            }
            else
            {
                $this->_debug('Create wsdl from ' . $wsdl);
                $this->wsdl = new WSDL($wsdl);
                $this->_externalWSDLURL = $wsdl;
            }

            $this->appendDebug($this->wsdl->getDebug());
            $this->wsdl->clearDebug();

            if ($err = $this->wsdl->getError())
            {
                die('WSDL ERROR: '.$err);
            }
        }
    }

    /**
    * processes request and returns response
    *
    * @param    string $data usually is the value of $HTTP_RAW_POST_DATA
    * @access   public
    */
    public function service($data)
    {
        if (isset($_SERVER['REQUEST_METHOD']))
        {
            $rm = $_SERVER['REQUEST_METHOD'];
        }
        else
        {
            $rm = '';
        }

        if (isset($_SERVER['QUERY_STRING']))
        {
            $qs = $_SERVER['QUERY_STRING'];
        }
        else
        {
            $qs = '';
        }

        $this->_debug("In service, request method=$rm query string=$qs strlen(\$data)=" . strlen($data));

        if ($rm == 'POST')
        {
            $this->_debug("In service, invoke the request");
            $this->__parseRequest($data);
            if (! $this->_fault)
            {
                $this->_invokeMethod();
            }
            if (! $this->_fault)
            {
                $this->_serializeReturn();
            }
            $this->_sendResponse();
        }
        else if (preg_match('/wsdl/', $qs) )
        {
            $this->_debug("In service, this is a request for WSDL");
            if ($this->_externalWSDLURL)
            {
                if (strpos($this->_externalWSDLURL, "http://") !== false) // assume URL
                {
                    $this->_debug("In service, re-direct for WSDL");
                    header('Location: '.$this->_externalWSDLURL);
                }
                else // assume file
                {
                    $this->_debug("In service, use file passthru for WSDL");
                    header("Content-Type: text/xml\r\n");
                    $pos = strpos($this->_externalWSDLURL, "file://");
                    
                    if ($pos === false)
                    {
                        $filename = $this->_externalWSDLURL;
                    }
                    else
                    {
                        $filename = substr($this->_externalWSDLURL, $pos + 7);
                    }

                    $fp = fopen($this->_externalWSDLURL, 'r');
                    fpassthru($fp);
                }
            }
            else if ($this->wsdl)
            {
                $this->_debug("In service, serialize WSDL");
                header("Content-Type: text/xml; charset=ISO-8859-1\r\n");
                
                print $this->wsdl->serialize($this->debugFlag);
                
                if ($this->debugFlag)
                {
                    $this->_debug('wsdl:');
                    $this->appendDebug($this->varDump($this->wsdl));
                    print $this->getDebugAsXMLComment();
                }
            }
            else
            {
                $this->_debug("In service, there is no WSDL");
                header("Content-Type: text/html; charset=ISO-8859-1\r\n");
                print "This service does not provide WSDL";
            }
        }
        else if ($this->wsdl)
        {
            $this->_debug("In service, return Web description");
            print $this->wsdl->webDescription();
        }
        else
        {
            $this->_debug("In service, no Web description");
            header("Content-Type: text/html; charset=ISO-8859-1\r\n");
            print "This service does not provide a Web description";
        }
    }

    /**
    * parses HTTP request headers.
    *
    * The following fields are set by this function (when successful)
    *
    * headers
    * request
    * xml_encoding
    * SOAPAction
    *
    * @access protected
    */
    protected function _parseHTTPHeaders()
    {
        $this->_request = '';
        $this->_SOAPAction = '';
        
        if (function_exists('getallheaders'))
        {
            $this->_debug("In _parseHTTPHeaders, use getallheaders");
            $headers = getallheaders();
            foreach ($headers as $k=>$v)
            {
                $k = strtolower($k);
                $this->_headers[$k] = $v;
                $this->_request .= "$k: $v\r\n";
                $this->_debug("$k: $v");
            }
            // get SOAPAction header
            if (isset($this->_headers['soapaction']))
            {
                $this->_SOAPAction = str_replace('"','',$this->_headers['soapaction']);
            }
            // get the character encoding of the incoming request
            if (isset($this->_headers['content-type']) && strpos($this->_headers['content-type'],'='))
            {
                $enc = str_replace('"','',substr(strstr($this->_headers["content-type"],'='),1));
                
                if (preg_match('/^(ISO-8859-1|US-ASCII|UTF-8)$/i',$enc))
                {
                    $this->xmlEncoding = strtoupper($enc);
                }
                else
                {
                    $this->xmlEncoding = 'US-ASCII';
                }
            }
            else
            {
                // should be US-ASCII for HTTP 1.0 or ISO-8859-1 for HTTP 1.1
                $this->xmlEncoding = 'ISO-8859-1';
            }
        }
        else if (isset($_SERVER) && is_array($_SERVER))
        {
            $this->_debug("In _parseHTTPHeaders, use _SERVER");
            foreach ($_SERVER as $k => $v)
            {
                if (substr($k, 0, 5) == 'HTTP_')
                {
                    $k = str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($k, 5))));
                }
                else
                {
                    $k = str_replace(' ', '-', strtolower(str_replace('_', ' ', $k)));
                }
                if ($k == 'soapaction')
                {
                    // get SOAPAction header
                    $k = 'SOAPAction';
                    $v = str_replace('"', '', $v);
                    $v = str_replace('\\', '', $v);
                    $this->_SOAPAction = $v;
                }
                else if ($k == 'content-type')
                {
                    // get the character encoding of the incoming request
                    if (strpos($v, '='))
                    {
                        $enc = substr(strstr($v, '='), 1);
                        $enc = str_replace('"', '', $enc);
                        $enc = str_replace('\\', '', $enc);
                        if (preg_match('/^(ISO-8859-1|US-ASCII|UTF-8)$/i',$enc))
                        {
                            $this->xmlEncoding = strtoupper($enc);
                        }
                        else
                        {
                            $this->xmlEncoding = 'US-ASCII';
                        }
                    }
                    else
                    {
                        // should be US-ASCII for HTTP 1.0 or ISO-8859-1 for HTTP 1.1
                        $this->xmlEncoding = 'ISO-8859-1';
                    }
                }
                $this->_headers[$k] = $v;
                $this->_request .= "$k: $v\r\n";
                $this->_debug("$k: $v");
            }
        }
        else
        {
            $this->_debug("In _parseHTTPHeaders, HTTP headers not accessible");
            $this->_setError("HTTP headers not accessible");
        }
    }

    /**
    * parses a request
    *
    * The following fields are set by this function (when successful)
    *
    * headers
    * request
    * xml_encoding
    * SOAPAction
    * request
    * requestSOAP
    * methodURI
    * methodname
    * methodparams
    * requestHeaders
    * document
    *
    * This sets the fault field on error
    *
    * @param    string $data XML string
    * @access   protected
    */
    protected function __parseRequest($data = '')
    {
        $this->_debug('entering __parseRequest()');
        $this->_parseHTTPHeaders();
        $this->_debug('got character encoding: '.$this->xmlEncoding);
        // uncompress if necessary
        if (isset($this->_headers['content-encoding']) && $this->_headers['content-encoding'] != '')
        {
            $this->_debug('got content encoding: ' . $this->_headers['content-encoding']);

            if ($this->_headers['content-encoding'] == 'deflate' || $this->_headers['content-encoding'] == 'gzip')
            {
                // if decoding works, use it. else assume data wasn't gzencoded
                if (function_exists('gzuncompress'))
                {
                    if ($this->_headers['content-encoding'] == 'deflate' && $degzdata = @gzuncompress($data))
                    {
                        $data = $degzdata;
                    }
                    else if ($this->_headers['content-encoding'] == 'gzip' && $degzdata = gzinflate(substr($data, 10)))
                    {
                        $data = $degzdata;
                    }
                    else
                    {
                        $this->_fault('SOAP-ENV:Client', 'Errors occurred when trying to decode the data');
                        return;
                    }
                }
                else
                {
                    $this->_fault('SOAP-ENV:Client', 'This Server does not support compressed data');
                    return;
                }
            }
        }

        $this->_request .= "\r\n".$data;
        $data = $this->_parseRequest($this->_headers, $data);
        $this->_requestSOAP = $data;
        $this->_debug('leaving __parseRequest');
    }

    /**
    * invokes a PHP function for the requested SOAP method
    *
    * The following fields are set by this function (when successful)
    *
    * methodreturn
    *
    * Note that the PHP function that is called may also set the following
    * fields to affect the response sent to the client
    *
    * responseHeaders
    * outgoing_headers
    *
    * This sets the fault field on error
    *
    * @access protected
    */
    protected function _invokeMethod() {
        $this->_debug('in _invokeMethod, methodname=' . $this->_methodName . ' methodURI=' . $this->_methodURI . ' SOAPAction=' . $this->_SOAPAction);

        //
        // if you are debugging in this area of the code, your service uses a class to implement methods,
        // you use SOAP RPC, and the client is .NET, please be aware of the following...
        // when the .NET wsdl.exe utility generates a proxy, it will remove the '.' or '..' from the
        // method name.  that is fine for naming the .NET methods.  it is not fine for properly constructing
        // the XML request and reading the XML response.  you need to add the RequestElementName and
        // ResponseElementName to the System.Web.Services.Protocols.SoapRpcMethodAttribute that wsdl.exe
        // generates for the method.  these parameters are used to specify the correct XML element names
        // for .NET to use, i.e. the names with the '.' in them.
        //
        $orig_methodname = $this->_methodName;
        if ($this->wsdl)
        {
            if ($this->opData = $this->wsdl->getOperationData($this->_methodName))
            {
                $this->_debug('in _invokeMethod, found WSDL operation=' . $this->_methodName);
                $this->appendDebug('opData=' . $this->varDump($this->opData));
            }
            else if ($this->opData = $this->wsdl->getOperationDataForSoapAction($this->_SOAPAction))
            {
                // Note: hopefully this case will only be used for doc/lit, since rpc services should have wrapper element
                $this->_debug('in _invokeMethod, found WSDL soapAction=' . $this->_SOAPAction . ' for operation=' . $this->opData['name']);
                $this->appendDebug('opData=' . $this->varDump($this->opData));
                $this->_methodName = $this->opData['name'];
            }
            else
            {
                $this->_debug('in _invokeMethod, no WSDL for operation=' . $this->_methodName);
                $this->_fault('SOAP-ENV:Client', "Operation '" . $this->_methodName . "' is not defined in the WSDL for this service");
                return;
            }
        }
        else
        {
            $this->_debug('in _invokeMethod, no WSDL to validate method');
        }

        // if a . is present in $this->_methodName, we see if there is a class in scope,
        // which could be referred to. We will also distinguish between two deliminators,
        // to allow methods to be called a the class or an instance
        if (strpos($this->_methodName, '..') > 0)
        {
            $delim = '..';
        }
        else if (strpos($this->_methodName, '.') > 0)
        {
            $delim = '.';
        }
        else
        {
            $delim = '';
        }
        $this->_debug("in _invokeMethod, delim=$delim");

        $class = '';
        $method = '';
        if (strlen($delim) > 0 && substr_count($this->_methodName, $delim) == 1)
        {
            $try_class = substr($this->_methodName, 0, strpos($this->_methodName, $delim));
            if (class_exists($try_class))
            {
                // get the class and method name
                $class = $try_class;
                $method = substr($this->_methodName, strpos($this->_methodName, $delim) + strlen($delim));
                $this->_debug("in _invokeMethod, class=$class method=$method delim=$delim");
            }
            else
            {
                $this->_debug("in _invokeMethod, class=$try_class not found");
            }
        }
        else
        {
            $try_class = '';
            $this->_debug("in _invokeMethod, no class to try");
        }

        // does method exist?
        if ($class == '')
        {
            if (!function_exists($this->_methodName))
            {
                $this->_debug("in _invokeMethod, function '$this->_methodName' not found!");
                $this->_result = 'fault: method not found';
                $this->_fault('SOAP-ENV:Client',"method '$this->_methodName'('$orig_methodname') not defined in service('$try_class' '$delim')");
                return;
            }
        }
        else
        {
            $method_to_compare = (substr(phpversion(), 0, 2) == '4.') ? strtolower($method) : $method;
            if (!in_array($method_to_compare, get_class_methods($class)))
            {
                $this->_debug("in _invokeMethod, method '$this->_methodName' not found in class '$class'!");
                $this->_result = 'fault: method not found';
                $this->_fault('SOAP-ENV:Client',"method '$this->_methodName'/'$method_to_compare'('$orig_methodname') not defined in service/'$class'('$try_class' '$delim')");
                return;
            }
        }

        // evaluate message, getting back parameters
        // verify that request parameters match the method's signature
        if (! $this->_verifyMethod($this->_methodName,$this->_methodParams))
        {
            // debug
            $this->_debug('ERROR: request not verified against method signature');
            $this->_result = 'fault: request failed validation against method signature';
            // return fault
            $this->_fault('SOAP-ENV:Client',"Operation '$this->_methodName' not defined in service.");
            return;
        }

        // if there are parameters to pass
        $this->_debug('in _invokeMethod, params:');
        $this->appendDebug($this->varDump($this->_methodParams));
        $this->_debug("in _invokeMethod, calling '$this->_methodName'");
        

        if ($class == '')
        {
            $this->_debug('in _invokeMethod, calling function using call_user_func_array()');
            $call_arg = "$this->_methodName";    // straight assignment changes $this->_methodName to lower case after call_user_func_array()
        }
        else if ($delim == '..')
        {
            $this->_debug('in _invokeMethod, calling class method using call_user_func_array()');
            $call_arg = array ($class, $method);
        }
        else
        {
            $this->_debug('in _invokeMethod, calling instance method using call_user_func_array()');
            $instance = new $class ();
            $call_arg = array(&$instance, $method);
        }

        if (is_array($this->_methodParams))
        {
            $this->_methodReturn = call_user_func_array($call_arg, array_values($this->_methodParams));
        }
        else
        {
            $this->_methodReturn = call_user_func_array($call_arg, array());
        }

        $this->_debug('in _invokeMethod, methodreturn:');
        $this->appendDebug($this->varDump($this->_methodReturn));
        $this->_debug("in _invokeMethod, called method $this->_methodName, received data of type ".gettype($this->_methodReturn));
    }

    /**
    * serializes the return value from a PHP function into a full SOAP Envelope
    *
    * The following fields are set by this function (when successful)
    *
    * responseSOAP
    *
    * This sets the fault field on error
    *
    * @access protected
    */
    protected function _serializeReturn()
    {
        $this->_debug('Entering _serializeReturn methodname: ' . $this->_methodName . ' methodURI: ' . $this->_methodURI);
        // if fault
        if (isset($this->_methodReturn) &&
            is_object($this->_methodReturn) &&
            $this->_methodReturn instanceof Fault)
        {
            $this->_debug('got a fault object from method');
            $this->_fault = $this->_methodReturn;
            return;
        }
        else if ($this->_methodReturnisliteralxml)
        {
            $return_val = $this->_methodReturn;
        // returned value(s)
        }
        else
        {
            $this->_debug('got a(n) '.gettype($this->_methodReturn).' from method');
            $this->_debug('serializing return value');
            if ($this->wsdl)
            {
                if (sizeof($this->opData['output']['parts']) > 1)
                {
                    $this->_debug('more than one output part, so use the method return unchanged');
                    $opParams = $this->_methodReturn;
                }
                else if (sizeof($this->opData['output']['parts']) == 1)
                {
                    $this->_debug('exactly one output part, so wrap the method return in a simple array');
                    // TODO: verify that it is not already wrapped!
                    //foreach ($this->opData['output']['parts'] as $name => $type) {
                    //  $this->_debug('wrap in element named ' . $name);
                    //}
                    $opParams = array($this->_methodReturn);
                }
                $return_val = $this->wsdl->serializeRPCParameters($this->_methodName,'output',$opParams);
                $this->appendDebug($this->wsdl->getDebug());
                $this->wsdl->clearDebug();
                if ($errstr = $this->wsdl->getError())
                {
                    $this->_debug('got wsdl error: '.$errstr);
                    $this->_fault('SOAP-ENV:Server', 'unable to serialize result');
                    return;
                }
            }
            else
            {
                if (isset($this->_methodReturn))
                {
                    $return_val = $this->serializeVal($this->_methodReturn, 'return');
                }
                else
                {
                    $return_val = '';
                    $this->_debug('in absence of WSDL, assume void return for backward compatibility');
                }
            }
        }
        $this->_debug('return value:');
        $this->appendDebug($this->varDump($return_val));

        $this->_debug('serializing response');
        if ($this->wsdl)
        {
            $this->_debug('have WSDL for serialization: style is ' . $this->opData['style']);
            if ($this->opData['style'] == 'rpc')
            {
                $this->_debug('style is rpc for serialization: use is ' . $this->opData['output']['use']);
                if ($this->opData['output']['use'] == 'literal')
                {
                    // http://www.ws-i.org/Profiles/BasicProfile-1.1-2004-08-24.html R2735 says rpc/literal accessor elements should not be in a namespace
                    if ($this->_methodURI)
                    {
                        $payload = '<ns1:'.$this->_methodName.'Response xmlns:ns1="'.$this->_methodURI.'">'.$return_val.'</ns1:'.$this->_methodName."Response>";
                    }
                    else
                    {
                        $payload = '<'.$this->_methodName.'Response>'.$return_val.'</'.$this->_methodName.'Response>';
                    }
                }
                else
                {
                    if ($this->_methodURI)
                    {
                        $payload = '<ns1:'.$this->_methodName.'Response xmlns:ns1="'.$this->_methodURI.'">'.$return_val.'</ns1:'.$this->_methodName."Response>";
                    }
                    else
                    {
                        $payload = '<'.$this->_methodName.'Response>'.$return_val.'</'.$this->_methodName.'Response>';
                    }
                }
            }
            else
            {
                $this->_debug('style is not rpc for serialization: assume document');
                $payload = $return_val;
            }
        }
        else
        {
            $this->_debug('do not have WSDL for serialization: assume rpc/encoded');
            $payload = '<ns1:'.$this->_methodName.'Response xmlns:ns1="'.$this->_methodURI.'">'.$return_val.'</ns1:'.$this->_methodName."Response>";
        }
        
        $this->_result = 'successful';
        
        if ($this->wsdl)
        {
            //if ($this->debugFlag){
                $this->appendDebug($this->wsdl->getDebug());
            //  }
            if (isset($this->opData['output']['encodingStyle']))
            {
                $encodingStyle = $this->opData['output']['encodingStyle'];
            }
            else
            {
                $encodingStyle = '';
            }
            // Added: In case we use a WSDL, return a serialized env. WITH the usedNamespaces.
            $this->_responseSOAP = $this->serializeEnvelope($payload,$this->_responseHeaders,$this->wsdl->usedNamespaces,$this->opData['style'],$this->opData['output']['use'],$encodingStyle);
        }
        else
        {
            $this->_responseSOAP = $this->serializeEnvelope($payload,$this->_responseHeaders);
        }
        $this->_debug("Leaving _serializeReturn");
    }

    /**
    * sends an HTTP response
    *
    * The following fields are set by this function (when successful)
    *
    * outgoing_headers
    * response
    *
    * @access   protected
    */
    protected function _sendResponse()
    {
        $this->_debug('Enter _sendResponse');
        
        if ($this->_fault)
        {
            $payload = $this->_fault->serialize();
            $this->outgoingHeaders[] = "HTTP/1.0 500 Internal Server Error";
            $this->outgoingHeaders[] = "Status: 500 Internal Server Error";
        }
        else
        {
            $payload = $this->_responseSOAP;
            // Some combinations of PHP+Web server allow the Status
            // to come through as a header.  Since OK is the default
            // just do nothing.
            // $this->outgoingHeaders[] = "HTTP/1.0 200 OK";
            // $this->outgoingHeaders[] = "Status: 200 OK";
        }
        // add debug data if in debug mode
        if (isset($this->debugFlag) && $this->debugFlag)
        {
            $payload .= $this->getDebugAsXMLComment();
        }
        $this->outgoingHeaders[] = "Server: $this->title Server v$this->version";
        preg_match('/\$Revisio' . 'n: ([^ ]+)/', $this->revision, $rev);
        $this->outgoingHeaders[] = "X-SOAP-Server: $this->title/$this->version (".$rev[1].")";
        // Let the Web server decide about this
        //$this->outgoingHeaders[] = "Connection: Close\r\n";
        $payload = $this->_getHTTPBody($payload);
        $type = $this->_getHTTPContentType();
        $charset = $this->_getHTTPContentTypeCharset();
        $this->outgoingHeaders[] = "Content-Type: $type" . ($charset ? '; charset=' . $charset : '');
        //begin code to compress payload - by John
        // NOTE: there is no way to know whether the Web server will also compress
        // this data.
        if (strlen($payload) > 1024 && isset($this->_headers) && isset($this->_headers['accept-encoding']))
        { 
            if (strstr($this->_headers['accept-encoding'], 'gzip'))
            {
                if (function_exists('gzencode'))
                {
                    if (isset($this->debugFlag) && $this->debugFlag)
                    {
                        $payload .= "<!-- Content being gzipped -->";
                    }
                    $this->outgoingHeaders[] = "Content-Encoding: gzip";
                    $payload = gzencode($payload);
                }
                else
                {
                    if (isset($this->debugFlag) && $this->debugFlag)
                    {
                        $payload .= "<!-- Content will not be gzipped: no gzencode -->";
                    }
                }
            }
            else if (strstr($this->_headers['accept-encoding'], 'deflate'))
            {
                // Note: MSIE requires gzdeflate output (no Zlib header and checksum),
                // instead of gzcompress output,
                // which conflicts with HTTP 1.1 spec (http://www.w3.org/Protocols/rfc2616/rfc2616-sec3.html#sec3.5)
                if (function_exists('gzdeflate'))
                {
                    if (isset($this->debugFlag) && $this->debugFlag)
                    {
                        $payload .= "<!-- Content being deflated -->";
                    }
                    $this->outgoingHeaders[] = "Content-Encoding: deflate";
                    $payload = gzdeflate($payload);
                }
                else
                {
                    if (isset($this->debugFlag) && $this->debugFlag)
                    {
                        $payload .= "<!-- Content will not be deflated: no gzcompress -->";
                    }
                }
            }
        }
        //end code
        $this->outgoingHeaders[] = "Content-Length: ".strlen($payload);
        reset($this->outgoingHeaders);
        foreach ($this->outgoingHeaders as $hdr)
        {
            header($hdr, false);
        }
        print $payload;
        $this->_response = join("\r\n",$this->outgoingHeaders)."\r\n\r\n".$payload;
    }

    /**
    * takes the value that was created by parsing the request
    * and compares to the method's signature, if available.
    *
    * @param    string  $operation  The operation to be invoked
    * @param    array   $request    The array of parameter values
    * @return   boolean Whether the operation was found
    * @access   protected
    */
    protected function _verifyMethod($operation, $request)
    {
        if (isset($this->wsdl) && is_object($this->wsdl))
        {
            if ($this->wsdl->getOperationData($operation))
            {
                return true;
            }
        }
        else if (isset($this->_operations[$operation]))
        {
            return true;
        }
        return false;
    }

    /**
    * processes SOAP message received from client
    *
    * @param    array   $headers    The HTTP headers
    * @param    string  $data       unprocessed request data from client
    * @return   mixed   value of the message, decoded into a PHP type
    * @access   protected
    */
    protected function _parseRequest($headers, $data)
    {
        $this->_debug('Entering _parseRequest() for data of length ' . strlen($data) . ' headers:');
        $this->appendDebug($this->varDump($headers));
        
        if (!isset($headers['content-type']))
        {
            $this->_setError('Request not of type text/xml (no content-type header)');
            return false;
        }
        if (!strstr($headers['content-type'], 'text/xml'))
        {
            $this->_setError('Request not of type text/xml');
            return false;
        }
        if (strpos($headers['content-type'], '='))
        {
            $enc = str_replace('"', '', substr(strstr($headers["content-type"], '='), 1));
            $this->_debug('Got response encoding: ' . $enc);
            if (preg_match('/^(ISO-8859-1|US-ASCII|UTF-8)$/i',$enc))
            {
                $this->xmlEncoding = strtoupper($enc);
            }
            else
            {
                $this->xmlEncoding = 'US-ASCII';
            }
        }
        else
        {
            // should be US-ASCII for HTTP 1.0 or ISO-8859-1 for HTTP 1.1
            $this->xmlEncoding = 'ISO-8859-1';
        }
        
        $this->_debug('Use encoding: ' . $this->xmlEncoding . ' when creating Parser');
        // parse response, get soap parser obj
        $parser = new Parser($data,$this->xmlEncoding,'',$this->decodeUFT8);
        // parser debug
        $this->_debug("parser debug: \n".$parser->getDebug());
        
        // if fault occurred during message parsing
        if ($err = $parser->getError())
        {
            $this->_result = 'fault: error in msg parsing: '.$err;
            $this->_fault('SOAP-ENV:Client',"error in msg parsing:\n".$err);
        // else successfully parsed request into Val object
        }
        else
        {
            // get/set methodname
            $this->_methodURI = $parser->root_struct_namespace;
            $this->_methodName = $parser->root_struct_name;
            $this->_debug('methodname: '.$this->_methodName.' methodURI: '.$this->_methodURI);
            $this->_debug('calling parser->get_soapbody()');
            $this->_methodParams = $parser->get_soapbody();
            // get SOAP headers
            $this->_requestHeaders = $parser->getHeaders();
            // get SOAP Header
            $this->_requestHeader = $parser->get_soapheader();
            // add document for doclit support
            $this->document = $parser->document;
        }
     }

    /**
    * gets the HTTP body for the current response.
    *
    * @param string $soapmsg The SOAP payload
    * @return string The HTTP body, which includes the SOAP payload
    * @access protected
    */
    protected function _getHTTPBody($soapmsg)
    {
        return $soapmsg;
    }
    
    /**
    * gets the HTTP content type for the current response.
    *
    * Note: _getHTTPBody must be called before this.
    *
    * @return string the HTTP content type for the current response.
    * @access protected
    */
    protected function _getHTTPContentType()
    {
        return 'text/xml';
    }
    
    /**
    * gets the HTTP content type charset for the current response.
    * returns false for non-text content types.
    *
    * Note: _getHTTPBody must be called before this.
    *
    * @return string the HTTP content type charset for the current response.
    * @access protected
    */
    protected function _getHTTPContentTypeCharset()
    {
        return $this->soapDefEncoding;
    }

    /**
    * register a service function with the server
    *
    * @param    string $name the name of the PHP function, class.method or class..method
    * @param    array $in assoc array of input values: key = param name, value = param type
    * @param    array $out assoc array of output values: key = param name, value = param type
    * @param    mixed $namespace the element namespace for the method or false
    * @param    mixed $soapaction the soapaction for the method or false
    * @param    mixed $style optional (rpc|document) or false Note: when 'document' is specified, parameter and return wrappers are created for you automatically
    * @param    mixed $use optional (encoded|literal) or false
    * @param    string $documentation optional Description to include in WSDL
    * @param    string $encodingStyle optional (usually 'http://schemas.xmlsoap.org/soap/encoding/' for encoded)
    * @access   public
    */
    public function register(
        $name,
        Array $in = array(),
        Array $out = array(),
        $namespace = false,
        $soapaction = false,
        $style = false,
        $use = false,
        $documentation = '',
        $encodingStyle = '')
    {
        if ($this->_externalWSDLURL)
        {
            die('You cannot bind to an external WSDL file, and register methods outside of it! Please choose either WSDL or no WSDL.');
        }
        if (! $name)
        {
            die('You must specify a name when you register an operation');
        }
        if (!is_array($in))
        {
            die('You must provide an array for operation inputs');
        }
        if (!is_array($out)) {
            die('You must provide an array for operation outputs');
        }

        if (false == $namespace)
        {
            // uh...do stuff?
        }
        if ($soapaction == false)
        {
            $SERVER_NAME = $_SERVER['SERVER_NAME'];
            $SCRIPT_NAME = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];

            $HTTPS = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'off';

            if ($HTTPS == '1' || $HTTPS == 'on')
            {
                $SCHEME = 'https';
            }
            else
            {
                $SCHEME = 'http';
            }

            $soapaction = "$SCHEME://$SERVER_NAME$SCRIPT_NAME/$name";
        }
        if (false == $style)
        {
            $style = "rpc";
        }
        if (false == $use)
        {
            $use = "encoded";
        }
        if ($use == 'encoded' && $encodingStyle == '')
        {
            $encodingStyle = 'http://schemas.xmlsoap.org/soap/encoding/';
        }

        $this->_operations[$name] = array(
        'name' => $name,
        'in' => $in,
        'out' => $out,
        'namespace' => $namespace,
        'soapaction' => $soapaction,
        'style' => $style);
        
        if ($this->wsdl)
        {
            $this->wsdl->addOperation($name,$in,$out,$namespace,$soapaction,$style,$use,$documentation,$encodingStyle);
        }
        return true;
    }

    /**
    * Specify a fault to be returned to the client.
    * This also acts as a flag to the server that a fault has occured.
    *
    * @param    string $faultcode
    * @param    string $faultstring
    * @param    string $faultactor
    * @param    string $faultdetail
    * @access   public
    */
    public function fault($faultcode, $faultstring, $faultactor = '', $faultdetail = '')
    {
        if ($faultdetail == '' && $this->debugFlag)
        {
            $faultdetail = $this->getDebug();
        }
        $this->_fault = new Fault($faultcode, $faultactor, $faultstring, $faultdetail);
        $this->_fault->soapDefEncoding = $this->soapDefEncoding;
    }

    /**
    * Sets up wsdl object.
    * Acts as a flag to enable internal WSDL generation
    *
    * @param string $serviceName, name of the service
    * @param mixed $namespace optional 'tns' service namespace or false
    * @param mixed $endpoint optional URL of service endpoint or false
    * @param string $style optional (rpc|document) WSDL style (also specified by operation)
    * @param string $transport optional SOAP transport
    * @param mixed $schemaTargetNamespace optional 'types' targetNamespace for service schema or false
    */
    public function configureWSDL(
        $serviceName,
        $namespace = false,
        $endpoint = false,
        $style = 'rpc',
        $transport = 'http://schemas.xmlsoap.org/soap/http',
        $schemaTargetNamespace = false)
    {
        $SERVER_NAME = $_SERVER['SERVER_NAME'];
        $SERVER_PORT = $_SERVER['SERVER_PORT'];
        $SCRIPT_NAME = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $HTTPS = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'off';
        
        // If server name has port number attached then strip it (else port number gets duplicated in WSDL output) (occurred using lighttpd and FastCGI)
        $colon = strpos($SERVER_NAME,":");
        if ($colon)
        {
            $SERVER_NAME = substr($SERVER_NAME, 0, $colon);
        }
        
        if ($SERVER_PORT == 80)
        {
            $SERVER_PORT = '';
        }
        else
        {
            $SERVER_PORT = ':' . $SERVER_PORT;
        }
        
        if (false == $namespace)
        {
            $namespace = "http://$SERVER_NAME/soap/$serviceName";
        }
        
        if (false == $endpoint)
        {
            if ($HTTPS == '1' || $HTTPS == 'on')
            {
                $SCHEME = 'https';
            }
            else
            {
                $SCHEME = 'http';
            }
            $endpoint = "$SCHEME://$SERVER_NAME$SERVER_PORT$SCRIPT_NAME";
        }
        
        if (false == $schemaTargetNamespace)
        {
            $schemaTargetNamespace = $namespace;
        }
        
        $this->wsdl = new WSDL;
        $this->wsdl->serviceName = $serviceName;
        $this->wsdl->endpoint = $endpoint;
        $this->wsdl->namespaces['tns'] = $namespace;
        $this->wsdl->namespaces['soap'] = 'http://schemas.xmlsoap.org/wsdl/soap/';
        $this->wsdl->namespaces['wsdl'] = 'http://schemas.xmlsoap.org/wsdl/';
        
        if ($schemaTargetNamespace != $namespace)
        {
            $this->wsdl->namespaces['types'] = $schemaTargetNamespace;
        }
        
        $this->wsdl->schemas[$schemaTargetNamespace][0] = new XMLSchema('', '', $this->wsdl->namespaces);
        
        if ($style == 'document')
        {
            $this->wsdl->schemas[$schemaTargetNamespace][0]->schemaInfo['elementFormDefault'] = 'qualified';
        }
        $this->wsdl->schemas[$schemaTargetNamespace][0]->schemaTargetNamespace = $schemaTargetNamespace;
        $this->wsdl->schemas[$schemaTargetNamespace][0]->imports['http://schemas.xmlsoap.org/soap/encoding/'][0] = array('location' => '', 'loaded' => true);
        $this->wsdl->schemas[$schemaTargetNamespace][0]->imports['http://schemas.xmlsoap.org/wsdl/'][0] = array('location' => '', 'loaded' => true);
        $this->wsdl->bindings[$serviceName.'Binding'] = array(
            'name'=>$serviceName.'Binding',
            'style'=>$style,
            'transport'=>$transport,
            'portType'=>$serviceName.'PortType');
        $this->wsdl->ports[$serviceName.'Port'] = array(
            'binding'=>$serviceName.'Binding',
            'location'=>$endpoint,
            'bindingType'=>'http://schemas.xmlsoap.org/wsdl/soap/');
    }
}
