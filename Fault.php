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
* Contains information for a SOAP fault.
* Mainly used for returning faults from deployed functions
* in a server instance.
* @author   Dietrich Ayala <dietrich@ganx4.com>
* @author   Daniel Carbone <daniel.p.carbone@gmail.com>
* @access public
*/
class Fault extends Base
{
    /**
     * The fault code (client|server)
     * @var string
     * @access protected
     */
    protected $faultCode = null;
    
    /**
     * The fault actor
     * @var string
     * @access protected
     */
    protected $faultActor = null;
    
    /**
     * The fault string, a description of the fault
     * @var string
     * @access protected
     */
    protected $faultString = null;

    /**
     * The fault detail, typically a string or array of string
     * @var mixed
     * @access protected
     */
    protected $faultDetail;

    /**
    * constructor
    *
    * @param string $faultcode (SOAP-ENV:Client | SOAP-ENV:Server)
    * @param string $faultactor only used when msg routed between multiple actors
    * @param string $faultstring human readable error message
    * @param mixed $faultdetail detail, typically a string or array of string
    */
    function __construct($faultcode, $faultactor = '', $faultstring = '', $faultdetail = '')
    {
        parent::__construct();
        $this->_faultCode = $faultcode;
        $this->_faultActor = $faultactor;
        $this->_faultString = $faultstring;
        $this->_faultDetail = $faultdetail;
    }

    /**
    * serialize a fault
    *
    * @return   string  The serialization of the fault instance.
    * @access   public
    */
    function serialize()
    {
        $ns_string = '';
        foreach ($this->namespaces as $k => $v)
        {
            $ns_string .= "\n  xmlns:$k=\"$v\"";
        }
        $return_msg =
            '<?xml version="1.0" encoding="'.$this->soapDefEncoding.'"?>'.
            '<SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"'.$ns_string.">\n".
                '<SOAP-ENV:Body>'.
                '<SOAP-ENV:Fault>'.
                    $this->serializeVal($this->_faultCode, 'faultcode').
                    $this->serializeVal($this->_faultActor, 'faultactor').
                    $this->serializeVal($this->_faultString, 'faultstring').
                    $this->serializeVal($this->_faultDetail, 'detail').
                '</SOAP-ENV:Fault>'.
                '</SOAP-ENV:Body>'.
            '</SOAP-ENV:Envelope>';
        return $return_msg;
    }
}
