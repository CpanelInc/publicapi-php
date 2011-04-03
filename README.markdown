# cPanel PublicAPI PHP Repository

This is the repository for the cPanel PublicAPI client written in PHP.

## What's Included
The repository contains the following items. Each of which is explained in
 further detail below:

* The cPanel PHP library
* The PublicAPI client class
* An examples directory
* The PHPUnit tests for the cPanel PHP library and PublicAPI client class

## QuickStart Example of the PublicAPI Client

This quick start example illustrates:

1. Instantiating the PublicAPI client object with a configuration array.
1. Invoking the *whm_api()* method for querying the
[XML-API::version][xmlapi_version] function
1. Getting the version string from the response object

Code:
    <?php
    
        // Include the autoloader
        require_once realpath( dirname(__FILE__) . '/Util/Autoload.php');
        
        // Make a configuration data array
        $config = array(
            'service' => array(
                'whm' => array(
                    'config'    => array(
                        'host' => '10.1.4.191',
                        'user' => 'root',
                        'password' => 'rootsecret'
                    ),
                ),
            ),
        );
        
        // Instantiate the PublicAPI client
        $cp = Cpanel_PublicAPI::getInstance($config);
        
        // Make a Whostmgr query
        $response = $cp->whm_api('version');
        
        // Print result string
        echo "WHM Version: {$response->version}\n";
        
    ?>

## The cPanel PHP Library

The cPanel PHP library is a foundation for developers to build applications and
scripts that interact with cPanel systems.
 
_Version 0.1.0 is compatible with PHP >= 5.2_
 
The library is divided into components.  The follow list itemizes a few
of the components, and their description, that are in the library:

* **Cpanel_Query**

    Abstract classes for creating objects that can query a cPanel system

* **Cpanel_Service**

    Concrete and abstract classes that represent cPanel Services like Whostmgr
    and cPanel

* **Cpanel_Parser**

    Classes that allow for encoding and decoding common string and data
    structures

* **Cpanel_Util**

    Utility files and scripts for developing with the cPanel library

* ... and others 

## The PublicAPI Client Class

PublicAPI is the moniker for cPanel's defined, client interface: A contract if
you will, that presents a language agnostic set of methods for interacting with
cPanel systems.

The PublicAPI PHP client class in this repository is a PHP implementation of
that interface.  The client supports all methods of the PublicAPI interface as
well as most of the methods available in previous PHP client classes, such as
the [XML-API client class][XML-API_github].

Examples of how to code with the PublicAPI client class can be found:

* In brief: The **QuickStart Example of the PublicAPI Client** section of this
document
* In detail: The examples directory, _Cpanel/Examples/_ within the repository

_Version 0.1.0 is compatible with PHP >= 5.2_

## Examples Directory

There are several example files located in _Cpanel/Examples/_.  Reading
_Introduction_to_PublicAPI.php_ is a good place to start.  All other examples
assume that you have read it.

Each example file is a working example if you substitute your own credentials
and host information.

In fact, you can even run the LivePHP example, *Using_the_LivePHP_Service.php*,
locally without having to download and install it respective of the LivePHP
environment.  This is possible due to a mock server that is part of the testing
apparatus. Note: this is a very, very simple mock server and is not
intended for anything other than example usage.  So please, do not implement or
rely on it.

## Unit Tests

Unit tests for the cPanel PHP library and the PublicAPI client class are
included in the _Cpanel/Tests/_ directory.  They are written for use with
[PHPUnit][PHPUnit_mainpage] >= 3.5 These test will require PHP 5.3 or
greater, as they use specific Reflection functions that allow for assertions
to be made on properties and methods whose visibility is _private_ or 
_protected_.  

[XML-API_github]: http://github.com/CpanelInc/xmlapi-php "XML-API client class on GitHub"
[xmlapi_version]: http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/DisplaycPanelWHMVersion "XML-API 'version'"
[PHPUnit_mainpage]: http://www.phpunit.de