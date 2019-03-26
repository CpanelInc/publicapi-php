<?php
/**
 * Cpanel_Query_Live_Interface
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
 * @package    Cpanel_Query
 * @subpackage Live
 * @author     David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright  (c) 2011 cPanel, L.L.C., All rights Reserved. (http://cpanel.net) 
 * @license    http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version    0.1.0
 * @link       http://sdk.cpanel.net
 * @since      0.1.0
 */
/**
 * Interface for all Live query classes
 *
 * @class      Cpanel_Query_Live_Interface
 * @category   Cpanel
 * @package    Cpanel_Query
 * @subpackage Live
 * @author     David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright  (c) 2011 cPanel, L.L.C., All rights Reserved. (http://cpanel.net) 
 * @license    http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version    0.1.0
 * @link       http://sdk.cpanel.net
 * @since      0.1.0
 */
interface Cpanel_Query_Live_Interface
{
    /**
     * Create a file handle attached to a socket file, spawned by cpsrvd for the
     * current running process
     * 
     * @return bool value
     */
    function openCpanelHandle();
    /**
     * Perform a local, 'live' query over an open socket spawned by cpsrvd
     * 
     * @param string $code        Raw string to send to cpsrvd
     * @param int    $skip_return Force a read, after writing, on the socket
     *  file handle, but discard the response and do not return response object
     * 
     * @return Cpanel_Query_Object|void if $skipReturn is set to 1, null
     *  will be returned 
     */
    function exec($code, $skip_return = 0);
    /**
     * Send shutdown signal to cpsrvd and gracefully close the socket file
     * handle
     * 
     * @return void
     */
    function closeCpanelHandle();
}
?>