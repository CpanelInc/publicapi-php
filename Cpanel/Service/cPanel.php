<?php
/**
 * Cpanel_Service_cPanel
 * 
 * Copyright (c) 2011, cPanel, L.L.C.
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
 *    * Neither the name of cPanel, L.L.C. nor the
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
 * @copyright Copyright (c) 2011, cPanel, L.L.C., All rights Reserved. (http://cpanel.net) 
 * @license   http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version   0.1.0
 * @link      http://sdk.cpanel.net
 * @since     0.1.0
 */
/** 
 * cPanel Service class
 *
 * @class     Cpanel_Service_cPanel
 * @category  Cpanel
 * @package   Cpanel_Service
 * @author    David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright Copyright (c) 2011, cPanel, L.L.C., All rights Reserved. (http://cpanel.net) 
 * @license   http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version   0.1.0
 * @link      http://sdk.cpanel.net
 * @since     0.1.0
 */
class Cpanel_Service_cPanel extends Cpanel_Service_Abstract
{
    /**
     * Default service adapter name
     */
    const ADAPTER_DEFAULT = 'cpanel';
    /**
     * Constructor
     * 
     * @param arrays $optsArray Option configuration data
     * 
     * @return Cpanel_Service_cPanel
     */
    public function __construct($optsArray = array())
    {
        if (!count($optsArray)) {
            $opts = array();
        }
        if (array_key_exists('config', $optsArray)) {
            $opts = $optsArray['config'];
        } else {
            $opts = $optsArray;
        }
        parent::__construct($opts);
        $this->listner->log('debug', "cPanel object configured");
        $rObj = $this->genResponseObject();
        $a = $this->getAdapter($rObj);
        $rObj->setResponseFormatType($a->getAdapterResponseFormatType());
        $a->setResponseObject($rObj);
        if ($a instanceof Cpanel_Query_Http_Abstract) {
            $this->initAdapter($a);
        }
        return $this;
    }
    /**
     * Fetch default adapter name for WHM service
     * 
     * @see    Cpanel_Service_Abstract::getDefaultAdapterName()
     * 
     * @return string
     */
    public function getDefaultAdapterName()
    {
        return self::ADAPTER_DEFAULT;
    }
    /**
     * Validate a given string corresponds to a usable adapter type
     * 
     * NOTE: will normalize $type for use in other methods
     * 
     * @param string $type service adapter name to validate
     * 
     * @see    Cpanel_Service_Abstract::validAdapter()
     * 
     * @return string|bool String of normalized $type, if valid, otherwise false
     */
    public function validAdapter($type)
    {
        if (strtolower($type) == parent::ADAPTER_CPANEL) {
            $normalized = parent::ADAPTER_CPANEL;
        } elseif (strtolower($type) == parent::ADAPTER_WHM
            || strtolower($type) == 'whm'
        ) {
            $normalized = parent::ADAPTER_WHM;
        } elseif (strtolower($type) == parent::ADAPTER_LIVE
            || strtolower($type) == 'local'
            || strtolower($type) == 'livephp'
        ) {
            $normalized = parent::ADAPTER_LIVE;
        } else {
            $normalized = false;
        }
        return $normalized;
    }
    /**
     * Spawn a new adapter object based on valid service adapter name
     * 
     * @param string $adapterType Valid service adapter name
     * 
     * @see    Cpanel_Service_Abstract::spawnAdapter()
     * 
     * @return Cpanel_Service_Adapter_Cpanelapi|Cpanel_Service_Adapter_WHMapi|Cpanel_Service_Adapter_Liveapi
     */
    protected function spawnAdapter($adapterType)
    {
        $c = 'Cpanel_Service_Adapter_Cpanelapi';
        switch ($adapterType) {
        case parent::ADAPTER_CPANEL:
            $c = 'Cpanel_Service_Adapter_Cpanelapi';
            break;

        case parent::ADAPTER_WHM:
            $c = 'Cpanel_Service_Adapter_WHMapi';
            break;

        case parent::ADAPTER_LIVE:
            $c = 'Cpanel_Service_Adapter_Liveapi';
            break;
        }
        $adapter = new $c;
        if ($this->listner) {
            $adapter->setOptions(array('listner' => $this->listner));
        }
        return $adapter;
    }
    /**
     * Make an API1 cPanel query for a given service
     * 
     * @param string $aservice service adapter name
     * @param array  $mf       Any required parameters for constructing the call
     *  like module and function
     * @param array  $args     Arguments for the API1 call, ordinal array
     * 
     * @return Cpanel_Query_Object
     */
    public function api1_request($aservice, $mf, $args = array())
    {
        $this->checkParams($aservice, $mf, $args, __FUNCTION__, parent::API1ARGS);
        $rObj = $this->genResponseObject($aservice);
        $a = $this->getAdapter($rObj);
        /**
         * TODO: handle more dynamically; 
         *  need logic for user set before query request. 
         *  prob need a set/get_output type meth.
         */
        $rObj->setResponseFormatType($a->getAdapterResponseFormatType());
        $a->setResponseObject($rObj);
        if ($this->isLocalQuery()) {
            return $a->makeQuery('exec', "1", $mf['module'], $mf['function'], $args);
        }
        $this->initAdapter($a);
        $user = $a->getUser();
        $account = (array_key_exists('user', $mf)) ? $mf['user'] : $user;
        return $a->api1_query($account, $mf['module'], $mf['function'], $args);
    }
    /**
     * Make an API2 cPanel query for a given service
     * 
     * @param string $aservice service adapter name
     * @param array  $mf       Any required parameters for constructing the call
     *  like module and function
     * @param array  $args     Arguments for the API2 call, associative array
     * 
     * @return Cpanel_Query_Object
     */
    public function api2_request($aservice, $mf, $args = array())
    {
        $this->checkParams($aservice, $mf, $args, __FUNCTION__, parent::API2ARGS);
        $rObj = $this->genResponseObject($aservice);
        $a = $this->getAdapter($rObj);
        $rObj->setResponseFormatType($a->getAdapterResponseFormatType());
        $a->setResponseObject($rObj);
        if ($this->isLocalQuery()) {
            return $a->makeQuery('exec', "2", $mf['module'], $mf['function'], $args);
        }
        $this->initAdapter($a);
        $user = $a->getUser();
        $account = (array_key_exists('user', $mf)) ? $mf['user'] : $user;
        return $a->api2_query($account, $mf['module'], $mf['function'], $args);
    }
    /**
     * Direct URL query method for PublicAPI client
     * 
     * @param string $uri          URL to query
     * @param array  $formdata     Array of URL parameters
     * @param array  $queryOptions Array of options for query mechanism
     * 
     * @return Cpanel_Query_Object
     */
    public function directURLQuery($uri, $formdata, $queryOptions = array())
    {
        $this->disableAdapter(parent::ADAPTER_LIVE);
        $rObj = $this->genResponseObject(self::ADAPTER_DEFAULT);
        $a = $this->getAdapter($rObj);
        if (strpos($uri, 'xml-api') !== false) {
            $rtype = 'XML';
        } elseif (strpos($uri, 'json-api') !== false) {
            $rtype = 'JSON';
        } else {
            $rtype = $a->getAdapterResponseFormatType();
        }
        if (!empty($queryOptions) && is_array($queryOptions)) {
            $rObj->query->setOptions($queryOptions);
        }
        $rObj->setResponseFormatType($rtype, true);
        $a->setResponseObject($rObj);
        $this->enableAdapter(parent::ADAPTER_LIVE);
        return $a->makeQuery($uri, $formdata);
    }
    /**
     * Set a mode for a stored adapter
     * 
     * @param string $adapterType The adapter to modify
     * @param string $mode        Mode to set
     * 
     * @return Cpanel_Service_cPanel
     */
    public function setAdapterMode($adapterType, $mode)
    {
        if ($a = $this->adapters[$adapterType]) {
            if (method_exists($a, 'setMode')) {
                $a->setMode($mode);
            }
        }
        return $this;
    }
    /**
     * Unset a mode for a stored adapter
     * 
     * @param string $adapterType The adapter to modify
     * @param string $mode        Mode to unset
     * 
     * @return Cpanel_Service_cPanel
     */
    public function unsetAdapterMode($adapterType, $mode)
    {
        if ($a = $this->adapters[$adapterType]) {
            if (method_exists($a, 'unsetMode')) {
                $a->unsetMode($mode);
            }
        }
        return $this;
    }
    /**
     * Get the current adapter, spawn if necessary, and call requested method on
     * it.
     * 
     * NOTE: It's assumed that the adapter implements it's own measure to throw
     * if the method is undefined
     *
     * @param string $method Method to invoke on adapter
     * @param array  $args   Method arguments
     * 
     * @return mixed   The result of the success adapter method call
     */
    public function __call($method, $args)
    {
        $rObj = $this->genResponseObject();
        $a = $this->getAdapter($rObj);
        $rObj->setResponseFormatType($a->getAdapterResponseFormatType());
        $a->setResponseObject($rObj);
        return call_user_func_array(
            array(
                $a,
                $method
            ),
            $args
        );
    }
}
?>