<?php
/**
 * Cpanel_Core_PriorityQueue
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
 * Custom class that brings together functionality provide by PHP 5.3's
 * SplObjectStorage and SplPriorityQueue
 *
 * Essentially this is a pure PHP implementation of those classes and will 
 * behave like the respective class methods
 * 
 * @class     Cpanel_Core_PriorityQueue
 * @category  Cpanel
 * @package   Cpanel_Core
 * @author    David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright Copyright (c) 2011, cPanel, L.L.C., All rights Reserved. (http://cpanel.net) 
 * @license   http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version   0.2.0
 * @link      http://sdk.cpanel.net
 * @since     0.1.0
 */
class Cpanel_Core_PriorityQueue
    implements Countable, Iterator, ArrayAccess, Serializable
{
    /**
     * Extract flag
     * Return stored object
     */
    const EXTR_DATA = 1;
    /**
     * Extract flag
     * Return associated data
     * Enter description here ...
     * @var unknown_type
     */
    const EXTR_PRIORITY = 2;
    /**
     * Extract flag
     * Return a wo element array containing object and associated data
     */
    const EXTR_BOTH = 3;
    private $_storage;
    private $_flags = Cpanel_Core_PriorityQueue::EXTR_DATA;
    /**
     * Constructor
     * 
     * @return Cpanel_Core_PriorityQueue
     */
    public function __construct()
    {
        $this->_storage = array();
        return $this;
    }
    /**
     * Store an object with an associated data element
     *
     * @param object $obj  The object to store
     * @param mixed  $data A string or array to associate with the object,
     *  useful for prioritizing the extraction of objects in queuing fashion.
     * 
     * @return void  
     */
    public function attach($obj, $data)
    {
        if (!is_object($obj)) {
            throw new Exception('First argument must be an object');
        }
        $key = spl_object_hash($obj);
        $this->_storage[$key] = array(
            'obj' => $obj,
            'inf' => $data
        );
    }
    /**
     * Check if an particular object is stored.
     *
     * @param object $obj Object to seek
     * 
     * @return bool   True if object is found, otherwise FALSE
     */
    public function contains($obj)
    {
        if (!is_object($obj)) {
            throw new Exception('First argument must be an object');
        }
        return (array_key_exists(spl_object_hash($obj), $this->_storage));
    }
    /**
     * Remove an object from storage.
     * 
     * @param object $obj Object to remove from storage
     * 
     * @return void  
     */
    public function detach($obj)
    {
        if (!is_object($obj)) {
            throw new Exception('First argument must be an object');
        }
        if ($this->contains($obj)) {
            unset($this->_storage[spl_object_hash($obj) ]);
        }
    }
    /**
     * Count how many objects are in storage
     * 
     * @return int The number of objects in storage
     */
    public function count()
    {
        return count($this->_storage);
    }
    /**
     * Prioritize objects in storage based on their associated data
     * 
     * @return void
     */
    private function _sort()
    {
        $tmp = $this->_storage;
        uasort(
            $tmp, array(
                $this,
                'compare'
            )
        );
        $this->_storage = $tmp;
    }
    /**
     * Iterator method. Fetch the current object from storage iterator
     * 
     * @see    Cpanel_Core_PriorityQueue::setFlags
     * 
     * @return mixed Content located at the current position of the storage
     *               iterator
     */
    public function current()
    {
        $ref = current($this->_storage);
        return $this->_returnStructure($ref);
    }
    /**
     * Iterator method. Fetch the current key of the storage iterator.
     * 
     * @return int Index of the storage iterator.
     */
    public function key()
    {
        if ($this->valid()) {
            $storageKeys = array_keys($this->_storage);
            return array_search(key($this->_storage), $storageKeys);
        }
        return false;
    }
    /**
     * Iterator method. Advance the storage iterator
     * 
     * @return void
     */
    public function next()
    {
        next($this->_storage);
    }
    /**
     * Iterator method. Move the storage iterator to the first index.
     * 
     * @return void
     */
    public function rewind()
    {
        reset($this->_storage);
    }
    /**
     * Iterator method. Determine if storage iterator is pointing to a valid
     * index
     * 
     * @return bool TRUE if iterator is at a valid location from which to 
     *              source content from storage, otherwise FALSE
     */
    public function valid()
    {
        return (current($this->_storage) !== false) ? true : false;
    }
    /**
     * Sets what content is returned by various functions
     * 
     * The method affects the following:
     *  Cpanel_Core_PriorityQueue::current
     *  Cpanel_Core_PriorityQueue::extrac
     *  Cpanel_Core_PriorityQueue::offsetGet.
     *  
     *  Use one of the following constants:
     *  Cpanel_Core_PriorityQueue::EXTR_DATA
     *  Cpanel_Core_PriorityQueue::EXTR_PRIORITY
     *  Cpanel_Core_PriorityQueue::EXTR_BOTH
     * 
     * @param int $flag Integer value associated with class constant
     * 
     * @return void
     */
    public function setExtractFlag($flag)
    {
        $this->_flags = $flag;
    }
    /**
     * Shift off the top most element from storage
     *
     * @see    Cpanel_Core_PriorityQueue::setFlags
     * 
     * @return mixed Content at top of storage 
     */
    public function extract()
    {
        $this->top();
        $ref = array_shift($this->_storage);
        return $this->_returnStructure($ref);
    }
    /**
     * Sort storage and move iterator to the first index of storage.
     * 
     * @return void
     */
    public function top()
    {
        $this->_sort();
        reset($this->_storage);
    }
    /**
     * Fetch associated data for current storage iterator position
     * 
     * @return mixed Associated data for current storage object, null if 
     *               iterator is at an invalid position
     */
    public function getInfo()
    {
        if ($this->valid()) {
            $ref = current($this->_storage);
            return $ref['inf'];
        }
        return null;
    }
    /**
     * Update associated data for object located at the current storage
     * iterator's position
     *
     * @param mixed $data String, Integer or Array to associate with current
     *                     object
     * 
     * @return bool  TRUE on success, otherwise FALSE
     */
    public function setInfo($data)
    {
        if ($this->valid() && !is_bool($data) && !is_object($data)) {
            $key = key($this->_storage);
            $this->_storage[$key]['inf'] = $data;
            return true;
        }
        return false;
    }
    /**
     * ArrayAccess method. Alias for Cpanel_Core_PriorityQueue::attach.
     * 
     * This method allows for attaching an object/data pair to an instance of
     * this class just like an array. i.e., $pq[$obj] = $priority;
     *
     * @param object $offset Object to store
     * @param mixed  $value  String, integer, or array to associate with object
     * 
     * @see    attach()
     * 
     * @return void     
     * @throws Exception If $offset is not defined 
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            throw new Exception('An object must be provided as an index');
        } else {
            $this->attach($offset, $value);
        }
    }
    /**
     * ArrayAccess method.  Alias for Cpanel_Core_PriorityQueue::contains.
     * 
     * This method allows an instance of this class to be treated as an array
     * for verifying an object is in storage.  i.e. array_key_exists($obj, $pq);
     * 
     * @param object $offset Object to seek in storage
     * 
     * @see    contains()
     * 
     * @return bool   TRUE if in storage, otherwise FALSE
     */
    public function offsetExists($offset)
    {
        return $this->contains($offset);
    }
    /**
     * ArrayAccess method. Alias for Cpanel_Core_PriorityQueue::detach
     * 
     * @param object $offset Object to remove from storage.
     * 
     * @return void  
     */
    public function offsetUnset($offset)
    {
        $this->detach($offset);
    }
    /**
     *ArrayAccess method.  Alias for Cpanel_Core_PriorityQueue::current
     * 
     * This method allows an instance of this object to be used as an array in 
     * a loop structure or direct "index" lookup.  i.e. $r = $pq[$obj];
     * 
     * @param object $offset Object whose contents to return
     * 
     * @see    setFlags()
     * @see    current()
     * 
     * @return mixed  Content stored related to the object provided, otherwise
     *                NULL
     */
    public function offsetGet($offset)
    {
        if (!$this->contains($offset)) {
            return null;
        }
        $ref = $this->_storage[spl_object_hash($offset) ];
        return $this->_returnStructure($ref);
    }
    /**
     * Fetchs the data subset of content located within a reference.
     * 
     * This method is responsible for data returned by various other methods in
     * this class and it's behavior is determined by the private property 
     * Cpanel_Core_PriorityQueue::$_flags and can be altered by the use of
     * Cpanel_Core_PriorityQueue::setFlags 
     * 
     * @param array $ref Array containing all content for a particular object
     *  sourced from storage
     * 
     * @see    setFlags()
     * 
     * @return mixed Object, array, string or integer from the $ref structure
     */
    private function _returnStructure($ref)
    {
        if (!is_array($ref)) {
            throw new Exception('Invalid internal reference');
        } elseif (!array_key_exists('obj', $ref) || !array_key_exists('inf', $ref)) {
            throw new Exception('Invalid internal object reference');
        }
        if ($this->_flags === Cpanel_Core_PriorityQueue::EXTR_DATA) {
            return $ref['obj'];
        } elseif ($this->_flags === Cpanel_Core_PriorityQueue::EXTR_PRIORITY) {
            return $ref['inf'];
        } elseif ($this->_flags === Cpanel_Core_PriorityQueue::EXTR_BOTH) {
            return array(
                'data' => $ref['obj'],
                'priority' => $ref['inf']
            );
        } else {
            return $ref['obj'];
        }
    }
    /**
     * Evaluate the difference of two given data points.
     * 
     * This method is essentially a collection of functions for performing an
     * aggregate strcmp.  Input values are expected to be strings, integers or
     * an one level deep array containing strings or integers. Return values are
     * similar to strcmp, i.e. 0, 1, -1
     * 
     * @param mixed $priority1 String, integer or one level array to quantify
     * @param mixed $priority2 String, integer or one level array to quantify
     * 
     * @internal
     *           <code>
     *           <?php
     *           $a1 = 1;
     *           $a2 = 2;
     *           $b1 = array(1,1);
     *           $b2 = array(1,2);
     *           $b3 - array(1);
     *           $c1 = array(1,1,1);
     *           $c2 = array(1,1,2);
     *           $c3 = array(1,1);
     *           $r = $pq->compare( $a1, $a2);  // $r = -1
     *           $r = $pq->compare( $b1, $b2);  // $r = -1
     *           $r = $pq->compare( $b1, $b3);  // $r = -1
     *           $r = $pq->compare( $a1, $b3);  // $r = 0
     *           $r = $pq->compare( $a2, $b3);  // $r = 1
     *           $r = $pq->compare( $c1, $c2);  // $r = -1
     *           $r = $pq->compare( $c1, $c3);  // $r = -1
     *           $r = $pq->compare( $b1, $c3);  // $r = 0
     *           ?>
     *           </code>
     * 
     * @return   int   Returns -1 if $priority1 is "bigger", 1 if it is "smaller"
     *                 and 0 if they are "equal"
     */
    public function compare($priority1, $priority2)
    {
        if (!function_exists('cmp_priority_queue')) {
            /**
             * General function that determines which comparison function to use
             * and altering the input respectively
             * 
             * @param mixed $a String, integer, or array
             * @param mixed $b String, integer, or array
             * 
             * @return int   Returns -1 if $a is "bigger", 1 if it is "smaller", 
             *               and 0 if they are "equal"
             */
            function cmp_priority_queue($a, $b)
            {
                if (is_object($a) || is_bool($a)) {
                    throw new Exception('Cannot sort objects');
                } elseif (is_object($b) || is_bool($b)) {
                    throw new Exception('Cannot sort booleans');
                }
                if (!is_array($a["inf"]) && !is_array($b["inf"])) {
                    return cmp_data_queue($a['inf'], $b['inf']);
                } elseif (!is_array($a["inf"]) && is_array($b["inf"])) {
                    return cmp_data_array_queue(
                        array($a["inf"]),
                        $b["inf"]
                    );
                } elseif (is_array($a["inf"]) && !is_array($b["inf"])) {
                    return cmp_data_array_queue(
                        $a["inf"],
                        array($b["inf"])
                    );
                } else { //they must both be arrays
                    return cmp_data_array_queue($a["inf"], $b["inf"]);
                }
            }
            /**
             * String comparison function
             * 
             * @param string $a String for comparision
             * @param string $b String for comparision
             * 
             * @return int Returns -1 if $a is "bigger", 1 if it is "smaller", 
             *  and 0 if they are "equal"
             */
            function cmp_data_queue($a, $b)
            {
                if ($a == $b) {
                    return 0;
                }
                return ($a < $b) ? -1 : 1;
            }
            /**
             * Array comparision function
             *
             * @param array $a Array for comparision
             * @param array $b Array for comparision
             * 
             * @return int Returns -1 if $a is "bigger", 1 if it is "smaller", 
             *  and 0 if they are "equal"
             */
            function cmp_data_array_queue($a, $b)
            {
                foreach ($a as $key => $value) {
                    $e = 0;
                    if (array_key_exists($key, $b)) {
                        $e = cmp_data_queue($a[$key], $b[$key]);
                    } else {
                        $e = - 1;
                    }
                    if ($e) {
                        break 1;
                    }
                }
                if ($e === 0 && count($b) > count($a)) {
                    return 1;
                }
                return $e;
            }
        }
        return cmp_priority_queue($priority1, $priority2);
    }
    /**
     * Serializable method. Serialize storage
     * 
     * This allows an internal storage to be serialized.  i.e.
     * $s = serialize($pq);
     * 
     * @return String representing the serialized storage container
     */
    public function serialize()
    {
        return serialize($this->_storage);
    }
    /**
     * Serializable method. Unserialize storage representation and replace the
     * interal container.
     * 
     * This allows an instance of this class to be unserialized.  i.e.
     * $pq = unserialize($s);
     * 
     * @param string $data Serialization string to be unserialized
     * 
     * @return Cpanel_Core_PriorityQueue
     */
    public function unserialize($data)
    {
        $this->_storage = unserialize($data);
        return $this;
    }
}
?>