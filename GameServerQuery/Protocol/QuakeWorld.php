<?php
// +----------------------------------------------------------------------+
// | PHP version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Aidan Lister <aidan@php.net>                                |
// |          Tom Buskens <ortega@php.net>                                |
// +----------------------------------------------------------------------+
//
// $Id$


/**
 * QuakeWorld Protocol
 *
 * @category       Net
 * @package        Net_GameServerQuery
 * @author         Aidan Lister <aidan@php.net>
 * @version        $Revision$
 */
class Net_GameServerQuery_Protocol_QuakeWorld extends Net_GameServerQuery_Protocol
{

    protected function _status ()
    {
        // Header
        if (!$this->_match("\xFF\xFF\xFF\xFFn")) {
            return false;
        }

        while ($this->_match("\\\\([^\\\\]*)\\\\([^\\\\]*)")) {
            $this->_addVar($this->_result[1], $this->_result[2]);
        }

        return $this->_output;

    }
    
    protected function _players ()
    {
       

    }

    protected function _rules ()
    {
       

    }
}

?>