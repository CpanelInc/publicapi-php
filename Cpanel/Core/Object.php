<?php
/**
 * Cpanel_Core_Object
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
 * @package   Cpanel_Core
 * @author    David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright Copyright (c) 2011, cPanel, L.L.C., All rights Reserved. (http://cpanel.net) 
 * @license   http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version   0.2.0
 * @link      http://sdk.cpanel.net
 * @since     0.1.0
 */
/**
 * Base class for any Cpanel class to inherit from.
 * 
 * This class is a container class with accessors to the internal storage.  The
 * internal stoeage container will store data as a Cpanel_Core_Object
 * such that it the parent object can continuely recure into the data structure.
 *
 * @class     Cpanel_Core_Object
 * @category  Cpanel
 * @package   Cpanel_Core
 * @author    David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright Copyright (c) 2011, cPanel, L.L.C., All rights Reserved. (http://cpanel.net) 
 * @license   http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version   0.2.0
 * @link      http://sdk.cpanel.net
 * @since     0.1.0
 */
class Cpanel_Core_Object implements IteratorAggregate, Countable
{
    /**
     * internal storage location
     */
    protected $dataContainer;
    /**
     * Constructor
     * 
     * @param array $optsArray Data to store internally
     * 
     * @return void 
     */
    public function __construct($optsArray = array())
    {
        $this->dataContainer = new ArrayObject(array());
        if (!empty($optsArray)) {
            $this->setOptions($optsArray);
        }
    }
    /**
     * IteratorAggregate required method
     * 
     * @return ArrayIterator Internal data with an iterable object
     */
    public function getIterator()
    {
        return new ArrayIterator($this->dataContainer);
    }

    public function count() {
        return $this->getIterator()->count();
    }

    /**
     * Accessor for storing data internally
     * 
     * @param array $optsArray Array of key/value parses to store internally
     * @param bool  $override  Override any previous stored data. Default is true
     * 
     * @return Cpanel_Core_Object
     */
    public function setOptions($optsArray, $override = true)
    {
        if (!is_array($optsArray) && !is_object($optsArray)) {
            throw new Exception(
                'setOptions() must receive an iterable variable type.'
            );
        }
        if ($this->dataContainer === null) {
            THROW new Exception(
                'data is empty. Most likely this class was extended without '
                .'"parent::__construct()"'
            );
        }
        if (count($optsArray) == 0) {
            return $this;
        } elseif (is_array($optsArray)) {
            foreach ($optsArray as $key => $value) {
                if (($override && array_key_exists($key, $this->dataContainer))
                    || (!array_key_exists($key, $this->dataContainer))
                ) {
                    if (is_array($value)) {
                        $this->dataContainer[$key] = new Cpanel_Core_Object($value);
                    } else {
                        $this->dataContainer[$key] = $value;
                    }
                }
            }
        } elseif ($optsArray instanceof Cpanel_Core_Object) {
            $newData = $optsArray->getAllDataRecursively();
            if ($override) {
                $this->dataContainer->exchangeArray(
                    array_replace_recursive(
                        $this->getAllDataRecursively(),
                        $newData
                    )
                );
            } else {
                $this->dataContainer->exchangeArray(
                    array_replace_recursive(
                        $newData,
                        $this->getAllDataRecursively()
                    )
                );
            }
            //}elseif(is_object($optsArray)){ //not supported ATM
            //some fallback to get_object_vars or
            // implement an iterable if
            
        }
        return $this;
    }
    /**
     * Accessor method for retrieving internal data
     * 
     * @param string $key Key to search for within data store
     * 
     * @return mixed|void Value if $key exists, otherwise NULL
     */
    public function getOption($key)
    {
        if ($this->dataContainer->offsetExists($key)) {
            return $this->dataContainer[$key];
        }
        return;
    }
    /**
     * Retrieve internal data storage object
     * 
     * @return ArrayObject
     */
    public function getAllData()
    {
        return $this->dataContainer;
    }
    /**
     * Retreive a deeply nested array structure representation of data store
     * 
     * @return array Array representation of internal data object storage
     */
    public function getAllDataRecursively()
    {
        $dataContainer = $this->dataContainer;
        if ($dataContainer instanceof ArrayObject) {
            foreach ($dataContainer as $key => $value) {
                if ($value instanceof Cpanel_Core_Object) {
                    $value = $value->getAllDataRecursively();
                }
                $dataContainer[$key] = $value;
            }
            $dataContainer = $dataContainer->getArrayCopy();
        }
        return $dataContainer;
    }
    /**
     * Magic get accessor
     * 
     * @param string $key Key to search for in internal data storage
     * 
     * @see    getOption()
     * 
     * @return mixed|void Value of $key if it exists, otherwise null
     */
    public function __get($key)
    {
        return $this->getOption($key);
    }
    /**
     * Magic set accessor
     * 
     * @param string $key   Key portion of element to store
     * @param mixed  $value Value portion of element to store
     * 
     * @see    setOptions()
     * 
     * @return void  
     */
    public function __set($key, $value)
    {
        $this->setOptions(
            array(
                $key => $value
            )
        );
    }
}
?>