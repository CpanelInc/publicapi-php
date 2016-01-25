<?php
/**
 * Cpanel_Service_Adapter_WHMapi
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
 * Service adapter for Whostmgr Service
 *
 * @class     Cpanel_Service_Adapter_WHMapi
 * @category  Cpanel
 * @package   Cpanel_Service
 * @author    David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright Copyright (c) 2011, cPanel, Inc., All rights Reserved. (http://cpanel.net) 
 * @license   http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version   0.1.0
 * @link      http://sdk.cpanel.net
 * @since     0.1.0
 */
class Cpanel_Service_Adapter_WHMapi extends Cpanel_Service_XmlapiClientClass
{
    /**
     * Default response format type
     */
    const DRFT = 'JSON';
    /**
     * Current response format type
     */
    private $_adapterResponseFormatType;
    /**
     * Valid response format types for this service adapter
     */
    private $_validRFT = array(
        'JSON',
        'XML'
    );

    /**
     * Constructor
     *
     * Prepare the adapter for use by Service.
     *
     * If $RFT is not passed the const Cpanel_Service_Adapter_WHMapi::DRFT will be
     * used when invoking {@link setAdapterResponseFormatType()} at
     * instantiation
     *
     * HTTP port is set to '2087' by default.  {@link setPort()}
     *
     * NOTE: this constructor as support for the legacy PHP XML-API client class
     *
     * @param string $host     Host address for query call
     * @param string $user     User to authenticate query call
     * @param string $password Password to authenticate query call
     * @param string $RFT      Response format type
     *
     * @throws Exception in case of no valid stread wrapper is found
     * @return Cpanel_Service_Adapter_WHMapi
     */
    public function __construct($host = null, $user = null, $password = null, $RFT = null)
    {
        parent::__construct();
        if ($host) {
            $this->host = $host;
        }
        if ($user) {
            $this->user = $user;
        }
        if ($password) {
            $this->setPassword($password);
        }

        // @codeCoverageIgnoreStart
        $registeredStreams = stream_get_wrappers();
        if (in_array('https', $registeredStreams)) {
            $port = 2087;
        } elseif (in_array('http', $registeredStreams)) {
            $port = 2086;
        } else {
            throw new Exception('No valid protocol stream wrapper registered');
        }
        // @codeCoverageIgnoreEnd

        $this->setPort($port);
        $RFT = ($RFT) ? $RFT : self::DRFT;
        $this->setAdapterResponseFormatType($RFT);
        return $this;
    }
    /**
     * Return the current response format type
     * 
     * @see Cpanel_Query_Http_Abstract::getAdapterResponseFormatType()
     * 
     * @return string
     */
    public function getAdapterResponseFormatType()
    {
        return $this->_adapterResponseFormatType;
    }
    /**
     * Set the response format type
     * 
     * @param string $type The response format type to set
     * 
     * @see    Cpanel_Query_Http_Abstract::setAdapterResponseFormatType()
     * 
     * @return Cpanel_Service_Adapter_Cpanelapi
     * @throws Exception If an invalid RFT
     */
    public function setAdapterResponseFormatType($type)
    {
        if (!in_array($type, $this->_validRFT)) {
            throw new Exception('Invalid adapter response format type');
        }
        $this->_adapterResponseFormatType = $type;
        return $this;
    }
    /**
     * Method for querying a native XML-API function
     * 
     * @param string $function XML-API function to invoke
     * @param array  $args     Arguments for $function
     * 
     * @see    Cpanel_Query_Http_Abstract::xmlapi_query()
     * @link   http://docs.cpanel.net/twiki/bin/vief/AllDocumentation/AutomationIntegration/XmlApi#Functions XML-API Funcitons
     * 
     * @return Cpanel_Query_Object
     */
    public function xmlapi_query($function, $args = array())
    {
        return $this->makeQuery($function, $args);
    }
    /**
     * Method for querying API1 via XML-API
     * 
     * @param string $user   User to query against
     * @param string $module API1 module to source
     * @param string $func   API1 function of $module to invoke
     * @param array  $args   Arguments for $function
     * 
     * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/CallingAPIFunctions Calling API Functions
     * @link   http://docs.cpanel.net/twiki/bin/view/ApiDocs/Api1/WebHome API1 Modules
     * @see    Cpanel_Query_Http_Abstract::api1_query()
     * 
     * @return Cpanel_Query_Object
     * @throws Exception If $user, $module, or $func are not defined
     * @throws Exception If $args is not an array
     */
    public function api1_query($user, $module, $func, $args = array())
    {
        if (empty($module) || empty($func) || empty($user)) {
            throw new Exception(
                __FUNCTION__ . ' requires username, module and function'
            );
        }
        if (!is_array($args)) {
            if (!empty($args)) {
                throw new Exception(
                    __FUNCTION__ . ' expects query arguments in an array'
                );
            } else {
                $args = array();
            }
        }
        $rObj = $this->getResponseObject();
        // TODO: reconcile if $rObj isn't set?
        $cpArgType = $rObj->getResponseFormatType();
        $call = array();
        foreach (array(
            'module',
            'func',
            'user'
        ) as $var) {
            $call['cpanel_' . strtolower($cpArgType) . 'api_' . $var] = $$var;
        }
        $call['cpanel_' . strtolower($cpArgType) . 'api_apiversion'] = '1';
        foreach ($args as $key => $value) {
            $call['arg-' . $key] = $value;
        }
        return $this->makeQuery('cpanel', $call);
    }
    /**
     * Method for querying API2 via XML-API
     * 
     * @param string $user   User to query against
     * @param string $module API2 module to source
     * @param string $func   API2 function of $module to invoke
     * @param array  $args   Arguments for $function
     * 
     * @link   http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/CallingAPIFunctions Calling API Functions
     * @link   http://docs.cpanel.net/twiki/bin/view/ApiDocs/Api2/WebHome API2 Modules
     * @see    Cpanel_Query_Http_Abstract::api2_query()
     * 
     * @return Cpanel_Query_Object
     * @throws Exception If $user, $module, or $func are not defined
     * @throws Exception If $args is not an array
     */
    public function api2_query($user, $module, $func, $args = array())
    {
        if (empty($user) || empty($module) || empty($func)) {
            throw new Exception(
                __FUNCTION__ . ' requires username, module and function'
            );
        }
        if (!is_array($args)) {
            if (!empty($args)) {
                throw new Exception(
                    __FUNCTION__ . ' expects query arguments in an array'
                );
            } else {
                $args = array();
            }
        }
        $rObj = $this->getResponseObject();
        // TODO: reconcile if $rObj isn't set?
        $cpArgType = $rObj->getResponseFormatType();
        foreach (array(
            'module',
            'func',
            'user'
        ) as $var) {
            $args['cpanel_' . strtolower($cpArgType) . 'api_' . $var] = $$var;
        }
        $args['cpanel_' . strtolower($cpArgType) . 'api_apiversion'] = '2';
        return $this->makeQuery('cpanel', $args);
    }
}
?>