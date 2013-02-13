NuSOAP5
=======

This is a modernization effort aimed at bringing the aging NuSOAP library up to PHP 5 standards

*Version*: 0.0.2

 - Initial conversion task

What Is Working So Far
----------------------

As of version 0.0.2, basic Client functionality is in place

Basic Usage
-----------

The "__autoload.php" file must be included / required before any action can be taken.

**Client**

    require_once "__autoload.php";
    
    $client = new NuSOAP\Client("url-to-wsdl");
    $client->setCredentials("username", "password", "authtype");

    $result = $client->call("FunctionName", $params);


Initial Goals
--------------

The NuSOAP library has been extremely useful in several of my projects, but as time goes on and PHP advances, much of the core logic of this library has become either deprecated or had it's use discouraged.

This project aims to bring NuSOAP into the PHP 5 era, with a long-term goal of taking care of all of the
// TODO's that you see spread throughout the code.

The original NuSOAP project home is:

http://sourceforge.net/projects/nusoap/

### For Now ###

I intend only to modernize the library, without overly modifying functionality.

### Things I've Changed ###

 - All class files are now under the namespace NuSOAP
 - All classes have been renamed to remove the "nusoap_" appension
 - All class vars and methods now have their scope properly defined (public, protected, private)
 - The code was inconsistent in style, so I have tried to implement the Allman style (http://en.wikipedia.org/wiki/Indent_style#Allman_style)
 - Class constructors are now __construct() rather than classname
 - Unified method and variable naming within classes, removing underscores
 - No longer relies on $GLOBALS for anything
 - Relies on $_SERVER in favor of $HTTP_SERVER_VARS


**Keep in mind**

This is very much a work in progress.  There are no guarantees that it will work perfectly at any time during the 
modernization process.

**Help is welcome!**

If you wish to help out in this effort, by all means please do!  The library is massive, and any help would be welcome.
