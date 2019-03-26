<?php
/**
 * Cpanel_Service_Adapter_Liveapi
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
 * Service adapter for cPanel Service
 *
 * @class     Cpanel_Service_Adapter_Liveapi
 * @category  Cpanel
 * @package   Cpanel_Service
 * @author    David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright Copyright (c) 2011, cPanel, L.L.C., All rights Reserved. (http://cpanel.net) 
 * @license   http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version   0.1.0
 * @link      http://sdk.cpanel.net
 * @since     0.1.0
 */
class Cpanel_Service_Adapter_Liveapi extends Cpanel_Query_Live_Abstract
{
    /**
     * Default response format type
     */
    const DRFT = 'LiveJSON';
    
    /**
     * Default return mode
     */
    const DEFAULT_MODE = 1;
    /**
     * Legacy string return mode for certain legacy methods
     */
    const LEGACY_STRING_MODE = 2;
    /**
     * Current response format type
     */
    private $_adapterResponseFormatType;
    /**
     * Valid response format types for this service adapter
     */
    private $_validRFT = array(
        'LiveJSON',
    );
    /**
     * Constructor
     * 
     * Prepare the adapter for use by Service.
     * 
     * If $RFT is not passed the const Cpanel_Service_Adapter_Liveapi::DRFT will be
     * used when invoking {@link setAdapterResponseFormatType()} at 
     * instantiation
     * 
     * @param string $RFT Response format type
     * 
     * @return Cpanel_Service_Adapter_Liveapi
     */
    public function __construct($RFT = null)
    {
        parent::__construct();
        if ($RFT) {
            $this->_adapterResponseFormatType = $RFT;
            if (!in_array($RFT, $this->_validRFT)) {
                array_push($this->_validRFT, $RFT);
            }
        } else {
            $this->_adapterResponseFormatType = self::DRFT;
        }
        $this->mode = self::DEFAULT_MODE;
        return $this;
    }
    /**
     * Return the current response format type
     * 
     * @see   Cpanel_Query_Http_Abstract::getAdapterResponseFormatType()
     * 
     * @return string Response format type
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
     * Set behavior mode for adapter
     * 
     * @param string $mode Mode to set
     * 
     * @return void
     */
    public function setMode($mode)
    {
        if ($this->mode & ~$mode ) {
            $this->mode = $this->mode | $mode;
        }
    }
    /**
     * Unset a behavior mode for adapter
     * 
     * @param string $mode Mode to unset
     * 
     * @return void
     */
    public function unsetMode($mode)
    {
        if ($this->mode & $mode) {
            $this->mode = $this->mode & ~$mode;
        }
    }
    /**
     * Handle special modes where output is expected to be something other than
     * the default Cpanel_Query_Object
     * 
     * @param Cpanel_Query_Object $rObj Response object
     * 
     * @return mixed
     * @throws Exception If invalid input
     */
    private function _returnOutputMode($rObj)
    {
        if (! ($rObj instanceof Cpanel_Query_Object)) {
            throw new Exception('Invalid response object');
        }

        $r = $rObj;
        if ($rObj->validResponse()) {
            if ($this->mode & self::LEGACY_STRING_MODE) {
                $r = $rObj->getResponseParser()->getLegacyString($rObj);
            }
        } 
        
        return $r;
    }
    
    // TODO: add similar method to Cpanelapi and WHMapi1 adapters
    
