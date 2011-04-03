<?php
/**
 * Cpanel_Service_XmlapiClientClass
 * 
 * Copyright (c) 2011, cPanel, Inc.
 * All rights reserved.
 * http://cpanel.net
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * Neither the name of cPanel, Inc. nor the
 *      names of its contributors may be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA,
 * OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
 * EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * @category  Cpanel
 * @package   Cpanel_Service
 * @author    David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright Copyright (c) 2011, cPanel, Inc., All rights Reserved. (http://cpanel.net) 
 * @license   http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version   0.1.0
 * @link      http://sdk.cpanel.net
 * @since     0.1.0
 */
/**
 * Abstraction of the PHP XML-API client class
 * 
 * This class provides an abstraction layer for WHM and cPanel services adapters
 * such that they can support all query-based functions of the legacy XML-API
 * client class.
 *
 * @class     Cpanel_Service_XmlapiClientClass
 * @category  Cpanel
 * @package   Cpanel_Service
 * @author    David Neimeyer <david.neimeyer@cpanel.net>
 * @author    Matt Dees <matt@cpanel.net>
 * @copyright Copyright (c) 2011, cPanel, Inc., All rights Reserved. (http://cpanel.net) 
 * @license   http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version   0.1.0
 * @link      http://sdk.cpanel.net
 * @since     0.1.0
 */
abstract class Cpanel_Service_XmlapiClientClass extends Cpanel_Query_Http_Abstract
{
    /**
     * Method for querying API1 via XML-API
     * 
     * @param string $user     User to query against
     * @param string $module   API1 module to source
     * @param string $function API1 function of $module to invoke
     * @param array  $args     Arguments for $function
     * 
     * @return Cpanel_Query_Object
     */
    abstract public function api1_query($user, $module, $function, $args = array());
    /**
     * Method for querying API2 via XML-API
     * 
     * @param string $user     User to query against
     * @param string $module   API2 module to source
     * @param string $function API2 function of $module to invoke
     * @param array  $args     Arguments for $function
     * 
     * @return Cpanel_Query_Object
     */
    abstract public function api2_query($user, $module, $function, $args = array());
    /**
     * Method for querying a native XML-API function
     * 
     * @param string $function XML-API function to invoke
     * @param array  $args     Arguments for $function
     * 
     * @return Cpanel_Query_Object
     */
    abstract public function xmlapi_query($function, $args = array());
    //###
    //  XML API Functions
    //###
    
    /**
    * Return a list of available XML-API calls
    *
    * This function will return an array containing all applications available within the XML-API
    *
    * 
    * @return mixed
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/ListAvailableCalls XML API Call documentation
    */
    public function applist()
    {
        return $this->makeQuery('applist');
    }
    //###
    // Account functions
    //###
    
