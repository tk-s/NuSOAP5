NuSOAP5
=======

This is a modernization effort aimed at bringing the aging NuSOAP library up to PHP 5 standards

*Version*: 0.0.1

 - Initial conversion task

Initial Goals
--------------

The NuSOAP library has been extremely useful in several of my projects, but as times goes on and PHP advances, 
much of the core logic of this library has become either deprecated or had it's use discouraged.

This project aims to bring NuSOAP into the PHP 5 era, with a long-term goal of taking care of all of the
// TODO's that you see spread throughout the code.

### For Now ###

I intend only to modernize the library, without overly modifying functionality.

### Things I've Changed ###

 - All class files are now under the namespace NuSOAP
 - All classes have been renamed to remove the "nusoap_" appension
 - All class vars and methods now have their scope properly defined (public, protected, private)
 - The code was inconsistent in style, so I have tried to implement the Allman style (http://en.wikipedia.org/wiki/Indent_style#Allman_style)
 - Class constructors are now __construct() rather than classname
 - Unified method and variable naming within classes, removing underscores
 - All class vars and methods listed as @private are now defined as protected


**Keep in mind**

This is very much a work in progress.  There are no guarantees that it will work perfectly at any time during the 
modernization process.

**Help is welcome!**

If you wish to help out in this effort, by all means please do!  The library is massive, and any help would be welcome.
