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

date_default_timezone_set(@date_default_timezone_get());

class Base
{
    /**
     * Identification for HTTP headers.
     *
     * @var string
     * @access protected
     */
    public $title = 'NuSOAP5';
    
    /**
     * Version for HTTP headers.
     *
     * @var string
     * @access protected
     */
    public $version = '0.0.1';
    
    /**
     * Current error string (manipulated by getError/setError)
     *
     * @var string
     * @access protected
     */
    protected static $errorString = '';
    
    /**
     * Current debug string (manipulated by debug/appendDebug/clearDebug/getDebug/getDebugAsXMLComment)
     *
     * @var string
     * @access protected
     */
    protected static $debugString = '';
    
    /**
     * toggles automatic encoding of special characters as entities
     * (should always be true, I think)
     *
     * @var boolean
     * @access protected
     */
    protected static $charEncoding = true;
    
    /**
     * the debug level for this instance
     *
     * @var integer
     * @access protected
     */
    protected static $debugLevel;

    /**
     * Global debug state
     * @var boolean
     */
    protected static $debug = false;

    /**
    * set schema version
    *
    * @var      string
    * @access   public
    */
    public $XMLSchemaVersion = 'http://www.w3.org/2001/XMLSchema';
    
    /**
    * charset encoding for outgoing messages
    *
    * @var      string
    * @access   public
    */
    //public $soapDefEncoding = 'ISO-8859-1';
    public $soapDefEncoding = 'UTF-8';

    /**
    * namespaces in an array of prefix => uri
    *
    * this is "seeded" by a set of constants, but it may be altered by code
    *
    * @var      array
    * @access   public
    */
    public $namespaces = array(
        'SOAP-ENV' => 'http://schemas.xmlsoap.org/soap/envelope/',
        'xsd' => 'http://www.w3.org/2001/XMLSchema',
        'xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
        'SOAP-ENC' => 'http://schemas.xmlsoap.org/soap/encoding/'
        );

    /**
    * namespaces used in the current context, e.g. during serialization
    *
    * @var      array
    * @access   protected
    */
    protected static $usedNamespaces = array();

    /**
    * XML Schema types in an array of uri => (array of xml type => php type)
    * is this legacy yet?
    * no, this is used by the XMLSchema class to verify type => namespace mappings.
    * @var      array
    * @access   public
    */
    public $typemap = array(
        'http://www.w3.org/2001/XMLSchema' => array(
            'string'=>'string','boolean'=>'boolean','float'=>'double','double'=>'double','decimal'=>'double',
            'duration'=>'','dateTime'=>'string','time'=>'string','date'=>'string','gYearMonth'=>'',
            'gYear'=>'','gMonthDay'=>'','gDay'=>'','gMonth'=>'','hexBinary'=>'string','base64Binary'=>'string',
            // abstract "any" types
            'anyType'=>'string','anySimpleType'=>'string',
            // derived datatypes
            'normalizedString'=>'string','token'=>'string','language'=>'','NMTOKEN'=>'','NMTOKENS'=>'','Name'=>'','NCName'=>'','ID'=>'',
            'IDREF'=>'','IDREFS'=>'','ENTITY'=>'','ENTITIES'=>'','integer'=>'integer','nonPositiveInteger'=>'integer',
            'negativeInteger'=>'integer','long'=>'integer','int'=>'integer','short'=>'integer','byte'=>'integer','nonNegativeInteger'=>'integer',
            'unsignedLong'=>'','unsignedInt'=>'','unsignedShort'=>'','unsignedByte'=>'','positiveInteger'=>''),
        'http://www.w3.org/2000/10/XMLSchema' => array(
            'i4'=>'','int'=>'integer','boolean'=>'boolean','string'=>'string','double'=>'double',
            'float'=>'double','dateTime'=>'string',
            'timeInstant'=>'string','base64Binary'=>'string','base64'=>'string','ur-type'=>'array'),
        'http://www.w3.org/1999/XMLSchema' => array(
            'i4'=>'','int'=>'integer','boolean'=>'boolean','string'=>'string','double'=>'double',
            'float'=>'double','dateTime'=>'string',
            'timeInstant'=>'string','base64Binary'=>'string','base64'=>'string','ur-type'=>'array'),
        'http://soapinterop.org/xsd' => array('SOAPStruct'=>'struct'),
        'http://schemas.xmlsoap.org/soap/encoding/' => array('base64'=>'string','array'=>'array','Array'=>'array'),
        'http://xml.apache.org/xml-soap' => array('Map')
    );

