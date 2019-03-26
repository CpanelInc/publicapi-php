<?php
/**
 * Cpanel_Listner_Subject_Logger
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
 * @category   Cpanel
 * @package    Cpanel_Listner
 * @subpackage Subject
 * @author     David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright  (c) 2011 cPanel, L.L.C., All rights Reserved. (http://cpanel.net) 
 * @license    http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version    0.2.0
 * @link       http://sdk.cpanel.net
 * @since      0.1.0
 */
/**
 * Subject class for logging
 *
 * @class      Cpanel_Listner_Subject_Logger
 * @category   Cpanel
 * @package    Cpanel_Listner
 * @subpackage Subject
 * @author     David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright  (c) 2011 cPanel, L.L.C., All rights Reserved. (http://cpanel.net) 
 * @license    http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version    0.2.0
 * @link       http://sdk.cpanel.net
 * @since      0.1.0
 */
class Cpanel_Listner_Subject_Logger extends Cpanel_Listner_Subject_Abstract
{
    /**
     * Store for relaying log messages
     */
    protected $log = null;
    /**
     * Verbosity level for observers to honor
     */
    protected $debugLevel;
    /**
     * Constructor
     * 
     * @param string $level Verbosity level for observers to honor
     * 
     * @return Cpanel_Listner_Subject_Logger
     */
    public function __construct($level = 'std')
    {
        parent::__construct();
        $this->debugLevel = $level;
        return $this;
    }
    /**
     * Send log message to all log observers
     * 
     * @param string $type     Type of log message
     * @param string $msg      Message string
     * @param bool   $flushmsg Flush internal log storage immediately following
     *  notification to observers.
     * 
     * @return void
     */
    public function log($type, $msg, $flushmsg = true)
    {
        $this->log[$type] = $msg;
        $this->notify();
        if ($flushmsg) {
            unset($this->log[$type]);
        }
    }
    /**
     * Retrieve log messages
     * 
     * @return array Array whose key is a message type and value is a message
     */
    public function getLog()
    {
        return $this->log;
    }
    /**
     * Retrieve verbosity level, as set by this subject
     * 
     * @return string
     */
    public function getDebugLevel()
    {
        return $this->debugLevel;
    }
    /**
     * Set verbosity level for which all attached observers must honor
     * 
     * @param string $level Verbosity level to set
     * 
     * @return void  
     */
    public function setDebugLevel($level)
    {
        $this->debugLevel = $level;
        $this->notify();
    }
}
?>