    /**
    * Create a cPanel Account
    *
    * This function will allow one to create an account, the $acctconf parameter requires that the follow 
    * three associations are defined:
    *   - username
    *   - password
    *   - domain
    *
    * Failure to prive these will cause an error to be logged.  Any other key/value pairs as defined by the createaccount call 
    * documentation are allowed parameters for this call.
    * 
    * @param array $acctconf
    * 
    * @return mixed
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/CreateAccount XML API Call documentation
    */
    /**
     * createacct
     * Insert description here
     *
     * @param $acctconf
     *                    
     * 
     * @return
     *         
     * @access
     * @static
     * @see   
     * @since 
     */
    public function createacct($acctconf)
    {
        if (!is_array($acctconf)) {
            throw new Exception("createacct requires that first parameter passed to it is an array");
        }
        if (!isset($acctconf['username']) || !isset($acctconf['password']) || !isset($acctconf['domain'])) {
            throw new Exception("createacct requires that username, password & domain elements are in the array passed to it");
        }
        return $this->makeQuery('createacct', $acctconf);
    }
    /**
    * Change a cPanel Account's Password
    * 
    * This function will allow you to change the password of a cpanel account
    *
    * @param string $username The username to change the password of
    * @param string $pass     The new password for the cPanel Account
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/ChangePassword XML API Call documentation
    */
    public function passwd($username, $pass)
    {
        if (!isset($username) || !isset($pass)) {
            throw new Exception("passwd requires that an username and password are passed to it");
        }
        return $this->makeQuery('passwd', array(
            'user' => $username,
            'pass' => $pass
        ));
    }
    /**
    * Limit an account's monthly bandwidth usage
    *
    * This function will set an account's bandwidth limit.
    *
    * @param string $username The username of the cPanel account to modify
    * @param int    $bwlimit  The new bandwidth limit in megabytes
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/LimitBandwidth XML API Call documentation
    */
    public function limitbw($username, $bwlimit)
    {
        if (!isset($username) || !isset($bwlimit)) {
            throw new Exception("limitbw requires that an username and bwlimit are passed to it");
        }
        return $this->makeQuery('limitbw', array(
            'user' => $username,
            'bwlimit' => $bwlimit
        ));
    }
    /**
    * List accounts on Server
    *
    * This call will return a list of account on a server, either no parameters or both parameters may be passed to this function.
    *
    * @param string $searchtype Type of account search to use, allowed values: domain, owner, user, ip or package
    * @param string $search     the string to search against
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/ListAccounts XML API Call documentation
    */
    public function listaccts($searchtype = null, $search = null)
    {
        if ($search) {
            return $this->makeQuery('listaccts', array(
                'searchtype' => $searchtype,
                'search' => $search
            ));
        }
        return $this->makeQuery('listaccts');
    }
    /**
    * Modify a cPanel account
    *
    * This call will allow you to change limitations and information about an account.  See the XML API call documentation for a list of
    * acceptable values for args.
    *
    * @param string $username The username to modify
    * @param array  $args     the new values for the modified account (see {@link http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/ModifyAccount modifyacct documentation})
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/ModifyAccount XML API Call documentation 
    */
    public function modifyacct($username, $args = array())
    {
        if (!isset($username)) {
            throw new Exception("modifyacct requires that username is passed to it");
        }
        $args['user'] = $username;
        if (sizeof($args) < 2) {
            throw new Exception("modifyacct requires that at least one attribute is passed to it");
        }
        return $this->makeQuery('modifyacct', $args);
    }
    /**
    * Edit a cPanel Account's Quota
    *
    * This call will allow you to change a cPanel account's quota
    *
    * @param string $username The username of the account to modify the quota.
    * @param int    $quota    the new quota in megabytes
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/EditQuota XML API Call documentation
    */
    public function editquota($username, $quota)
    {
        if (!isset($username) || !isset($quota)) {
            throw new Exception("editquota requires that an username and quota are passed to it");
        }
        return $this->makeQuery('editquota', array(
            'user' => $username,
            'quota' => $quota
        ));
    }
    /**
    * Return a summary of the account's information
    *
    * This call will return a brief report of information about an account, such as:
    *   - Disk Limit
    *   - Disk Used
    *   - Domain
    *   - Account Email
    *   - Theme
    *   - Start Data
    *
    * Please see the XML API Call documentation for more information on what is returned by this call
    *
    * @param string $username The username to retrieve a summary of
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/ShowAccountInformation XML API Call documenation
    */
    public function accountsummary($username)
    {
        if (!isset($username)) {
            throw new Exception("accountsummary requires that an username is passed to it");
        }
        return $this->makeQuery('accountsummary', array(
            'user' => $username
        ));
    }
    /**
    * Suspend a User's Account
    *
    * This function will suspend the specified cPanel users account.
    * The $reason parameter is optional, but can contain a string of any length
    *
    * @param string $username The username to suspend
    * @param string $reason   The reason for the suspension
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/SuspendAccount XML API Call documentation
    */
    public function suspendacct($username, $reason = null)
    {
        if (!isset($username)) {
            throw new Exception("suspendacct requires that an username is passed to it");
        }
        if ($reason) {
            return $this->makeQuery('suspendacct', array(
                'user' => $username,
                'reason' => $reason
            ));
        }
        return $this->makeQuery('suspendacct', array(
            'user' => $username
        ));
    }
    /**
    * List suspended accounts on a server
    *
    * This function will return an array containing all the suspended accounts on a server
    *
    * 
     * @return mixed
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/ListSuspended XML API Call documentation
    */
    public function listsuspended()
    {
        return $this->makeQuery('listsuspended');
    }
    /**
    * Remove an Account
    *
    * This XML API call will remove an account on the server
    * The $keepdns parameter is optional, when enabled this will leave the DNS zone on the server
    *
    * @param string $username The usename to delete
    * @param bool   $keepdns  When pass a true value, the DNS zone will be retained
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/TerminateAccount
    */
    public function removeacct($username, $keepdns = false)
    {
        if (!isset($username)) {
            throw new Exception("removeacct requires that a username is passed to it");
        }
        if ($keepdns) {
            return $this->makeQuery('removeacct', array(
                'user' => $username,
                'keepdns' => '1'
            ));
        }
        return $this->makeQuery('removeacct', array(
            'user' => $username
        ));
    }
    /**
    * Unsuspend an Account
    *
    * This XML API call will unsuspend an account
    *
    * @param string $username The username to unsuspend
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/UnsuspendAcount XML API Call documentation
    */
    public function unsuspendacct($username)
    {
        if (!isset($username)) {
            throw new Exception("unsuspendacct requires that a username is passed to it");
        }
        return $this->makeQuery('unsuspendacct', array(
            'user' => $username
        ));
    }
    /**
    * Change an Account's Package
    *
    * This XML API will change the package associated account.
    *
    * @param string $username the username to change the package of
    * @param string $pkg      The package to change the account to.
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/ChangePackage XML API Call documentation
    */
    public function changepackage($username, $pkg)
    {
        if (!isset($username) || !isset($pkg)) {
            throw new Exception("changepackage requires that username and pkg are passed to it");
        }
        return $this->makeQuery('changepackage', array(
            'user' => $username,
            'pkg' => $pkg
        ));
    }
    /**
    * Return the privileges a reseller has in WHM
    *
    * This will return a list of the privileges that a reseller has to WHM
    * 
     * @return mixed
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/ViewPrivileges XML API Call documentation
    */
    public function myprivs()
    {
        return $this->makeQuery('myprivs');
    }
    /**
    * Display Data about a Virtual Host
    * 
    * This function will return information about a specific domain.  This data is essentially a representation of the data
    * Contained in the httpd.conf VirtualHost for the domain.
    *
    * 
     * @return mixed 
    * @param string $domain The domain to fetch information for
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/DomainUserData
    */
    /**
     * domainuserdata
     * Insert description here
     *
     * @param $domain
     *                  
     * 
     * @return
     *         
     * @access
     * @static
     * @see   
     * @since 
     */
    public function domainuserdata($domain)
    {
        if (!isset($domain)) {
            throw new Exception("domainuserdata requires that domain is passed to it");
        }
        return $this->makeQuery("domainuserdata", array(
            'domain' => $domain
        ));
    }
    /**
    * Change a site's IP Address
    * 
    * This function will allow you to change the IP address that a domain listens on.
    * In order to properly call this function Either $user or $domain parameters must be defined
    * @param string $ip     The $ip address to change the account or domain to
    * @param string $user   The username to change the IP of
    * @param string $domain The domain to change the IP of
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/SetSiteIp XML API Call documentation
    */
    public function setsiteip($ip, $user = null, $domain = null)
    {
        if (!isset($ip)) {
            throw new Exception("setsiteip requires that ip is passed to it");
        }
        if ($user == null && $domain == null) {
            throw new Exception("setsiteip requires that either domain or user is passed to it");
        }
        if ($user == null) {
            return $this->makeQuery("setsiteip", array(
                "ip" => $ip,
                "domain" => $domain
            ));
        } else {
            return $this->makeQuery("setsiteip", array(
                "ip" => $ip,
                "user" => $user
            ));
        }
    }
    //###
    // DNS Functions
    //###
    // This API function lets you create a DNS zone.
    