    /**
    * XML entities to convert
    *
    * @var      array
    * @access   public
    * @deprecated
    * @see  expandEntities
    */
    public $xmlEntities = array('quot' => '"','amp' => '&',
        'lt' => '<','gt' => '>','apos' => "'");

    /**
     * @Constructor
     * 
     * @param integer $debugLevel
     */
    public function __construct($debugLevel = 9, $debug = false)
    {
        // require functions.php
        require_once(realpath(dirname(__FILE__)))."/functions.php";

        // Set debug level
        static::$debugLevel = $debugLevel;

        // Set debug state
        static::$debug = $debug;
    }

    /**
    * gets the global debug level, which applies to future instances
    *
    * @return   integer Debug level 0-9, where 0 turns off
    * @access   public
    */
    public function getGlobalDebugLevel()
    {
        return static::$debugLevel;
    }

    /**
    * sets the global debug level, which applies to future instances
    *
    * @param    int $level  Debug level 0-9, where 0 turns off
    * @access   public
    */
    public function setGlobalDebugLevel($level)
    {
        static::$debugLevel = $level;
    }

    /**
    * adds debug data to the instance debug string with formatting
    *
    * @param    string $string debug data
    * @access   protected
    */
    protected function debug($string)
    {
        if (static::$debugLevel > 0)
        {
            $this->appendDebug($this->getmicrotime().' '.get_class($this).": $string\n");
        }
    }

    /**
    * adds debug data to the instance debug string without formatting
    *
    * @param    string $string debug data
    * @access   public
    */
    public function appendDebug($string = "")
    {
        if (static::$debugLevel > 0)
        {
            static::$debugString .= $string;
        }
    }

    /**
    * clears the current debug data for this instance
    *
    * @access   public
    */
    public function clearDebug()
    {
        static::$debugString = "";
    }

    /**
    * gets the current debug data for this instance
    *
    * @return   debug data
    * @access   public
    */
    public function getDebug()
    {
        return static::$debugString;
    }

    /**
    * gets the current debug data for this instance as an XML comment
    * this may change the contents of the debug data
    *
    * @return   debug data as an XML comment
    * @access   public
    */
    public function getDebugAsXMLComment()
    {
        while (strpos(static::$debugString, '--'))
        {
            static::$debugString = str_replace('--', '- -', static::$debugString);
        }
        $ret = "<!--\n" . static::$debugString . "\n-->";
        return $ret;
    }

    /**
    * expands entities, e.g. changes '<' to '&lt;'.
    *
    * @param    string  $val    The string in which to expand entities.
    * @access   protected
    */
    protected function expandEntities($val)
    {
        if (static::$_charEncoding)
        {
            return htmlspecialchars($val);
        }
        return $val;
    }

    /**
    * returns error string if present
    *
    * @return   mixed error string or false
    * @access   public
    */
    public function getError()
    {
        if (static::$_errorString !== '')
        {
            return static::$_errorString;
        }
        return false;
    }

    /**
    * sets error string
    *
    * @return   boolean $string error string
    * @access   protected
    */
    protected function setError($str = "")
    {
        static::$_errorString = $str;
    }

    /**
    * detect if array is a simple array or a struct (associative array)
    *
    * @param    array   $val    The PHP array
    * @return   string  (arraySimple|arrayStruct)
    * @access   protected
    */
    protected function isArraySimpleOrStruct(Array $val)
    {
        $keyList = array_keys($val);
        foreach ($keyList as $keyListValue)
        {
            if (!is_int($keyListValue))
            {
                return 'arrayStruct';
            }
        }
        return 'arraySimple';
    }

