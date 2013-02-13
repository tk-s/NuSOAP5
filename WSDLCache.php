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

/*
The NuSOAP project home is:
http://sourceforge.net/projects/nusoap/

The primary support for NuSOAP is the mailing list:
nusoap-general@lists.sourceforge.net
*/

/**
* caches instances of the wsdl class
* 
* @author   Scott Nichol <snichol@users.sourceforge.net>
* @author   Ingo Fischer <ingo@apollon.de>
* @author   Daniel Carbone <daniel.p.carbone@gmail.com>
* @version  $Id: class.wsdlcache.php,v 1.7 2007/04/17 16:34:03 snichol Exp $
* @access public 
*/
class WSDLCache
{
    /**
     *  @var resource
     *  @access protected
     */
    protected $_fplock;
    
    /**
     *  @var integer
     *  @access protected
     */
    protected $_cacheLifetime;
    
    /**
     *  @var string
     *  @access protected
     */
    protected $_cacheDir;
    
    /**
     *  @var string
     *  @access public
     */
    public $debugString = '';

    /**
    * constructor
    *
    * @param string $_cacheDir directory for cache-files
    * @param integer $_cacheLifetime lifetime for caching-files in seconds or 0 for unlimited
    * @access public
    */
    function __construct($_cacheDir = '.', $_cacheLifetime = 0)
    {
        $this->_fplock = array();
        $this->_cacheDir = $_cacheDir != '' ? $_cacheDir : '.';
        $this->_cacheLifetime = $_cacheLifetime;
    }

    /**
    * creates the filename used to cache a wsdl instance
    *
    * @param string $wsdl The URL of the wsdl instance
    * @return string The filename used to cache the instance
    * @access protected
    */
    protected function _createFilename($wsdl)
    {
        return $this->_cacheDir.'/wsdlcache-' . md5($wsdl);
    }

    /**
    * adds debug data to the class level debug string
    *
    * @param    string $string debug data
    * @access   protected
    */
    protected function _debug($string)
    {
        $this->debugString .= get_class($this).": $string\n";
    }

    /**
    * gets a wsdl instance from the cache
    *
    * @param string $wsdl The URL of the wsdl instance
    * @return object wsdl The cached wsdl instance, null if the instance is not in the cache
    * @access public
    */
    public function get($wsdl) 
    {
        $filename = $this->_createFilename($wsdl);
        if ($this->_obtainMutex($filename, "r"))
        {
            // check for expired WSDL that must be removed from the cache
            if ($this->_cacheLifetime > 0)
            {
                if (file_exists($filename) && (time() - filemtime($filename) > $this->_cacheLifetime))
                {
                    unlink($filename);
                    $this->_debug("Expired $wsdl ($filename) from cache");
                    $this->releaseMutex($filename);
                    return null;
                }
            }
            // see what there is to return
            if (!file_exists($filename))
            {
                $this->_debug("$wsdl ($filename) not in cache (1)");
                $this->releaseMutex($filename);
                return null;
            }
            $fp = @fopen($filename, "r");
            if ($fp)
            {
                $s = implode("", @file($filename));
                fclose($fp);
                $this->_debug("Got $wsdl ($filename) from cache");
            }
            else
            {
                $s = null;
                $this->_debug("$wsdl ($filename) not in cache (2)");
            }
            $this->releaseMutex($filename);
            return (!is_null($s)) ? unserialize($s) : null;
        }
        else
        {
            $this->_debug("Unable to obtain mutex for $filename in get");
        }
        return null;
    }

    /**
    * obtains the local mutex
    *
    * @param string $filename The Filename of the Cache to lock
    * @param string $mode The open-mode ("r" or "w") or the file - affects lock-mode
    * @return boolean Lock successfully obtained ?!
    * @access protected
    */
    protected function _obtainMutex($filename, $mode)
    {
        if (isset($this->_fplock[md5($filename)]))
        {
            $this->_debug("Lock for $filename already exists");
            return false;
        }
        $this->_fplock[md5($filename)] = fopen($filename.".lock", "w");
        if ($mode == "r")
        {
            return flock($this->_fplock[md5($filename)], LOCK_SH);
        }
        else
        {
            return flock($this->_fplock[md5($filename)], LOCK_EX);
        }
    }

    /**
    * adds a wsdl instance to the cache
    *
    * @param object wsdl $wsdl_instance The wsdl instance to add
    * @return boolean WSDL successfully cached
    * @access public
    */
    public function put($wsdl_instance)
    {
        $filename = $this->_createFilename($wsdl_instance->wsdl);
        $s = serialize($wsdl_instance);
        if ($this->_obtainMutex($filename, "w"))
        {
            $fp = fopen($filename, "w");
            if (! $fp)
            {
                $this->_debug("Cannot write $wsdl_instance->wsdl ($filename) in cache");
                $this->releaseMutex($filename);
                return false;
            }
            fputs($fp, $s);
            fclose($fp);
            $this->_debug("Put $wsdl_instance->wsdl ($filename) in cache");
            $this->releaseMutex($filename);
            return true;
        }
        else
        {
            $this->_debug("Unable to obtain mutex for $filename in put");
        }
        return false;
    }

    /**
    * releases the local mutex
    *
    * @param string $filename The Filename of the Cache to lock
    * @return boolean Lock successfully released
    * @access protected
    */
    protected function releaseMutex($filename)
    {
        $ret = flock($this->_fplock[md5($filename)], LOCK_UN);
        fclose($this->_fplock[md5($filename)]);
        unset($this->_fplock[md5($filename)]);
        if (! $ret)
        {
            $this->_debug("Not able to release lock for $filename");
        }
        return $ret;
    }

    /**
    * removes a wsdl instance from the cache
    *
    * @param string $wsdl The URL of the wsdl instance
    * @return boolean Whether there was an instance to remove
    * @access public
    */
    public function remove($wsdl)
    {
        $filename = $this->_createFilename($wsdl);
        if (!file_exists($filename))
        {
            $this->_debug("$wsdl ($filename) not in cache to be removed");
            return false;
        }
        // ignore errors obtaining mutex
        $this->_obtainMutex($filename, "w");
        $ret = unlink($filename);
        $this->_debug("Removed ($ret) $wsdl ($filename) from cache");
        $this->releaseMutex($filename);
        return $ret;
    }
}