    /**
    * Add a DNS Zone
    *
    * This XML API function will create a DNS Zone.  This will use the "standard" template when
    * creating the zone.
    *
    * @param string $domain The DNS Domain that you wish to create a zone for
    * @param string $ip     The IP you want the domain to resolve to
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/AddDNSZone XML API Call documentation
    */
    public function adddns($domain, $ip)
    {
        if (!isset($domain) || !isset($ip)) {
            throw new Exception("adddns require that domain, ip are passed to it");
        }
        return $this->makeQuery('adddns', array(
            'domain' => $domain,
            'ip' => $ip
        ));
    }
    /**
    * Add a record to a zone
    *
    * This will append a record to a DNS Zone.  The $args argument to this function 
    * must be an associative array containing information about the DNS zone, please 
    * see the XML API Call documentation for more info
    *
    * @param string $zone The DNS zone that you want to add the record to
    * @param array  $args Associative array representing the record to be added
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/AddZoneRecord XML API Call documentation
    */
    public function addzonerecord($zone, $args)
    {
        if (!is_array($args)) {
            throw new Exception("addzonerecord requires that $args passed to it is an array");
        }
        $args['zone'] = $zone;
        return $this->makeQuery('addzonerecord', $args);
    }
    /**
    * Edit a Zone Record
    *
    * This XML API Function will allow you to edit an existing DNS Zone Record.
    * This works by passing in the line number of the record you wish to edit.
    * Line numbers can be retrieved with dumpzone()
    *
    * @param string $zone The zone to edit
    * @param int    $line The line number of the zone to edit
    * @param array  $args An associative array representing the zone record
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/EditZoneRecord XML API Call documentation
    * @see    dumpzone()
    */
    /**
     * editzonerecord
     * Insert description here
     *
     * @param $zone
     * @param $line
     * @param $args
     *                
     * 
     * @return
     *         
     * @access
     * @static
     * @see   
     * @since 
     */
    public function editzonerecord($zone, $line, $args)
    {
        if (!is_array($args)) {
            throw new Exception("editzone requires that $args passed to it is an array");
        }
        $args['domain'] = $zone;
        $args['Line'] = $line;
        return $this->makeQuery('editzonerecord', $args);
    }
    /**
    * Retrieve a DNS Record
    *
    * This function will return a data structure representing a DNS record, to 
    * retrieve all lines see dumpzone.
    * @param string $zone The zone that you want to retrieve a record from
    * @param string $line The line of the zone that you want to retrieve
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/GetZoneRecord XML API Call documentation
    */
    public function getzonerecord($zone, $line)
    {
        return $this->makeQuery('getzonerecord', array(
            'domain' => $zone,
            'Line' => $line
        ));
    }
    /**
    * Remove a DNS Zone
    *
    * This function will remove a DNS Zone from the server
    *
    * @param string $domain The domain to be remove
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/DeleteDNSZone XML API Call documentation
    */
    public function killdns($domain)
    {
        if (!isset($domain)) {
            throw new Exception("killdns requires that domain is passed to it");
        }
        return $this->makeQuery('killdns', array(
            'domain' => $domain
        ));
    }
    /**
    * Return a List of all DNS Zones on the server
    * 
    * This XML API function will return an array containing all the DNS Zones on the server
    *
    * 
     * @return mixed
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/ListDNSZone XML API Call documentation
    */
    public function listzones()
    {
        return $this->makeQuery('listzones');
    }
    /**
    * Return all records in a zone
    *
    * This function will return all records within a zone.
    * @param string $domain The domain to return the records from.
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/ListOneZone XML API Call documentation
    * @see    editdnsrecord()
    * @see    getdnsrecord()
    */
    public function dumpzone($domain)
    {
        if (!isset($domain)) {
            throw new Exception("dumpzone requires that a domain is passed to it");
        }
        return $this->makeQuery('dumpzone', array(
            'domain' => $domain
        ));
    }
    /**
    * Return a Nameserver's IP
    *
    * This function will return a nameserver's IP
    *
    * @param string $nameserver The nameserver to lookup
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/LookupIP XML API Call documentation
    */
    public function lookupnsip($nameserver)
    {
        if (!isset($nameserver)) {
            throw new Exception("lookupnsip requres that a nameserver is passed to it");
        }
        return $this->makeQuery('lookupnsip', array(
            'nameserver' => $nameserver
        ));
    }
    /**
    * Remove a line from a zone
    *
    * This function will remove the specified line from a zone
    * @param string $zone The zone to remove a line from
    * @param int    $line The line to remove from the zone
    * @link  http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/RemoveZone XML API Call documentation
    */
    public function removezonerecord($zone, $line)
    {
        if (!isset($zone) || !isset($line)) {
            throw new Exception("removezone record requires that a zone and line number is passed to it");
        }
        return $this->makeQuery('removezonerecord', array(
            'zone' => $zone,
            'Line' => $line
        ));
    }
    /**
    * Reset a zone
    *
    * This function will reset a zone removing all custom records.  Subdomain records will be readded by scanning the userdata datastore.
    * @param string $domain the domain name of the zone to reset
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/ResetZone XML API Call documentation
    */
    public function resetzone($domain)
    {
        if (!isset($domain)) {
            throw new Exception("resetzone requires that a domain name is passed to it");
        }
        return $this->makeQuery('resetzone', array(
            'domain' => $domain
        ));
    }
    //###
    // Package Functions
    //###
    