    /**
    * serializes PHP values in accordance w/ section 5. Type information is
    * not serialized if $use == 'literal'.
    *
    * @param    mixed   $val    The value to serialize
    * @param    string  $name   The name (local part) of the XML element
    * @param    string  $type   The XML schema type (local part) for the element
    * @param    string  $name_ns    The namespace for the name of the XML element
    * @param    string  $type_ns    The namespace for the type of the element
    * @param    array   $attributes The attributes to serialize as name=>value pairs
    * @param    string  $use    The WSDL "use" (encoded|literal)
    * @param    boolean $Val    Whether this is called from Val.
    * @return   string  The serialized element, possibly with child elements
    * @access   public
    */
    public function serializeVal(
        $val,
        $name = false,
        $type = false,
        $name_ns = false,
        $type_ns = false,
        $attributes = false,
        $use = 'encoded',
        $Val = false)
    {
        $this->debug("in serializeVal: name={$name}, type={$type}, name_ns={$name_ns}, type_ns={$type_ns}, use={$use}, Val={$Val}");
        $this->appendDebug('value=' . $this->varDump($val));
        $this->appendDebug('attributes=' . $this->varDump($attributes));
        
        if (is_object($val) && get_class($val) === 'Val' && !$Val)
        {
            $this->debug("serializeVal: serialize Val");
            $xml = $val->serialize($use);
            $this->appendDebug($val->getDebug());
            $val->clearDebug();
            $this->debug("serializeVal of Val returning $xml");
            return $xml;
        }
        // force valid name if necessary
        if (is_numeric($name))
        {
            $name = '__numeric_' . $name;
        }
        else if (! $name)
        {
            $name = 'noname';
        }
        // if name has ns, add ns prefix to name
        $xmlns = '';
        if ($name_ns)
        {
            $prefix = 'nu'.rand(1000,9999);
            $name = $prefix.':'.$name;
            $xmlns .= " xmlns:$prefix=\"$name_ns\"";
        }
        // if type is prefixed, create type prefix
        if ($type_ns != '' && $type_ns === $this->namespaces['xsd'])
        {
            // need to fix this. shouldn't default to xsd if no ns specified
            // w/o checking against typemap
            $type_prefix = 'xsd';
        }
        else if ($type_ns)
        {
            $type_prefix = 'ns'.rand(1000,9999);
            $xmlns .= " xmlns:$type_prefix=\"$type_ns\"";
        }
        // serialize attributes if present
        $atts = '';
        if ($attributes)
        {
            foreach ($attributes as $k => $v)
            {
                $atts .= " $k=\"".$this->expandEntities($v).'"';
            }
        }
        // serialize null value
        if ($val === null)
        {
            $this->debug("serializeVal: serialize null");
            if ($use === 'literal')
            {
                // TODO: depends on minOccurs
                $xml = "<$name$xmlns$atts/>";
                $this->debug("serializeVal returning $xml");
                return $xml;
            }
            else
            {
                if (isset($type) && isset($type_prefix))
                {
                    $type_str = " xsi:type=\"$type_prefix:$type\"";
                }
                else
                {
                    $type_str = '';
                }
                $xml = "<$name$xmlns$type_str$atts xsi:nil=\"true\"/>";
                $this->debug("serializeVal returning $xml");
                return $xml;
            }
        }
        // serialize if an xsd built-in primitive type
        if ($type != '' && isset($this->typemap[$this->XMLSchemaVersion][$type]))
        {
            $this->debug("serializeVal: serialize xsd built-in primitive type");
            if (is_bool($val))
            {
                if ($type === 'boolean')
                {
                    $val = $val ? 'true' : 'false';
                }
                else if (! $val)
                {
                    $val = 0;
                }
            }
            else if (is_string($val))
            {
                $val = $this->expandEntities($val);
            }
            if ($use === 'literal')
            {
                $xml = "<$name$xmlns$atts>$val</$name>";
                $this->debug("serializeVal returning $xml");
                return $xml;
            }
            else
            {
                $xml = "<$name$xmlns xsi:type=\"xsd:$type\"$atts>$val</$name>";
                $this->debug("serializeVal returning $xml");
                return $xml;
            }
        }
        // detect type and serialize
        $xml = '';
        switch (true)
        {
            case (is_bool($val) || $type === 'boolean') :
                $this->debug("serializeVal: serialize boolean");
                if ($type === 'boolean')
                {
                    $val = $val ? 'true' : 'false';
                }
                else if (! $val)
                {
                    $val = 0;
                }
                if ($use === 'literal')
                {
                    $xml .= "<$name$xmlns$atts>$val</$name>";
                }
                else
                {
                    $xml .= "<$name$xmlns xsi:type=\"xsd:boolean\"$atts>$val</$name>";
                }
            break;
            
            case (is_int($val) || is_long($val) || $type === 'int') :
                $this->debug("serializeVal: serialize int");
                if ($use === 'literal')
                {
                    $xml .= "<$name$xmlns$atts>$val</$name>";
                }
                else
                {
                    $xml .= "<$name$xmlns xsi:type=\"xsd:int\"$atts>$val</$name>";
                }
            break;

            case (is_float($val)|| is_double($val) || $type === 'float') :
                $this->debug("serializeVal: serialize float");
                if ($use === 'literal')
                {
                    $xml .= "<$name$xmlns$atts>$val</$name>";
                }
                else
                {
                    $xml .= "<$name$xmlns xsi:type=\"xsd:float\"$atts>$val</$name>";
                }
            break;
            
            case (is_string($val) || $type === 'string') :
                $this->debug("serializeVal: serialize string");
                $val = $this->expandEntities($val);
                if ($use === 'literal')
                {
                    $xml .= "<$name$xmlns$atts>$val</$name>";
                }
                else
                {
                    $xml .= "<$name$xmlns xsi:type=\"xsd:string\"$atts>$val</$name>";
                }
            break;
            
            case is_object($val) :
                $this->debug("serializeVal: serialize object");
                if (get_class($val) === 'Val')
                {
                    $this->debug("serializeVal: serialize Val object");
                    $pXml = $val->serialize($use);
                    $this->appendDebug($val->getDebug());
                    $val->clearDebug();
                }
                else
                {
                    if (!$name)
                    {
                        $name = get_class($val);
                        $this->debug("In serializeVal, used class name $name as element name");
                    }
                    else
                    {
                        $this->debug("In serializeVal, do not override name $name for element name for class " . get_class($val));
                    }
                    foreach (get_object_vars($val) as $k => $v)
                    {
                        $pXml = isset($pXml) ? $pXml.$this->serializeVal($v,$k,false,false,false,false,$use) : $this->serializeVal($v,$k,false,false,false,false,$use);
                    }
                }
                if (isset($type) && isset($type_prefix))
                {
                    $type_str = " xsi:type=\"$type_prefix:$type\"";
                }
                else
                {
                    $type_str = '';
                }
                if ($use === 'literal')
                {
                    $xml .= "<$name$xmlns$atts>$pXml</$name>";
                }
                else
                {
                    $xml .= "<$name$xmlns$type_str$atts>$pXml</$name>";
                }
            break;
            
            case (is_array($val) || $type) :
                // detect if struct or array
                $valueType = $this->isArraySimpleOrStruct($val);
                if ($valueType === 'arraySimple' || preg_match('/^ArrayOf/', $type))
                {
                    $this->debug("serializeVal: serialize array");
                    $i = 0;
                    if (is_array($val) && count($val)> 0)
                    {
                        foreach ($val as $v)
                        {
                            if (is_object($v) && get_class($v) ===  'Val')
                            {
                                $tt_ns = $v->type_ns;
                                $tt = $v->type;
                            }
                            else if (is_array($v))
                            {
                                $tt = $this->isArraySimpleOrStruct($v);
                            }
                            else
                            {
                                $tt = gettype($v);
                            }
                            $array_types[$tt] = 1;
                            // TODO: for literal, the name should be $name
                            $xml .= $this->serializeVal($v,'item',false,false,false,false,$use);
                            ++$i;
                        }
                        if (count($array_types) > 1)
                        {
                            $array_typename = 'xsd:anyType';
                        }
                        else if (isset($tt) && isset($this->typemap[$this->XMLSchemaVersion][$tt]))
                        {
                            if ($tt === 'integer')
                            {
                                $tt = 'int';
                            }
                            $array_typename = 'xsd:'.$tt;
                        }
                        else if (isset($tt) && $tt === 'arraySimple')
                        {
                            $array_typename = 'SOAP-ENC:Array';
                        }
                        else if (isset($tt) && $tt === 'arrayStruct')
                        {
                            $array_typename = 'unnamed_struct_use_Val';
                        }
                        else
                        {
                            // if type is prefixed, create type prefix
                            if ($tt_ns != '' && $tt_ns === $this->namespaces['xsd'])
                            {
                                 $array_typename = 'xsd:' . $tt;
                            }
                            else if ($tt_ns) {
                                $tt_prefix = 'ns' . rand(1000, 9999);
                                $array_typename = "$tt_prefix:$tt";
                                $xmlns .= " xmlns:$tt_prefix=\"$tt_ns\"";
                            }
                            else
                            {
                                $array_typename = $tt;
                            }
                        }
                        $array_type = $i;
                        if ($use === 'literal')
                        {
                            $type_str = '';
                        }
                        else if (isset($type) && isset($type_prefix))
                        {
                            $type_str = " xsi:type=\"$type_prefix:$type\"";
                        }
                        else
                        {
                            $type_str = " xsi:type=\"SOAP-ENC:Array\" SOAP-ENC:arrayType=\"".$array_typename."[$array_type]\"";
                        }
                    // empty array
                    }
                    else
                    {
                        if ($use === 'literal')
                        {
                            $type_str = '';
                        }
                        else if (isset($type) && isset($type_prefix))
                        {
                            $type_str = " xsi:type=\"$type_prefix:$type\"";
                        }
                        else
                        {
                            $type_str = " xsi:type=\"SOAP-ENC:Array\" SOAP-ENC:arrayType=\"xsd:anyType[0]\"";
                        }
                    }
                    // TODO: for array in literal, there is no wrapper here
                    $xml = "<$name$xmlns$type_str$atts>".$xml."</$name>";
                }
                else
                {
                    // got a struct
                    $this->debug("serializeVal: serialize struct");
                    if (isset($type) && isset($type_prefix))
                    {
                        $type_str = " xsi:type=\"$type_prefix:$type\"";
                    }
                    else
                    {
                        $type_str = '';
                    }
                    if ($use === 'literal')
                    {
                        $xml .= "<$name$xmlns$atts>";
                    }
                    else
                    {
                        $xml .= "<$name$xmlns$type_str$atts>";
                    }
                    foreach ($val as $k => $v)
                    {
                        // Apache Map
                        if ($type === 'Map' && $type_ns === 'http://xml.apache.org/xml-soap')
                        {
                            $xml .= '<item>';
                            $xml .= $this->serializeVal($k,'key',false,false,false,false,$use);
                            $xml .= $this->serializeVal($v,'value',false,false,false,false,$use);
                            $xml .= '</item>';
                        }
                        else
                        {
                            $xml .= $this->serializeVal($v,$k,false,false,false,false,$use);
                        }
                    }
                    $xml .= "</$name>";
                }
            break;
            
            default:
                $this->debug("serializeVal: serialize unknown");
                $xml .= 'not detected, got '.gettype($val).' for '.$val;
            break;
        }
        $this->debug("serializeVal returning $xml");
        return $xml;
    }

