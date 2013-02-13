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

class Parser extends Base
{
    /**
     * [$xml description]
     * @var string
     */
    public $xml = '';

    /**
     * [$xmlEncoding description]
     * @var string
     */
    public $xmlEncoding = '';

    /**
     * [$method description]
     * @var string
     */
    public $method = '';

    /**
     * [$rootStruct description]
     * @var string
     */
    public $rootStruct = '';

    /**
     * [$rootStructName description]
     * @var string
     */
    public $rootStructName = '';

    /**
     * [$rootStructNamespace description]
     * @var string
     */
    public $rootStructNamespace = '';
    
    /**
     * [$rootHeader description]
     * @var string
     */
    public $rootHeader = '';
    
    /**
     * incoming SOAP body (text)
     * @var string
     */
    public $document = '';
    
    // determines where in the message we are (envelope,header,body,method)
    /**
     * [$status description]
     * @var string
     */
    public $status = '';
    
    /**
     * [$position description]
     * @var integer
     */
    public $position = 0;
    
    /**
     * [$depth description]
     * @var integer
     */
    public $depth = 0;
    
    /**
     * [$depthArray description]
     * @var array
     */
    public $depthArray = array();

    /**
     * [$message description]
     * @var array
     */
    public $message = array();
    
    /**
     * [$parent description]
     * @var string
     */
    public $parent = '';
    
    /**
     * parsed SOAP Body
     * @var [type]
     */
    public $soapResponse = NULL;

    /**
     * parsed SOAP Header
     * @var [type]
     */
    public $soapHeader = NULL;
    
    /**
     * incoming SOAP headers (text)
     * @var string
     */
    
    /**
     * [$bodyPosition description]
     * @var integer
     */
    public $bodyPosition = 0;
    
    /**
     * for multiref parsing
     * array of id => pos
     * @var array
     */
    public $ids = array();

    /**
     * array of id => hrefs => pos
     * @var array
     */
    public $multiRefs = array();

    /**
     * toggle for auto-decoding element content
     * @var boolean
     */
    public $decodeUTF8 = true;

    /**
     * instance of xml_parser
     * @var [type]
     */
    protected $parser = null;