    /**
    * Add a new package
    * 
    * This function will allow you to add a new package
    * This function should be passed an associative array containing elements that define package parameters.
    * These variables map directly to the parameters for the XML-API Call, please refer to the link below for a complete 
    * list of possible variable.  The "name" element is required.
    * @param array $pkg an associative array containing package parameters
    * 
     * @return mixed
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/AddPackage XML API Call documentation
    */
    public function addpkg($pkg)
    {
        if (!isset($pkg['name'])) {
            throw new Exception("addpkg requires that name is defined in the array passed to it");
        }
        return $this->makeQuery('addpkg', $pkg);
    }
    /**
    * Remove a package
    * 
    * This function allow you to delete a package
    * @param string $pkgname The package you wish to delete
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/DeletePackage XML API Call documentation
    */
    public function killpkg($pkgname)
    {
        if (!isset($pkgname)) {
            throw new Exception("killpkg requires that the package name is passed to it");
        }
        return $this->makeQuery('killpkg', array(
            'pkg' => $pkgname
        ));
    }
    /**
    * Edit a package
    *
    * This function allows you to change a package's paremeters.  This is passed an associative array defining
    * the parameters for the package.  The keys within this array map directly to the XML-API call, please see the link
    * below for a list of possible keys within this package.  The name element is required.
    * @param array $pkg An associative array containing new parameters for the package
    * 
     * @return mixed
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/EditPackage XML API Call documentation
    */
    public function editpkg($pkg)
    {
        if (!$isset($pkg['name'])) {
            throw new Exception("editpkg requires that name is defined in the array passed to it");
        }
        return $this->makeQuery('editpkg', $pkg);
    }
    /**
    * List Packages
    *
    * This function will list all packages available to the user
    * 
     * @return mixed
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/ListPackages XML API Call documentation
    */
    public function listpkgs()
    {
        return $this->makeQuery('listpkgs');
    }
    //###
    // Reseller functions
    //###
    
