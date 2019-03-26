<?php
/**
 * Cpanel
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
 * @package   Cpanel_Util
 * @author    David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright Copyright (c) 2011, cPanel, L.L.C., All rights Reserved. (http://cpanel.net) 
 * @license   http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version   0.1.0
 * @link      http://sdk.cpanel.net
 * @since     0.1.0
 */
if (!function_exists('cpanel_autoload')) {
    $cpinstalldir = realpath(dirname(__FILE__) . '/../../');
    set_include_path($cpinstalldir . PATH_SEPARATOR . get_include_path());
    define('CPANEL_LIB_PATH', $cpinstalldir . DIRECTORY_SEPARATOR . 'Cpanel');
    /**
     * Cpanel autoload function
     * 
     * @param string $classname Class to instantiate
     * 
     * @return void
     */
    function cpanel_autoload($classname)
    {
        if (strpos($classname, 'Cpanel_') === 0) {
            $filename = str_replace('_', '/', $classname) . '.php';
            if (file_exists($filename)) {
                include_once realpath($filename);
            }
            $paths = explode(PATH_SEPARATOR, get_include_path());
            foreach ($paths as $path) {
                $file = $path . DIRECTORY_SEPARATOR . $filename;
                if (file_exists($file)) {
                    include_once realpath($file);
                }
            }
        }
    }
    spl_autoload_register('cpanel_autoload');
}
?>