    /**
    * serializes a message
    *
    * @param string $body the XML of the SOAP body
    * @param mixed $headers optional string of XML with SOAP header content, or array of Val objects for SOAP headers, or associative array
    * @param array $namespaces optional the namespaces used in generating the body and headers
    * @param string $style optional (rpc|document)
    * @param string $use optional (encoded|literal)
    * @param string $encodingStyle optional (usually 'http://schemas.xmlsoap.org/soap/encoding/' for encoded)
    * @return string the message
    * @access public
    */
    public function serializeEnvelope(
        $body,
        $headers = false,
        Array $namespaces = array(),
        $style = 'rpc',
        $use = 'encoded',
        $encodingStyle = 'http://schemas.xmlsoap.org/soap/encoding/')
    {
        if (is_string($body) && $this->soapDefEncoding === "UTF-8")
        {
            $body = utf8_encode($body);
        }

        $this->debug("In serializeEnvelope length=" . strlen($body) . " body (max 1000 characters)=" . substr($body, 0, 1000) . " style=$style use=$use encodingStyle=$encodingStyle");
        $this->debug("headers:");
        $this->appendDebug($this->varDump($headers));
        $this->debug("namespaces:");
        $this->appendDebug($this->varDump($namespaces));

        // serialize namespaces
        $ns_string = '';
        foreach (array_merge($this->namespaces,$namespaces) as $k => $v)
        {
            $ns_string .= " xmlns:$k=\"$v\"";
        }
        if ($encodingStyle)
        {
            $ns_string = " SOAP-ENV:encodingStyle=\"$encodingStyle\"$ns_string";
        }

        // serialize headers
        if ($headers)
        {
            if (is_array($headers))
            {
                $xml = '';
                foreach ($headers as $k => $v)
                {
                    if (is_object($v) && get_class($v) == 'Val')
                    {
                        $xml .= $this->serializeVal($v, false, false, false, false, false, $use);
                    }
                    else
                    {
                        $xml .= $this->serializeVal($v, $k, false, false, false, false, $use);
                    }
                }
                $headers = $xml;
                $this->debug("In serializeEnvelope, serialized array of headers to $headers");
            }
            $headers = ($this->soapDefEncoding === "UTF-8" ? utf8_encode("<SOAP-ENV:Header>".$headers."</SOAP-ENV:Header>") : "<SOAP-ENV:Header>".$headers."</SOAP-ENV:Header>");
        }
        // serialize envelope
        return
        '<?xml version="1.0" encoding="'.$this->soapDefEncoding .'"?'.">".
        '<SOAP-ENV:Envelope'.$ns_string.">".
        $headers.
        "<SOAP-ENV:Body>".
            $body.
        "</SOAP-ENV:Body>".
        "</SOAP-ENV:Envelope>";
    }