    /**
    * Make a user a reseller
    *
    * This function will allow you to mark an account as having reseller privileges
    * @param string $username  The username of the account you wish to add reseller privileges to
    * @param int    $makeowner Boolean 1 or 0 defining whether the account should own itself or not
    * @see    setacls()
    * @see    setresellerlimits()
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/AddResellerPrivileges XML API Call documentation
    */
    public function setupreseller($username, $makeowner = true)
    {
        if (!isset($username)) {
            throw new Exception("setupreseller requires that username is passed to it");
        }
        if ($makeowner) {
            return $this->makeQuery('setupreseller', array(
                'user' => $username,
                'makeowner' => '1'
            ));
        }
        return $this->makeQuery('setupreseller', array(
            'user' => $username,
            'makeowner' => '0'
        ));
    }
    /**
    * Create a New ACL List
    *
    * This function allows you to create a new privilege set for reseller accounts.  This is passed an
    * Associative Array containing the configuration information for this variable.  Please see the XML API Call documentation
    * For more information.  "acllist" is a required element within this array
    * @param array $acl an associative array describing the parameters for the ACL to be create
    * 
     * @return mixed
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/CreateResellerACLList XML API Call documentation
    */
    public function saveacllist($acl)
    {
        if (!isset($acl['acllist'])) {
            throw new Exception("saveacllist requires that acllist is defined in the array passed to it");
        }
        return $this->makeQuery('saveacllist', $acl);
    }
    /**
    * List available saved ACLs
    *
    * This function will return a list of Saved ACLs for reseller accounts
    * 
     * @return mixed
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/ListCurrentResellerACLLists XML API Call documentation
    */
    public function listacls()
    {
        return $this->makeQuery('listacls');
    }
    /**
    * List Resellers
    *
    * This function will return a list of resellers on the server
    * 
     * @return mixed
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/ListResellerAccounts XML API Call documentation
    */
    public function listresellers()
    {
        return $this->makeQuery('listresellers');
    }
    /**
    * Get a reseller's statistics
    *
    * This function will return general information on a reseller and all it's account individually such as disk usage and bandwidth usage
    *
    * @param string $username The reseller to be checked
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/ListResellersAccountsInformation XML API Call documentation
    */
    public function resellerstats($username)
    {
        if (!isset($username)) {
            throw new Exception("resellerstats requires that a username is passed to it");
        }
        return $this->makeQuery('resellerstats', array(
            'reseller' => $username
        ));
    }
    /**
    * Remove Reseller Privileges
    *
    * This function will remove an account's reseller privileges, this does not remove the account.
    *
    * @param string $username The username to remove reseller privileges from
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/RemoveResellerPrivileges XML API Call documentation
    */
    public function unsetupreseller($username)
    {
        if (!isset($username)) {
            throw new Exception("unsetupreseller requires that a username is passed to it");
        }
        return $this->makeQuery('unsetupreseller', array(
            'user' => $username
        ));
    }
    /**
    * Set a reseller's privileges
    *
    * This function will allow you to set what parts of WHM a reseller has access to.  This is passed an associative array
    * containing the privleges that this reseller should have access to.  These map directly to the parameters passed to the XML API Call
    * Please view the XML API Call documentation for more information.  "reseller" is the only required element within this array
    * @param array $acl An associative array containing all the ACL information for the reseller
    * 
     * @return mixed
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/SetResellersACLList XML API Call documentation
    */
    public function setacls($acl)
    {
        if (!isset($acl['reseller'])) {
            throw new Exception("setacls requires that reseller is defined in the array passed to it");
        }
        return $this->makeQuery('setacls', $acl);
    }
    /**
    * Terminate a Reseller's Account
    *
    * This function will terminate a reseller's account and all accounts owned by the reseller
    *
    * @param string  $reseller          the name of the reseller to terminate
    * @param boolean $terminatereseller Passing this as true will terminate the the reseller's account as well as all the accounts owned by the reseller
    * 
     * @return mixed  
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/TerminateResellerandAccounts XML API Call documentation
    *         
    *         */
    public function terminatereseller($reseller, $terminatereseller = true)
    {
        if (!isset($reseller)) {
            throw new Exception("terminatereseller requires that username is passed to it");
        }
        $verify = 'I understand this will irrevocably remove all the accounts owned by the reseller ' . $reseller;
        if ($terminatereseller) {
            return $this->makeQuery('terminatereseller', array(
                'reseller' => $reseller,
                'terminatereseller' => '1',
                'verify' => $verify
            ));
        }
        return $this->makeQuery('terminatereseller', array(
            'reseller' => $reseller,
            'terminatereseller' => '0',
            'verify' => $verify
        ));
    }
    /**
    * Set a reseller's dedicated IP addresses
    *
    * This function will set a reseller's dedicated IP addresses.  If an IP is not passed to this function, 
    * it will reset the reseller to use the server's main shared IP address.
    * @param string $user The username of the reseller to change dedicated IPs for
    * @param string $ip   The IP to assign to the  reseller, this can be a comma-seperated list of IPs to allow for multiple IP addresses
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/SetResellerIps XML API Call documentation
    */
    public function setresellerips($user, $ip = null)
    {
        if (!isset($user)) {
            throw new Exception("setresellerips requires that a username is passed to it");
        }
        $params = array(
            "user" => $user
        );
        if ($ip != null) {
            $params['ip'] = $ip;
        }
        return $this->makeQuery('setresellerips', $params);
    }
    /**
    * Set Accounting Limits for a reseller account
    *
    * This function allows you to define limits for reseller accounts not included with in access control such as
    * the number of accounts a reseller is allowed to create, the amount of disk space to use.
    * This function is passed an associative array defining these limits, these map directly to the parameters for the XML API
    * Call, please refer to the XML API Call documentation for more information.  The only required parameters is "user"
    *
    * @param array $reseller_cfg An associative array containing configuration information for the specified reseller
    * 
     * @return mixed
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/SetResellerLimits XML API Call documentation
    *         
    */
    public function setresellerlimits($reseller_cfg)
    {
        if (!isset($reseller_cfg['user'])) {
            throw new Exception("setresellerlimits requires that a user is defined in the array passed to it");
        }
        return $this->makeQuery('setresellerips', $reseller_cfg);
    }
    /**
    * Set a reseller's main IP
    *
    * This function will allow you to set a reseller's main IP.  By default all accounts created by this reseller
    * will be created on this IP
    * @param string $reseller the username of the reseller to change the main IP of
    * @param string $ip       The ip you would like this reseller to create accounts on by default
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/SetResellerMainIp XML API Call documentation
    */
    public function setresellermainip($reseller, $ip)
    {
        if (!isset($reseller) || !isset($ip)) {
            throw new Exception("setresellermainip requires that an reseller and ip are passed to it");
        }
        return $this->makeQuery("setresellermainip", array(
            'user' => $reseller,
            'ip' => $ip
        ));
    }
    /**
    * Set reseller package limits
    *
    * This function allows you to define which packages a reseller has access to use
    * @param string  $user     The reseller you wish to define package limits for
    * @param boolean $no_limit Whether or not you wish this reseller to have packages limits
    * @param string  $package  if $no_limit is false, then the package you wish to modify privileges for
    * @param boolean $allowed  if $no_limit is false, then defines if the reseller should have access to the package or not
    * @param int     $number   if $no_limit is false, then defines the number of account a reseller can create of a specific package
    * 
     * @return mixed  
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/SetResellerPkgLimit XML API Call documentation
    */
    public function setresellerpackagelimits($user, $no_limit, $package = null, $allowed = null, $number = null)
    {
        if (!isset($user) || !isset($no_limit)) {
            throw new Exception("setresellerpackagelimits requires that a username and no_limit are passed to it by default");
        }
        if ($no_limit) {
            return $this->makeQuery("setresellerpackagelimits", array(
                'user' => $user,
                "no_limit" => '1'
            ));
        } else {
            if (is_null($package) || is_null($allowed)) {
                throw new Exception('setresellerpackagelimits requires that package and allowed are passed to it if no_limit eq 0');
            }
            $params = array(
                'user' => $user,
                'no_limit' => '0'
            );
            if ($allowed) {
                $params['allowed'] = 1;
            } else {
                $params['allowed'] = 0;
            }
            if (!is_null($number)) {
                $params['number'] = $number;
            }
            return $this->makeQuery('setresellerpackagelimits', $params);
        }
    }
    /**
    * Suspend a reseller and all accounts owned by a reseller
    *
    * This function, when called will suspend a reseller account and all account owned by said reseller
    * @param string $reseller The reseller account to be suspended
    * @param string $reason   (optional) The reason for suspending the reseller account
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/SuspendReseller XML API Call documentation
    */
    public function suspendreseller($reseller, $reason = null)
    {
        if (!isset($reseller)) {
            throw new Exception("suspendreseller requires that the reseller's username is passed to it");
        }
        $params = array(
            "user" => $reseller
        );
        if ($reason) {
            $params['reason'] = $reason;
        }
        return $this->makeQuery('suspendreseller', $params);
    }
    /**
    * Unsuspend a Reseller Account
    *
    * This function will unsuspend a reseller account and all accounts owned by the reseller in question
    * @param string $user The username of the reseller to be unsuspended
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/UnsuspendReseller XML API Call documentation
    */
    public function unsuspendreseller($user)
    {
        if (!isset($user)) {
            throw new Exception("unsuspendreseller requires that a username is passed to it");
        }
        return $this->makeQuery('unsuspendreseller', array(
            'user' => $user
        ));
    }
    /**
    * Get the number of accounts owned by a reseller
    *
    * This function will return the number of accounts owned by a reseller account, along with information such as the number of active, suspended and accounting limits
    * @param string $user The username of the reseller to get account information from
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/AcctCounts XML API Call documentation
    */
    public function acctcounts($user)
    {
        if (!isset($user)) {
            throw new Exception('acctcounts requires that a username is passed to it');
        }
        return $this->makeQuery('acctcounts', array(
            'user' => $user
        ));
    }
    /**
    * Set a reseller's nameservers
    *
    * This function allows you to change the nameservers that account created by a specific reseller account will use.
    * If this function is not passed a $nameservers parameter, it will reset the nameservers for the reseller to the servers's default
    * @param string $user        The username of the reseller account to grab reseller accounts from
    * @param string $nameservers A comma seperate list of nameservers
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/SetResellerNameservers XML API Call documentation
    */
    public function setresellernameservers($user, $nameservers = null)
    {
        if (!isset($user)) {
            throw new Exception("setresellernameservers requires that a username is passed to it");
        }
        $params = array(
            'user' => $user
        );
        if ($nameservers) {
            $params['nameservers'] = $nameservers;
        }
        return $this->makeQuery('setresellernameservers', $params);
    }
    //###
    // Server information
    //###
    
