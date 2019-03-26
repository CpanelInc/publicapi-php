<?php
/**
 * Cpanel_Service_WHM
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
 * Whostmgr Service class
 * 
 * This class can be used by Cpanel_PublicAPI, another custom client, or as a
 * replacement to the legacy XML-API client class.
 *
 * @class     Cpanel_Service_WHM
 * @category  Cpanel
 * @package   Cpanel_Service
 * @author    David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright Copyright (c) 2011, cPanel, L.L.C., All rights Reserved. (http://cpanel.net) 
 * @license   http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version   0.1.0
 * @link      http://sdk.cpanel.net
 * @since     0.1.0
 */
class Cpanel_Service_WHM extends Cpanel_Service_Abstract
{
    /**
     * Default service adapter name
     */
    const ADAPTER_DEFAULT = 'whostmgr';
    /**
     * Constructor
     * 
     * @param arrays $optsArray Option configuration data
     * 
     * @return Cpanel_Service_WHM
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
        $this->listner->log('debug', "WHM object configured");
        $rObj = $this->genResponseObject();
        $a = $this->getAdapter($rObj);
        $this->initAdapter($a);
        $rObj->setResponseFormatType($a->getAdapterResponseFormatType());
        $a->setResponseObject($rObj);
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
        if ((strtolower($type) == parent::ADAPTER_WHM)
            || (strtolower($type) == 'whm')
        ) {
            $normalized = parent::ADAPTER_WHM;
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
     * @return Cpanel_Service_Adapter_WHMapi
     */
    protected function spawnAdapter($adapterType)
    {
        $c = 'Cpanel_Service_Adapter_WHMapi';
        switch ($adapterType) {
        case parent::ADAPTER_WHM:
            $c = 'Cpanel_Service_Adapter_WHMapi';
            break;
        }
        $adapter = new $c;
        if ($this->listner) {
            $adapter->setOptions(array('listner' => $this->listner));
        }
        return $adapter;
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
        return $a->makeQuery($uri, $formdata);
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