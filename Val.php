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
* For creating serializable abstractions of native PHP types.  This class
* allows element name/namespace, XSD type, and XML attributes to be
* associated with a value.  This is extremely useful when WSDL is not
* used, but is also useful when WSDL is used with polymorphic types, including
* xsd:anyType and user-defined types.
*
* @author   Dietrich Ayala <dietrich@ganx4.com>
* @author   Daniel Carbone <daniel.p.carbone@gmail.com>
* @version  $Id: class.soap_val.php,v 1.11 2007/04/06 13:56:32 snichol Exp $
* @access   public
*/
class Val extends Base
{
    /**
     * The XML element name
     *
     * @var string
     * @access protected
     */
    protected $name;

    /**
     * The XML type name (string or false)
     *
     * @var mixed
     * @access protected
     */
    protected $type;

    /**
     * The PHP value
     *
     * @var mixed
     * @access protected
     */
    protected $value;

    /**
     * The XML element namespace (string or false)
     *
     * @var mixed
     * @access protected
     */
    protected $elementNS;
    /**
     * The XML type namespace (string or false)
     *
     * @var mixed
     * @access protected
     */
    protected $typeNS;

    /**
     * The XML element attributes (array or false)
     *
     * @var mixed
     * @access protected
     */
    protected $attributes;

    /**
    * constructor
    *
    * @param    string $name optional name
    * @param    mixed $type optional type name
    * @param    mixed $value optional value
    * @param    mixed $elementNS optional namespace of value
    * @param    mixed $_typeNS optional namespace of type
    * @param    mixed $attributes associative array of attributes to add to element serialization
    * @access   public
    */
    public function __construct(
        $name = 'Val',
        $type = false,
        $value = -1,
        $elementNS = false,
        $_typeNS = false,
        $attributes = false)
    {
        parent::__construct();
        $this->name = $name;
        $this->type = $type;
        $this->value = $value;
        $this->elementNS = $elementNS;
        $this->typeNS = $_typeNS;
        $this->attributes = $attributes;
    }

    /**
    * return serialized value
    *
    * @param    string $use The WSDL use value (encoded|literal)
    * @return   string XML data
    * @access   public
    */
    public function serialize($use='encoded')
    {
        return $this->serializeVal($this->value, $this->name, $this->type, $this->elementNS, $this->_typeNS, $this->attributes, $use, true);
    }

    /**
    * decodes a Val object into a PHP native type
    *
    * @return   mixed
    * @access   public
    */
    public function decode()
    {
        return $this->value;
    }
}