    /**
    * Get a server's hostname
    *
    * This function will return a server's hostname
    * 
     * @return mixed
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/DisplayServerHostname XML API Call documentation
    */
    public function gethostname()
    {
        return $this->makeQuery('gethostname');
    }
    /**
    * Get the version of cPanel running on the server
    *
    * This function will return the version of cPanel/WHM running on the remote system
    * 
     * @return mixed
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/DisplaycPanelWHMVersion XML API Call documentation
    */
    public function version()
    {
        return $this->makeQuery('version');
    }
    /**
    * Get Load Average
    *
    * This function will return the loadavg of the remote system
    *
    * 
     * @return mixed
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/LoadAvg XML API Call documentation
    */
    public function loadavg()
    {
        return $this->makeQuery('loadavg');
    }
    /**
    * Get a list of languages on the remote system
    *
    * This function will return a list of available langauges for the cPanel interface
    * 
     * @return mixed
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/GetLangList XML API Call documentation
    *         
    */
    public function getlanglist()
    {
        return $this->makeQuery('getlanglist');
    }
    //###
    // Server administration
    //###
    
    /**
    * Reboot server
    *
    * This function will reboot the server
    * @param boolean $force This will determine if the server should be given a graceful or forceful reboot
    * 
     * @return mixed  
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/RebootServer XML API Call documentation
    */
    public function reboot($force = false)
    {
        if ($force) {
            return $this->makeQuery('reboot', array(
                'force' => '1'
            ));
        }
        return $this->makeQuery('reboot');
    }
    /**
    * Add an IP to a server
    *
    * This function will add an IP alias to your server
    * @param string $ip      The IP to be added
    * @param string $netmask The netmask of the IP to be added
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/AddIPAddress XML API Call documentation
    */
    public function addip($ip, $netmask)
    {
        if (!isset($ip) || !isset($netmask)) {
            throw new Exception("addip requires that an IP address and Netmask are passed to it");
        }
        return $this->makeQuery('addip', array(
            'ip' => $ip,
            'netmask' => $netmask
        ));
    }
    // This function allows you to delete an IP address from your server.
    
