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


require_once 'Net\GameServerQuery\Protocol.php';


/**
 * GameSpy04 Protocol
 *
 * @category       Net
 * @package        Net_GameServerQuery
 * @author         Tom Buskens <ortega@php.net>
 * @version        $Revision$
 */
class Net_GameServerQuery_Protocol_GameSpy04 extends Net_GameServerQuery_Protocol
{
    /**
     * Rules packet
     *
     * @access    protected
     * @return    array      Array containing formatted server response
     */
    protected function _rules()
    {
        // Header
        $this->_getHeader();

        // Variable / value pairs
        while ($this->_match("([^\x00]+)\x00([^\x00]*)\x00")) {
            $this->_add($this->_result[1], $this->_result[2]);
        }

        return $this->_output;

    }


    /**
     * Player packet
     *
     * @access     protected
     * @return     array     Array containing formatted server response
     */
    protected function _players()
    {
        // Header
        $this->_getHeader();

        // Get values
        if (!$this->_getValues('player')) {
            return false;
        }

        // Get team info (same packet)
        $this->_team(true);

        return $this->_output;

    }

    /**
     * Team packet
     *
     * @access     protected
     * @param      bool  $from_players  True if packet was also contained player data.
     * @return     array Array containing formatted server response
     */
    protected function _team($from_players = false)
    {
        // Header
        if (!$from_players) {
            $this->_getHeader();
        }

        // Get values
        if (!$this->_getValues('team')) {
            return false;
        }

    }

    /**
     * Checks header
     *
     * @access     private
     * @return     bool      True if matched, false if not
     */
    private function _getHeader()
    {
        if ($this->_getPrefix(5) == "\x00NGSQ") {
            return true;
        }
        else {
            throw new Exception('Parsing error: header not matched');
            return false;
        }
    }

    /**
     * Gets variables according to a specific pattern
     *
     * @access     private
     * @param      string    $type     Variable type
     * @return     bool      True on success, false on pattern match failure
     */
    private function _getValues($type)
    {
        // Get number of sets
        if (!$this->_match("\x00(.)")) {
            throw new Exception('Parsing error');
        }

        // Convert byte to integer
        $count = $this->toInt($this->_result[1], 8);

        // Add count to output
        $this->_add($type . 'count', $count);

        // Get variable names, always start with an underscore
        $variables = array();

        while (true) {

            if (!$this->_match("_([^\x00]+)\x00")) {
                return false;
            }

            // Save variable name
            array_push($variables, $this->_result[1]);

            // Variable name sequence is ended with a second \x00
            if ($this->_match("\x00")) {
                break;
            }

        }

        // Get variable values
        $var_count = count($variables);

        // Loop through sets
        for ($i = 0; $i !== $count; $i++) {

            // Get values for each set
            for ($j = 0; $j !== $var_count; $j++) {

                if (!$this->_match("([^\x00]+)\x00")) {
                    return false;
                }

                // Add variables to output
                $this->_addPlayer($variables[$j], $this->_result[1]);

            }

        }

        return true;

    }

}

?>