    /**
    * constructor that actually does the parsing
    *
    * @param    string $xml SOAP message
    * @param    string $encoding character encoding scheme of message
    * @param    string $method method for which XML is parsed (unused?)
    * @param    string $decodeUTF8 whether to decode UTF-8 to ISO-8859-1
    * @access   public
    */
    function __construct($xml, $encoding='UTF-8', $method='', $decodeUTF8 = true)
    {
        parent::__construct();
        $this->xml = $xml;
        $this->xmlEncoding = $encoding;
        $this->method = $method;
        $this->decodeUTF8 = $decodeUTF8;

        // Check whether content has been read.
        if (!empty($xml))
        {
            // Check XML encoding
            $pos_xml = strpos($xml, '<?xml');
            if ($pos_xml !== FALSE)
            {
                $xml_decl = substr($xml, $pos_xml, strpos($xml, '?>', $pos_xml + 2) - $pos_xml + 1);
                if (preg_match("/encoding=[\"']([^\"']*)[\"']/", $xml_decl, $res))
                {
                    $xmlEncoding = $res[1];
                    if (strtoupper($xmlEncoding) != $encoding)
                    {
                        $err = "Charset from HTTP Content-Type '" . $encoding . "' does not match encoding from XML declaration '" . $xmlEncoding . "'";
                        $this->debug($err);
                        if ($encoding != 'ISO-8859-1' || strtoupper($xmlEncoding) != 'UTF-8')
                        {
                            $this->setError($err);
                            return;
                        }
                        // when HTTP says ISO-8859-1 (the default) and XML says UTF-8 (the typical), assume the other endpoint is just sloppy and proceed
                    }
                    else
                    {
                        $this->debug('Charset from HTTP Content-Type matches encoding from XML declaration');
                    }
                }
                else
                {
                    $this->debug('No encoding specified in XML declaration');
                }
            }
            else
            {
                $this->debug('No XML declaration');
            }
            $this->debug('Entering nusoap_parser(), length='.strlen($xml).', encoding='.$encoding);
            // Create an XML parser - why not xml_parser_create_ns?
            $this->parser = xml_parser_create($this->xmlEncoding);
            // Set the options for parsing the XML data.
            //xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
            xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
            xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, $this->xmlEncoding);
            // Set the object for the parser.
            xml_set_object($this->parser, $this);
            // Set the element handlers for the parser.
            xml_set_element_handler($this->parser, 'start_element','end_element');
            xml_set_character_data_handler($this->parser,'character_data');

            // Parse the XML file.
            if (!xml_parse($this->parser,$xml,true))
            {
                // Display an error message.
                $err = sprintf('XML error parsing SOAP payload on line %d: %s',
                xml_get_current_line_number($this->parser),
                xml_error_string(xml_get_error_code($this->parser)));
                $this->debug($err);
                $this->debug("XML payload:\n" . $xml);
                $this->setError($err);
            }
            else
            {
                $this->debug('in nusoap_parser ctor, message:');
                $this->appendDebug($this->varDump($this->message));
                $this->debug('parsed successfully, found root struct: '.$this->rootStruct.' of name '.$this->rootStructName);
                // get final value
                $this->soapResponse = $this->message[$this->rootStruct]['result'];
                // get header value
                if ($this->rootHeader != '' && isset($this->message[$this->rootHeader]['result']))
                {
                    $this->soapHeader = $this->message[$this->rootHeader]['result'];
                }
                // resolve hrefs/ids
                if (sizeof($this->multiRefs) > 0)
                {
                    foreach ($this->multiRefs as $id => $hrefs)
                    {
                        $this->debug('resolving multiRefs for id: '.$id);
                        $idVal = $this->buildVal($this->ids[$id]);
                        if (is_array($idVal) && isset($idVal['!id']))
                        {
                            unset($idVal['!id']);
                        }
                        foreach ($hrefs as $refPos => $ref)
                        {
                            $this->debug('resolving href at pos '.$refPos);
                            $this->multiRefs[$id][$refPos] = $idVal;
                        }
                    }
                }
            }
            xml_parser_free($this->parser);
        }
        else
        {
            $this->debug('xml was empty, didn\'t parse!');
            $this->setError('xml was empty, didn\'t parse!');
        }
    }

    /**
    * start-element handler
    *
    * @param    resource $parser XML parser object
    * @param    string $name element name
    * @param    array $attrs associative array of attributes
    * @access   private
    */
    protected function startElement($parser, $name, Array $attrs)
    {
        // position in a total number of elements, starting from 0
        // update class level pos
        $pos = $this->position++;
        // and set mine
        $this->message[$pos] = array('pos' => $pos,'children'=>'','cdata'=>'');
        // depth = how many levels removed from root?
        // set mine as current global depth and increment global depth value
        $this->message[$pos]['depth'] = $this->depth++;

        // else add self as child to whoever the current parent is
        if ($pos != 0)
        {
            $this->message[$this->parent]['children'] .= '|'.$pos;
        }
        
        // set my parent
        $this->message[$pos]['parent'] = $this->parent;
        
        // set self as current parent
        $this->parent = $pos;
        
        // set self as current value for this depth
        $this->depthArray[$this->depth] = $pos;
        
        // get element prefix
        if (strpos($name,':'))
        {
            // get ns prefix
            $prefix = substr($name,0,strpos($name,':'));
            // get unqualified name
            $name = substr(strstr($name,':'),1);
        }
        
        // set status
        if ($name == 'Envelope' && $this->status == '')
        {
            $this->status = 'envelope';
        }
        else if ($name == 'Header' && $this->status == 'envelope')
        {
            $this->rootHeader = $pos;
            $this->status = 'header';
        }
        else if ($name == 'Body' && $this->status == 'envelope')
        {
            $this->status = 'body';
            $this->bodyPosition = $pos;
        // set method
        }
        else if ($this->status == 'body' && $pos == ($this->bodyPosition+1))
        {
            $this->status = 'method';
            $this->rootStructName = $name;
            $this->rootStruct = $pos;
            $this->message[$pos]['type'] = 'struct';
            $this->debug("found root struct $this->rootStructName, pos $this->rootStruct");
        }
        // set my status
        $this->message[$pos]['status'] = $this->status;
        // set name
        $this->message[$pos]['name'] = htmlspecialchars($name);
        // set attrs
        $this->message[$pos]['attrs'] = $attrs;

        // loop through atts, logging ns and type declarations
        $attstr = '';
        foreach ($attrs as $key => $value)
        {
            $key_prefix = $this->getPrefix($key);
            $key_localpart = $this->getLocalPart($key);
            // if ns declarations, add to class level array of valid namespaces
            if ($key_prefix == 'xmlns')
            {
                if (preg_match('/^http:\/\/www.w3.org\/[0-9]{4}\/XMLSchema$/', $value))
                {
                    $this->XMLSchemaVersion = $value;
                    $this->namespaces['xsd'] = $this->XMLSchemaVersion;
                    $this->namespaces['xsi'] = $this->XMLSchemaVersion.'-instance';
                }
                $this->namespaces[$key_localpart] = $value;
                
                // set method namespace
                if ($name == $this->rootStructName)
                {
                    $this->methodNamespace = $value;
                }
            // if it's a type declaration, set type
        }
        else if ($key_localpart == 'type')
        {
                if (isset($this->message[$pos]['type']) && $this->message[$pos]['type'] == 'array')
                {
                    // do nothing: already processed arrayType
                }
                else
                {
                    $value_prefix = $this->getPrefix($value);
                    $value_localpart = $this->getLocalPart($value);
                    $this->message[$pos]['type'] = $value_localpart;
                    $this->message[$pos]['typePrefix'] = $value_prefix;
                    if (isset($this->namespaces[$value_prefix]))
                    {
                        $this->message[$pos]['type_namespace'] = $this->namespaces[$value_prefix];
                    }
                    else if (isset($attrs['xmlns:'.$value_prefix]))
                    {
                        $this->message[$pos]['type_namespace'] = $attrs['xmlns:'.$value_prefix];
                    }
                    // should do something here with the namespace of specified type?
                }
            }
            else if ($key_localpart == 'arrayType')
            {
                $this->message[$pos]['type'] = 'array';
                /* do arrayType ereg here
                [1]    arrayTypeValue    ::=    atype asize
                [2]    atype    ::=    QName rank*
                [3]    rank    ::=    '[' (',')* ']'
                [4]    asize    ::=    '[' length~ ']'
                [5]    length    ::=    nextDimension* Digit+
                [6]    nextDimension    ::=    Digit+ ','
                */
                $expr = '/([A-Za-z0-9_]+):([A-Za-z]+[A-Za-z0-9_]+)\[([0-9]+),?([0-9]*)\]/';
                if (preg_match($expr,$value,$regs))
                {
                    $this->message[$pos]['typePrefix'] = $regs[1];
                    $this->message[$pos]['arrayTypePrefix'] = $regs[1];
                    if (isset($this->namespaces[$regs[1]]))
                    {
                        $this->message[$pos]['arrayTypeNamespace'] = $this->namespaces[$regs[1]];
                    }
                    else if (isset($attrs['xmlns:'.$regs[1]]))
                    {
                        $this->message[$pos]['arrayTypeNamespace'] = $attrs['xmlns:'.$regs[1]];
                    }
                    $this->message[$pos]['arrayType'] = $regs[2];
                    $this->message[$pos]['arraySize'] = $regs[3];
                    $this->message[$pos]['arrayCols'] = $regs[4];
                }
            // specifies nil value (or not)
            }
            else if ($key_localpart == 'nil')
            {
                $this->message[$pos]['nil'] = ($value == 'true' || $value == '1');
            // some other attribute
            }
            else if ($key != 'href' && $key != 'xmlns' && $key_localpart != 'encodingStyle' && $key_localpart != 'root')
            {
                $this->message[$pos]['xattrs']['!' . $key] = $value;
            }

            if ($key == 'xmlns')
            {
                $this->defaultNamespace = $value;
            }
            // log id
            if ($key == 'id')
            {
                $this->ids[$value] = $pos;
            }
            // root
            if ($key_localpart == 'root' && $value == 1)
            {
                $this->status = 'method';
                $this->rootStructName = $name;
                $this->rootStruct = $pos;
                $this->debug("found root struct $this->rootStructName, pos $pos");
            }
            // for doclit
            $attstr .= " $key=\"$value\"";
        }
        // get namespace - must be done after namespace atts are processed
        if (isset($prefix))
        {
            $this->message[$pos]['namespace'] = $this->namespaces[$prefix];
            $this->defaultNamespace = $this->namespaces[$prefix];
        }
        else
        {
            $this->message[$pos]['namespace'] = $this->defaultNamespace;
        }
        if ($this->status == 'header')
        {
            if ($this->rootHeader != $pos)
            {
                $this->responseHeaders .= "<" . (isset($prefix) ? $prefix . ':' : '') . "$name$attstr>";
            }
        }
        else if ($this->rootStructName != '')
        {
            $this->document .= "<" . (isset($prefix) ? $prefix . ':' : '') . "$name$attstr>";
        }
    }

    /**
    * end-element handler
    *
    * @param    resource $parser XML parser object
    * @param    string $name element name
    * @access   protected
    */
    protected function endElement($parser, $name)
    {
        // position of current element is equal to the last value left in depthArray for my depth
        $pos = $this->depthArray[$this->depth--];

        // get element prefix
        if (strpos($name,':'))
        {
            // get ns prefix
            $prefix = substr($name,0,strpos($name,':'));
            // get unqualified name
            $name = substr(strstr($name,':'),1);
        }
        
        // build to native type
        if (isset($this->bodyPosition) && $pos > $this->bodyPosition)
        {
            // deal w/ multiRefs
            if (isset($this->message[$pos]['attrs']['href']))
            {
                // get id
                $id = substr($this->message[$pos]['attrs']['href'],1);
                // add placeholder to href array
                $this->multiRefs[$id][$pos] = 'placeholder';
                // add set a reference to it as the result value
                $this->message[$pos]['result'] =& $this->multiRefs[$id][$pos];
            // build complexType values
            }
            else if ($this->message[$pos]['children'] != '')
            {
                // if result has already been generated (struct/array)
                if (!isset($this->message[$pos]['result']))
                {
                    $this->message[$pos]['result'] = $this->buildVal($pos);
                }
            // build complexType values of attributes and possibly simpleContent
            }
            else if (isset($this->message[$pos]['xattrs']))
            {
                if (isset($this->message[$pos]['nil']) && $this->message[$pos]['nil'])
                {
                    $this->message[$pos]['xattrs']['!'] = null;
                }
                else if (isset($this->message[$pos]['cdata']) && trim($this->message[$pos]['cdata']) != '')
                {
                    if (isset($this->message[$pos]['type']))
                    {
                        $this->message[$pos]['xattrs']['!'] = $this->decodeSimple($this->message[$pos]['cdata'], $this->message[$pos]['type'], isset($this->message[$pos]['type_namespace']) ? $this->message[$pos]['type_namespace'] : '');
                    }
                    else
                    {
                        $parent = $this->message[$pos]['parent'];
                        if (isset($this->message[$parent]['type']) && ($this->message[$parent]['type'] == 'array') && isset($this->message[$parent]['arrayType']))
                        {
                            $this->message[$pos]['xattrs']['!'] = $this->decodeSimple($this->message[$pos]['cdata'], $this->message[$parent]['arrayType'], isset($this->message[$parent]['arrayTypeNamespace']) ? $this->message[$parent]['arrayTypeNamespace'] : '');
                        }
                        else
                        {
                            $this->message[$pos]['xattrs']['!'] = $this->message[$pos]['cdata'];
                        }
                    }
                }
                $this->message[$pos]['result'] = $this->message[$pos]['xattrs'];
            // set value of simpleType (or nil complexType)
            }
            else
            {
                //$this->debug('adding data for scalar value '.$this->message[$pos]['name'].' of value '.$this->message[$pos]['cdata']);
                if (isset($this->message[$pos]['nil']) && $this->message[$pos]['nil'])
                {
                    $this->message[$pos]['xattrs']['!'] = null;
                }
                else if (isset($this->message[$pos]['type']))
                {
                    $this->message[$pos]['result'] = $this->decodeSimple($this->message[$pos]['cdata'], $this->message[$pos]['type'], isset($this->message[$pos]['type_namespace']) ? $this->message[$pos]['type_namespace'] : '');
                }
                else
                {
                    $parent = $this->message[$pos]['parent'];
                    if (isset($this->message[$parent]['type']) && ($this->message[$parent]['type'] == 'array') && isset($this->message[$parent]['arrayType']))
                    {
                        $this->message[$pos]['result'] = $this->decodeSimple($this->message[$pos]['cdata'], $this->message[$parent]['arrayType'], isset($this->message[$parent]['arrayTypeNamespace']) ? $this->message[$parent]['arrayTypeNamespace'] : '');
                    }
                    else
                    {
                        $this->message[$pos]['result'] = $this->message[$pos]['cdata'];
                    }
                }

                /* add value to parent's result, if parent is struct/array
                $parent = $this->message[$pos]['parent'];
                if ($this->message[$parent]['type'] != 'map'){
                    if (strtolower($this->message[$parent]['type']) == 'array'){
                        $this->message[$parent]['result'][] = $this->message[$pos]['result'];
                    } else {
                        $this->message[$parent]['result'][$this->message[$pos]['name']] = $this->message[$pos]['result'];
                    }
                }
                */
            }
        }
        
        // for doclit
        if ($this->status == 'header')
        {
            if ($this->rootHeader != $pos)
            {
                $this->responseHeaders .= "</" . (isset($prefix) ? $prefix . ':' : '') . "$name>";
            }
        }
        else if ($pos >= $this->rootStruct)
        {
            $this->document .= "</" . (isset($prefix) ? $prefix . ':' : '') . "$name>";
        }
        // switch status
        if ($pos == $this->rootStruct)
        {
            $this->status = 'body';
            $this->rootStructNamespace = $this->message[$pos]['namespace'];
        }
        else if ($pos == $this->rootHeader)
        {
            $this->status = 'envelope';
        }
        else if ($name == 'Body' && $this->status == 'body')
        {
            $this->status = 'envelope';
        }
        else if ($name == 'Header' && $this->status == 'header') // will never happen (?)
        {
            $this->status = 'envelope';
        }
        else if ($name == 'Envelope' && $this->status == 'envelope')
        {
            $this->status = '';
        }
        // set parent back to my parent
        $this->parent = $this->message[$pos]['parent'];
    }

    /**
    * element content handler
    *
    * @param    resource $parser XML parser object
    * @param    string $data element content
    * @access   protected
    */
    protected function characterData($parser, $data)
    {
        $pos = $this->depthArray[$this->depth];
        if ($this->xmlEncoding=='UTF-8')
        {
            // TODO: add an option to disable this for folks who want
            // raw UTF-8 that, e.g., might not map to iso-8859-1
            // TODO: this can also be handled with xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, "ISO-8859-1");
            if ($this->decodeUTF8)
            {
                $data = utf8_decode($data);
            }
        }
        $this->message[$pos]['cdata'] .= $data;
        // for doclit
        if ($this->status == 'header')
        {
            $this->responseHeaders .= $data;
        }
        else
        {
            $this->document .= $data;
        }
    }

    /**
    * get the parsed SOAP Body (NULL if there was none)
    *
    * @return   mixed
    * @access   public
    */
    function getSoapBody()
    {
        return $this->soapResponse;
    }

    /**
    * get the parsed SOAP Header (NULL if there was none)
    *
    * @return   mixed
    * @access   public
    */
    function getSoapHeader()
    {
        return $this->soapHeader;
    }

    /**
    * get the unparsed SOAP Header
    *
    * @return   string XML or empty if no Header
    * @access   public
    */
    function getHeaders()
    {
        return $this->responseHeaders;
    }

    /**
    * decodes simple types into PHP variables
    *
    * @param    string $value value to decode
    * @param    string $type XML type to decode
    * @param    string $typens XML type namespace to decode
    * @return   mixed PHP value
    * @access   protected
    */
    protected function decodeSimple($value, $type, $typens)
    {
        // TODO: use the namespace!
        if ((!isset($type)) || $type == 'string' || $type == 'long' || $type == 'unsignedLong')
        {
            return (string) $value;
        }

        if ($type == 'int' || $type == 'integer' || $type == 'short' || $type == 'byte')
        {
            return (int) $value;
        }

        if ($type == 'float' || $type == 'double' || $type == 'decimal')
        {
            return (double) $value;
        }

        if ($type == 'boolean')
        {
            if (strtolower($value) == 'false' || strtolower($value) == 'f')
            {
                return false;
            }
            return (boolean) $value;
        }

        if ($type == 'base64' || $type == 'base64Binary')
        {
            $this->debug('Decode base64 value');
            return base64_decode($value);
        }
        
        // obscure numeric types
        if ($type == 'nonPositiveInteger' || $type == 'negativeInteger'
            || $type == 'nonNegativeInteger' || $type == 'positiveInteger'
            || $type == 'unsignedInt'
            || $type == 'unsignedShort' || $type == 'unsignedByte')
        {
            return (int) $value;
        }
        // bogus: parser treats array with no elements as a simple type
        if ($type == 'array')
        {
            return array();
        }
        // everything else
        return (string) $value;
    }

    /**
    * builds response structures for compound values (arrays/structs)
    * and scalars
    *
    * @param    integer $pos position in node tree
    * @return   mixed   PHP value
    * @access   protected
    */
    protected function buildVal($pos)
    {
        if (!isset($this->message[$pos]['type']))
        {
            $this->message[$pos]['type'] = '';
        }

        $this->debug('in buildVal() for '.$this->message[$pos]['name']."(pos $pos) of type ".$this->message[$pos]['type']);
        
        // if there are children...
        if ($this->message[$pos]['children'] != '')
        {
            $this->debug('in buildVal, there are children');
            $children = explode('|',$this->message[$pos]['children']);
            array_shift($children); // knock off empty
            
            // md array
            if (isset($this->message[$pos]['arrayCols']) && $this->message[$pos]['arrayCols'] != '')
            {
                $r=0; // rowcount
                $c=0; // colcount
                foreach ($children as $child_pos)
                {
                    $this->debug("in buildVal, got an MD array element: $r, $c");
                    $params[$r][] = $this->message[$child_pos]['result'];
                    $c++;
                    if ($c == $this->message[$pos]['arrayCols'])
                    {
                        $c = 0;
                        $r++;
                    }
                }
            // array
            }
            else if (strtolower($this->message[$pos]['type']) == 'array')
            {
                $this->debug('in buildVal, adding array '.$this->message[$pos]['name']);
                foreach ($children as $child_pos)
                {
                    $params[] = &$this->message[$child_pos]['result'];
                }
            // apache Map type: java hashtable
            }
            else if ($this->message[$pos]['type'] == 'Map' && $this->message[$pos]['type_namespace'] == 'http://xml.apache.org/xml-soap')
            {
                $this->debug('in buildVal, Java Map '.$this->message[$pos]['name']);
                foreach ($children as $child_pos){
                    $kv = explode("|",$this->message[$child_pos]['children']);
                    $params[$this->message[$kv[1]]['result']] = &$this->message[$kv[2]]['result'];
                }
            // generic compound type
            //} else if ($this->message[$pos]['type'] == 'SOAPStruct' || $this->message[$pos]['type'] == 'struct') {
            }
            else
            {
                // Apache Vector type: treat as an array
                $this->debug('in buildVal, adding Java Vector or generic compound type '.$this->message[$pos]['name']);
                if ($this->message[$pos]['type'] == 'Vector' && $this->message[$pos]['type_namespace'] == 'http://xml.apache.org/xml-soap')
                {
                    $notstruct = 1;
                }
                else
                {
                    $notstruct = 0;
                }
                //
                foreach ($children as $child_pos)
                {
                    if ($notstruct)
                    {
                        $params[] = &$this->message[$child_pos]['result'];
                    }
                    else
                    {
                        if (isset($params[$this->message[$child_pos]['name']]))
                        {
                            // de-serialize repeated element name into an array
                            if (!is_array($params[$this->message[$child_pos]['name']]) || !isset($params[$this->message[$child_pos]['name']][0]))
                            {
                                $params[$this->message[$child_pos]['name']] = array($params[$this->message[$child_pos]['name']]);
                            }

                            $params[$this->message[$child_pos]['name']][] = &$this->message[$child_pos]['result'];
                        }
                        else
                        {
                            $params[$this->message[$child_pos]['name']] = &$this->message[$child_pos]['result'];
                        }
                    }
                }
            }
            if (isset($this->message[$pos]['xattrs']))
            {
                $this->debug('in buildVal, handling attributes');
                foreach ($this->message[$pos]['xattrs'] as $n => $v)
                {
                    $params[$n] = $v;
                }
            }
            // handle simpleContent
            if (isset($this->message[$pos]['cdata']) && trim($this->message[$pos]['cdata']) != '')
            {
                $this->debug('in buildVal, handling simpleContent');
                if (isset($this->message[$pos]['type']))
                {
                    $params['!'] = $this->decodeSimple($this->message[$pos]['cdata'], $this->message[$pos]['type'], isset($this->message[$pos]['type_namespace']) ? $this->message[$pos]['type_namespace'] : '');
                }
                else
                {
                    $parent = $this->message[$pos]['parent'];
                    if (isset($this->message[$parent]['type']) && $this->message[$parent]['type'] == 'array' && isset($this->message[$parent]['arrayType']))
                    {
                        $params['!'] = $this->decodeSimple($this->message[$pos]['cdata'], $this->message[$parent]['arrayType'], isset($this->message[$parent]['arrayTypeNamespace']) ? $this->message[$parent]['arrayTypeNamespace'] : '');
                    }
                    else
                    {
                        $params['!'] = $this->message[$pos]['cdata'];
                    }
                }
            }
            $ret = is_array($params) ? $params : array();
            $this->debug('in buildVal, return:');
            $this->appendDebug($this->varDump($ret));
            return $ret;
        }
        else
        {
            $this->debug('in buildVal, no children, building scalar');
            $cdata = isset($this->message[$pos]['cdata']) ? $this->message[$pos]['cdata'] : '';
            if (isset($this->message[$pos]['type']))
            {
                $ret = $this->decodeSimple($cdata, $this->message[$pos]['type'], isset($this->message[$pos]['type_namespace']) ? $this->message[$pos]['type_namespace'] : '');
                $this->debug("in buildVal, return: $ret");
                return $ret;
            }
            $parent = $this->message[$pos]['parent'];
            if (isset($this->message[$parent]['type']) && ($this->message[$parent]['type'] == 'array') && isset($this->message[$parent]['arrayType']))
            {
                $ret = $this->decodeSimple($cdata, $this->message[$parent]['arrayType'], isset($this->message[$parent]['arrayTypeNamespace']) ? $this->message[$parent]['arrayTypeNamespace'] : '');
                $this->debug("in buildVal, return: $ret");
                return $ret;
            }
            $ret = $this->message[$pos]['cdata'];
            $this->debug("in buildVal, return: $ret");
            return $ret;
        }
    }
}