    /**
    * Delete an IP from a server
    *
    * Remove an IP from the server
    * @param string $ip             The IP to remove
    * @param string $ethernetdev    The ethernet device that the IP is bound to
    * @param bool   $skipifshutdown Whether the function should remove the IP even if the ethernet interface is down
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/DeleteIPAddress XML API Call documentation
    */
    public function delip($ip, $ethernetdev = null, $skipifshutdown = false)
    {
        $args = array();
        if (!isset($ip)) {
            throw new Exception("delip requires that an IP is defined in the array passed to it");
        }
        $args['ip'] = $ip;
        if ($ethernetdev) {
            $args['ethernetdev'] = $ethernetdev;
        }
        $args['skipifshutdown'] = ($skipifshutdown) ? '1' : '0';
        return $this->makeQuery('delip', $args);
    }
    /**
    * List IPs
    *
    * This should return a list of IPs on a server
    * 
     * @return mixed
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/DeleteIPAddress XML API Call documentation
    */
    public function listips()
    {
        return $this->makeQuery('listips');
    }
    /**
    * Set Hostname
    *
    * This function will allow you to set the hostname of the server
    * @param string $hostname the hostname that should be assigned to the serve
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/SetHostname XML API Call documentation
    */
    public function sethostname($hostname)
    {
        if (!isset($hostname)) {
            throw new Exception("sethostname requires that hostname is passed to it");
        }
        return $this->makeQuery('sethostname', array(
            'hostname' => $hostname
        ));
    }
    /**
    * Set the resolvers used by the server
    * 
    * This function will set the resolvers in /etc/resolv.conf for the server
    * @param string $nameserver1 The IP of the first nameserver to use
    * @param string $nameserver2 The IP of the second namesever to use
    * @param string $nameserver3 The IP of the third nameserver to use
    * @param string $nameserver4 The IP of the forth nameserver to use
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/SetResolvers XML API Call documentation
    */
    public function setresolvers($nameserver1, $nameserver2 = null, $nameserver3 = null)
    {
        $args = array();
        if (!isset($nameserver1)) {
            throw new Exception("setresolvers requires that nameserver1 is defined in the array passed to it");
        }
        $args['nameserver1'] = $nameserver1;
        if ($nameserver2) {
            $args['nameserver2'] = $nameserver2;
        }
        if ($nameserver3) {
            $args['nameserver3'] = $nameserver3;
        }
        return $this->makeQuery('setresolvers', $args);
    }
    /**
    * Display bandwidth Usage
    *
    * This function will return all bandwidth usage information for the server,
    * The arguments for this can be passed in via an associative array, the elements of this array map directly to the
    * parameters of the call, please see the XML API Call documentation for more information
    * @param array $args The configuration for what bandwidth information to display
    * 
     * @return mixed
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/ShowBw XML API Call documentation
    */
    public function showbw($args = null)
    {
        if (is_array($args)) {
            return $this->makeQuery('showbw', $args);
        }
        return $this->makeQuery('showbw');
    }
    /**
     * nvset
     * Insert description here
     *
     * @param $key  
     * @param $value
     *                 
     * 
     * @return
     *         
     * @access
     * @static
     * @see   
     * @since 
     */
    public function nvset($key, $value)
    {
        if (!isset($key) || !isset($value)) {
            throw new Exception("nvset requires that key and value are passed to it");
        }
        return $this->makeQuery('nvset', array(
            'key' => $key,
            'value' => $value
        ));
    }
    // This function allows you to retrieve and view a non-volatile variable's value.
    