    /**
     * Add a valid response format type to internal list
     * 
     * @param string $type New response format type
     * 
     * @return void
     */
    public function registerAdapterResponseFormatType($type)
    {
        if (is_string($type)) {
            array_push($this->_validRFT, $type);
        }
    }
    /**
     * Legacy method for fetching a value from the cPanel engine
     * 
     * NOTE: The legacy LivePHP client class would return an array
     * representation of the the server response.  The method will perform a
     * complete parse of the result as if a regular query method.  Thus the 
     * response should might backwards compatible, as opposed to cpanelprint.
     * 
     * @param string $var String, ExpVar, or a Dynamic UI construct
     * 
     * @see   Cpanel_Service_Adapter_Liveapi::cpanelprint()
     * @link  http://docs.cpanel.net/twiki/bin/view/DeveloperResources/ExpVarRef ExpVar
     * @link  http://docs.cpanel.net/twiki/bin/view/DeveloperResources/DynamicUIRef Dynamic UI Ref
     * 
     * @return Cpanel_Query_Object
     */
    public function fetch($var)
    {
        return $this->exec('<cpanel print="' . $var . '">');
    }
    /**
     * Method for querying API1
     * 
     * @param string $module API1 module to source
     * @param string $func   API1 function of $module to invoke
     * @param array  $args   Arguments for $function
     * 
     * @link   http://docs.cpanel.net/twiki/bin/view/ApiDocs/Api1/WebHome API1 Modules
     * 
     * @return Cpanel_Query_Object
     */
    public function api1($module, $func, $args = array())
    {
        return $this->makeQuery('exec', '1', $module, $func, $args);
    }
    /**
     * Method for querying API2
     * 
     * @param string $module API2 module to source
     * @param string $func   API2 function of $module to invoke
     * @param array  $args   Arguments for $function
     * 
     * @link   http://docs.cpanel.net/twiki/bin/view/ApiDocs/Api2/WebHome API2 Modules
     * 
     * @return Cpanel_Query_Object
     */
    public function api2($module, $func, $args = array())
    {
        return $this->makeQuery('exec', '2', $module, $func, $args);
    }
    /**
     * Method to invoke a cpanelif tag
     *
     * Passes an expression to cpaneld for evaluation; a special type of API1
     * tag.
     *
     * @param string $code String, ExpVar, or a Dynamic UI construct
     * 
     * @link  http://docs.cpanel.net/twiki/bin/view/DeveloperResources/ExpVarRef ExpVar
     * @link  http://docs.cpanel.net/twiki/bin/view/DeveloperResources/DynamicUIRef Dynamic UI Ref
     * 
     * @return Cpanel_Query_Object
     */
    public function cpanelif($code)
    {
        $rObj = $this->makeQuery('if', '1', 'if', 'if', $code);
        return $this->_returnOutputMode($rObj);
    }
    /**
     * Method to invode a cpanelfeature tag
     *
     * Determine if the effective user has the cPanel feature $feature; a
     * special type of API1 tag
     * 
     * @param string $feature Feature to query about
     * 
     * @return boolean Whether the effective user has $feature
     */
    public function cpanelfeature($feature)
    {
        $rObj = $this->makeQuery('feature', '1', 'feature', 'feature', $feature);
        return $this->_returnOutputMode($rObj); 
    }
    /**
     * Return the value of a string, cPvar, ExpVar, DynamicUIRef
     *
     * NOTE: The legacy LivePHP client class would return the string value that
     * was imbedded in the server response.  The method will perform a
     * complete parse of the result as if a regular query method.  Thus the 
     * response from this method IS NOT backwards compatible with the legacy
     * LivePHP class
     * 
     * @param string $var String, ExpVar, or a Dynamic UI construct
     * 
     * @link   http://docs.cpanel.net/twiki/bin/view/DeveloperResources/ExpVarRef ExpVar
     * @link   http://docs.cpanel.net/twiki/bin/view/DeveloperResources/DynamicUIRef Dynamic UI Ref
     * 
     * @return Cpanel_Query_Object
     */
    public function cpanelprint($var)
    {
        $rObj = $this->makeQuery('exec', '1', 'print', '', $var);
        return $this->_returnOutputMode($rObj); 
    }
    /**
     * Process a locale (language) key for the effective user's current locale
     * (language)
     *
     * It should be noted that this method of handling localization in cPanel 
     * is no longer supported, The cptext tag should be used instead.
     * 
     * NOTE: The legacy LivePHP client class would return the string value that
     * was imbedded in the server response.  The method will perform a
     * complete parse of the result as if a regular query method.  Thus the 
     * response from this method IS NOT backwards compatible with the legacy
     * LivePHP class
     *
     * @param string $key The key to cross-reference against the locale map.
     * 
     * @link   http://docs.cpanel.net/twiki/bin/view/CpanelLocale/ cPanel Locale
     * 
     * @return Cpanel_Query_Object
     */
    public function cpanellangprint($key)
    {
        $rObj = $this->makeQuery('exec', '1', 'langprint', '', $key);
        return $this->_returnOutputMode($rObj);
    }
    /**
     * Legacy method for executing a LivePHP API call
     *
     * In most cases, this method should not be used directly.
     * 
     * @param string $reqtype Cpanel action type: 'exec'|'feature'|'if'
     * @param string $version String value of the cPanel API call: '1'|'2'
     * @param string $module  cPanel API module name
     * @param string $func    cPanel API function name
     * @param array  $args    Arguments for the function call
     * 
     * @see    api1()
     * @see    api2()
     * @see    Cpanel_Query_Live_Abstract::makeQuery()
     * 
     * @return Cpanel_Query_Object
     */
    public function api($reqtype, $version, $module, $func, $args = array())
    {
        // TODO: consider trigger error if $args is not a array: a string
        //  is acceptable for non"exec" requests and "exec" w/module "print"
        //  furthermore, in the latter case, it just 'happens' to work with one
        //  element ordinal arrays due to internal cPanel module parsing
        return $this->makeQuery($reqtype, (string)$version, $module, $func, $args);
    }
    /**
     * Legacy method for closing the Live connection with cpsrvd
     *
     * @see    Cpanel_Query_Live_Abstract::closeCpanelHandle()
     * 
     * @return void
     */
    public function end()
    {
        $this->closeCpanelHandle();
    }
}
?>