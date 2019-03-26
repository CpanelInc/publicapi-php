<?php
/**
 * Cpanel_Listner_Subject_Abstract
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
 * @version    0.1.0
 * @link       http://sdk.cpanel.net
 * @since      0.1.0
 */
/**
 * Abstract class for listner subjects
 * 
 * @class      Cpanel_Listner_Subject_Abstract
 * @category   Cpanel
 * @package    Cpanel_Listner
 * @subpackage Subject
 * @author     David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright  (c) 2011 cPanel, L.L.C., All rights Reserved. (http://cpanel.net) 
 * @license    http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version    0.1.0
 * @link       http://sdk.cpanel.net
 * @since      0.1.0
 */
abstract class Cpanel_Listner_Subject_Abstract implements SplSubject
{
    protected $observers;
    /**
     * Constructor
     * 
     * @return Cpanel_Listner_Subject_Abstract
     */
    public function __construct()
    {
        $this->observers = new Cpanel_Core_PriorityQueue();
        return $this;
    }
    /**
     * Attach an observer to this subject
     * 
     * Option $priority will order the observer within the queue stack. Lower
     * numbers have higher precedent.
     * 
     * @param SplObserver $obs      Observer instance to attach
     * @param int         $priority Integer reprentation from 1-n defining the
     *  order to notify the observer relative to other observers in the stack
     * 
     * @see    SplSubject::attach()
     * 
     * @return Cpanel_Listner_Subject_Abstract
     */
    public function attach(SplObserver $obs, $priority = null)
    {
        //validate priority
        $stored = $this->contains($obs);
        if (!$stored) {
            $priority = ($priority) ? $priority : 10;
            $this->observers->attach($obs, $priority);
        } elseif ($priority) {
            $this->changePriority($obs, $priority);
        }
        return $this;
    }
    /**
     * Check if an observer instance is stored in the stack
     * 
     * @param SplObserver $obj Observer to search for.
     * 
     * @return bool
     */
    public function contains($obj)
    {
        return ($this->observers->contains($obj)) ? true : false;
    }
    /**
     * Modify the position of an observer within the queue stack.
     * 
     * This will alter the order inwhich the observer will be notified relative
     * to any other observers in the stack
     *
     * @param SplObserver $obs      Observer to reorder
     * @param int         $priority Integer representation from 1-n defining the
     *  order to notify the observer relative to other observers in the stack
     * 
     * @return Cpanel_Listner_Subject_Abstract
     * @throws Exception If priority is an empty array 
     *  with elements other than numeric strings
     * @throws Exception If priority is a string that is not numeric
     */
    public function changePriority(SplObserver $obs, $priority = '')
    {
        if (is_array($priority)) {
            if (empty($priority)) {
                THROW new Exception(
                    'A priority must be specified for "changePriority"'
                );
            }
            foreach ($priority as $value) {
                if (!is_numeric($value)) {
                    THROW new Exception(
                        'A priority must be specified for "changePriority"'
                    );
                }
            }
        } elseif (!is_numeric($priority)) {
            THROW new Exception(
                'A priority must be specified for "changePriority"'
            );
        }
        $this->observers[$obs] = $priority;
        return $this;
    }
    /**
     * Remove an observer from the queue stack
     * 
     * @param SplObserver $obs Observer to remove
     * 
     * @see    SplSubject::detach()
     * 
     * @return Cpanel_Listner_Subject_Abstract
     */
    public function detach(SplObserver $obs)
    {
        if ($this->contains($obs)) {
            $this->observers->detach($obs);
        }
        return $this;
    }
    /**
     * Trigger update on all observers in queue stack.
     * 
     * This will move the queue pointer to the top of heap an begin calling the
     * "update()" function on each observer as their priority is previously
     * defined.  "update" will be passed this object.  Therefore any information
     * necessary to an observer should be stored appropriately within this
     * subject instance.
     * 
     * @see    SplSubject::notify()
     * 
     * @return array An array containing an elements, sequentially, from the
     *               output of each observer's "update()" method. 
     */
    public function notify()
    {
        $this->observers->top();
        $called = array();
        while ($this->observers->valid()) {
            $observer = $this->observers->current();
            $updated = $observer->update($this);
            if ($updated) {
                $called[] = $updated;
            }
            $this->observers->next();
        }
        return $called;
    }
}
?>