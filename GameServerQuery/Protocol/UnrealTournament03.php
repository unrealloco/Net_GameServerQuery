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
 * Unreal Tournament 2003 protocol
 *
 * @category       Net
 * @package        Net_GameServerQuery
 * @author         Tom Buskens <ortega@php.net>
 * @version        $Revision$
 */
class Net_GameServerQuery_Protocol_UnrealTournament03 extends Net_GameServerQuery_Protocol
{

    /**
     * Players packet
     *
     * @access  protected
     * @return  array      Array containing formatted server response
     */
    protected function _players()
    {
        // Packet id
        if (!$this->_match("\x02")) {
            throw new Exception('Parsing error');
        }

        // Players
        while ($this->_match("(.{4})(.)")) {

            // Player id
            $this->_add('playerid', $this->toInt($this->_result[1], 32));

            // Get player name length and create expression
            $name_length = $this->toInt($this->_result[2]) - 1;
            $expr = sprintf("(.{%d})\x00(.{4})(.{4})(.{4})", $name_length);

            // Match expression
            if (!$this->_match($expr)) {
                throw new Exception('Parsing error');
            }

            $this->_addPlayer('name',  $this->_result[1]);
            $this->_addPlayer('ping',  $this->toInt($this->_result[2], 32));
            $this->_addPlayer('score', $this->toInt($this->_result[2], 32));
            $this->_addPlayer('team',  $this->toInt($this->_result[2], 32));
        }

        return $this->_output;
    }


    /**
     * Rules packet
     *
     * @access  protected
     * @return  array      Array containing formatted server response
     */
    protected function _rules()
    {
        // Packet id
        if (!$this->_match("\x01")) {
            throw new Exception('Parsing error');
        }

        // Var / value set.
        while ($this->_match(".")) {

            // Create expression using result of previous match to set the string length
            $expr = sprintf("(.{%d})\x00(.)", ($this->toInt($this->_result[0]) - 1));

            // Get variable name
            if ($this->_match($expr)) {
                $name = $this->_result[1];

                // Create expression using result of previous match to set string length
                $expr = sprintf("(.{%d})\x00", ($this->toInt($this->_result[2]) - 1));

                // Get variable value
                if ($this->_match($expr)) {
                    $this->_add($name, $this->_result[1]);
                }
                else {
                    throw new Exception('Parsing error');
                }
            }
            else {
                throw new Exception('Parsing error');
            }

        }
    }


    /**
     * Status packet
     *
     * @access  protected
     * @return  array      Array containing formatted server response
     */
    protected function _status()
    {
        // Get some 32 bit variables
        if (!$this->_match("\x00(.{4})\x00(.{4})(.{4})([^\x00]+)\x00(.)")) {
            throw new Exception('Parsing error');
        }

        $this->_add('gameport',  $this->toInt($this->_result[2], 32));
        $this->_add('queryport', $this->toInt($this->_result[3], 32));
        $this->_add('hostname',  $this->_result[4]);

        // Create expression using result of previous match to set string length
        $expr = sprintf("(.{%d})(.)", ($this->toInt($this->_result[5]) - 1));
        if (!$this->_match($expr)) {
            throw new Exception('Parsing error');
        }

        $this->_add('map', $this->_result[1]);

        // Create expression using result of previous match to set string length
        $expr = sprintf("(.{%d})(.{4})(.{4})", ($this->toInt($this->_result[2]) - 1));
        if (!$this->_match($expr)) {
            throw new Exception('Parsing error');
        }

        $this->_add('gametype',   $this->_result[1]);
        $this->_add('players',    $this->toInt($this->_result[2], 32));
        $this->_add('maxplayers', $this->toInt($this->_result[3], 32));

        return $this->_output;

    }
}
?>