    /**
     * nvget
     * Insert description here
     *
     * @param $key
     *               
     * 
     * @return
     *         
     * @access
     * @static
     * @see   
     * @since 
     */
    public function nvget($key)
    {
        if (!isset($key)) {
            throw new Exception("nvget requires that key is passed to it");
        }
        return $this->makeQuery('nvget', array(
            'key' => $key
        ));
    }
    //###
    // Service functions
    //###
    
    /**
    * Restart a Service
    *
    * This function allows you to restart a service on the server
    * @param string $service the service that you wish to restart please view the XML API Call documentation for acceptable values to this parameters
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/RestartService XML API Call documentation
    */
    public function restartsrv($service)
    {
        if (!isset($service)) {
            throw new Exception("restartsrv requires that service is passed to it");
        }
        return $this->makeQuery('restartservice', array(
            'service' => $service
        ));
    }
    /**
    * Service Status
    *
    * This function will return the status of all services on the and whether they are running or not
    * 
     * @return mixed
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/ServiceStatus XML API Call documentation
    */
    public function servicestatus()
    {
        return $this->makeQuery('servicestatus');
    }
    /**
    * Configure A Service
    *
    * This function will allow you to enabled or disable services along with their monitoring by chkservd
    * @param string $service   The service to be monitored
    * @param bool   $enabled   Whether the service should be enabled or not
    * @param bool   $monitored Whether the service should be monitored or not
    * 
     * @return mixed 
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/ConfigureService XML API Call documentation
    */
    public function configureservice($service, $enabled = true, $monitored = true)
    {
        if (!isset($service)) {
            throw new Exception("configure service requires that a service is passed to it");
        }
        $params = array(
            'service' => $service
        );
        if ($enabled) {
            $params['enabled'] = 1;
        } else {
            $params['enabled'] = 0;
        }
        if ($monitored) {
            $params['monitored'] = 1;
        } else {
            $params['monitored'] = 0;
        }
        return $this->makeQuery('configureservice', $params);
    }
    //###
    // SSL functions
    //###
    
    /**
    * Display information on an SSL host
    *
    * This function will return information on an SSL Certificate, CSR, cabundle and SSL key for a specified domain
    * @param array $args Configuration information for the SSL certificate, please see XML API Call documentation for required values
    * 
     * @return mixed
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/FetchSSL XML API Call documentation
    */
    public function fetchsslinfo($args)
    {
        if ((isset($args['domain']) && isset($args['crtdata'])) || (!isset($args['domain']) && !isset($args['crtdata']))) {
            throw new Exception("fetchsslinfo requires domain OR crtdata is passed to it");
        }
        if (isset($args['crtdata'])) {
            // crtdata must be URL-encoded!
            $args['crtdata'] = urlencode(trim($args['crtdata']));
        }
        return $this->makeQuery('fetchsslinfo', $args);
    }
    /**
    * Generate an SSL Certificate
    *
    * This function will generate an SSL Certificate, the arguments for this map directly to the call for the XML API call.  Please consult the XML API Call documentation for more information
    * @param array $args the configuration for the SSL Certificate being generated
    * 
     * @return mixed
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/GenerateSSL XML API Call documentation
    */
    public function generatessl($args)
    {
        if (!isset($args['xemail']) || !isset($args['host']) || !isset($args['country']) || !isset($args['state']) || !isset($args['city']) || !isset($args['co']) || !isset($args['cod']) || !isset($args['email']) || !isset($args['pass'])) {
            throw new Exception("generatessl requires that xemail, host, country, state, city, co, cod, email and pass are defined in the array passed to it");
        }
        return $this->makeQuery('generatessl', $args);
    }
    /**
    * Install an SSL certificate
    *
    * This function will allow you to install an SSL certificate that is uploaded via the $argument parameter to this call.  The arguments for this call map directly to the parameters for the XML API call, 
    * please consult the XML API Call documentation for more information.
    * @param array $args The configuration for the SSL certificate
    * 
     * @return mixed
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/InstallSSL XML API Call documentation
    */
    public function installssl($args)
    {
        if (!isset($args['user']) || !isset($args['domain']) || !isset($args['cert']) || !isset($args['key']) || !isset($args['cab']) || !isset($args['ip'])) {
            throw new Exception("installssl requires that user, domain, cert, key, cab and ip are defined in the array passed to it");
        }
        return $this->makeQuery('installssl', $args);
    }
    /**
    * List SSL Certs
    *
    * This function will list all SSL certificates installed on the server
    * 
     * @return mixed
    * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/ListSSL XML API Call documentation
    */
    public function listcrts()
    {
        return $this->makeQuery('listcrts');
    }
}
?>