    /**
     * formats a string to be inserted into an HTML stream
     *
     * @param string $str The string to format
     * @return string The formatted string
     * @access public
     * @deprecated
     */
    public function formatDump($str)
    {
        $str = htmlspecialchars($str);
        return nl2br($str);
    }

    /**
    * contracts (changes namespace to prefix) a qualified name
    *
    * @param    string $qname qname
    * @return   string contracted qname
    * @access   protected
    */
    protected function contractQname($qname)
    {
        // get element namespace
        //$this->xdebug("Contract $qname");
        if (strrpos($qname, ':'))
        {
            // get unqualified name
            $name = substr($qname, strrpos($qname, ':') + 1);
            // get ns
            $ns = substr($qname, 0, strrpos($qname, ':'));
            $p = $this->getPrefixFromNamespace($ns);
            if ($p)
            {
                return $p . ':' . $name;
            }
            return $qname;
        }
        else
        {
            return $qname;
        }
    }

    /**
    * expands (changes prefix to namespace) a qualified name
    *
    * @param    string $qname qname
    * @return   string expanded qname
    * @access   protected
    */
    protected function expandQname($qname)
    {
        // get element prefix
        if (strpos($qname,':') && !preg_match('/^http:\/\//',$qname))
        {
            // get unqualified name
            $name = substr(strstr($qname,':'),1);
            // get ns prefix
            $prefix = substr($qname,0,strpos($qname,':'));
            if (isset($this->namespaces[$prefix]))
            {
                return $this->namespaces[$prefix].':'.$name;
            }
            else
            {
                return $qname;
            }
        }
        else
        {
            return $qname;
        }
    }

    /**
    * returns the local part of a prefixed string
    * returns the original string, if not prefixed
    *
    * @param string $str The prefixed string
    * @return string The local part
    * @access public
    */
    public function getLocalPart($str)
    {
        if ($sstr = strrchr($str,':'))
        {
            // get unqualified name
            return substr( $sstr, 1 );
        }
        else
        {
            return $str;
        }
    }

    /**
    * returns the prefix part of a prefixed string
    * returns false, if not prefixed
    *
    * @param string $str The prefixed string
    * @return mixed The prefix or false if there is no prefix
    * @access public
    */
    public function getPrefix($str)
    {
        if ($pos = strrpos($str,':'))
        {
            // get prefix
            return substr($str,0,$pos);
        }
        return false;
    }

    /**
    * pass it a prefix, it returns a namespace
    *
    * @param string $prefix The prefix
    * @return mixed The namespace, false if no namespace has the specified prefix
    * @access public
    */
    public function getNamespaceFromPrefix($prefix)
    {
        if (isset($this->namespaces[$prefix]))
        {
            return $this->namespaces[$prefix];
        }
        //$this->setError("No namespace registered for prefix '$prefix'");
        return false;
    }

    /**
    * returns the prefix for a given namespace (or prefix)
    * or false if no prefixes registered for the given namespace
    *
    * @param string $ns The namespace
    * @return mixed The prefix, false if the namespace has no prefixes
    * @access public
    */
    public function getPrefixFromNamespace($ns)
    {
        foreach ($this->namespaces as $p => $n)
        {
            if ($ns == $n || $ns == $p)
            {
                static::$_usedNamespaces[$p] = $n;
                return $p;
            }
        }
        return false;
    }

    /**
    * returns the time in ODBC canonical form with microseconds
    *
    * @return string The time in ODBC canonical form with microseconds
    * @access public
    */
    public function getmicrotime()
    {
        if (function_exists('gettimeofday'))
        {
            $tod = gettimeofday();
            $sec = $tod['sec'];
            $usec = $tod['usec'];
        }
        else
        {
            $sec = time();
            $usec = 0;
        }
        return strftime('%Y-%m-%d %H:%M:%S', $sec) . '.' . sprintf('%06d', $usec);
    }

    /**
     * Returns a string with the output of var_dump
     *
     * @param mixed $data The variable to var_dump
     * @return string The output of var_dump
     * @access public
     */
    function varDump($data)
    {
        ob_start();
        var_dump($data);
        return ob_get_clean();
    }

    /**
    * represents the object as a string
    *
    * @return   string
    * @access   public
    */
    function __toString()
    {
        return $this->varDump($this);
    }
}
