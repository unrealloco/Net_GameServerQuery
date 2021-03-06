<?php
/**
 * PEAR :: Net_GameServerQuery
 *
 * PHP version 4
 *
 * Copyright (c) 1997-2004 The PHP Group
 *
 * This source file is subject to version 3.0 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available at through the world-wide-web at
 * http://www.php.net/license/3_0.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * @category Net
 * @package  Net_GameServerQuery
 * @author   Aidan Lister <aidan@php.net>  
 * @author   Tom Buskens <ortega@php.net>
 * @license  PHP 3.0 http://www.php.net/license/3_0.txt
 * @version  CVS: $Id$
 * @link     http://pear.php.net/package/Net_GameServerQuery
 */


/**
 * Process the server responses
 *
 * Loads the protocol drivers to parse packets, and runs
 * the normalistion methods
 *
 * @category Net
 * @package  Net_GameServerQuery
 * @author   Aidan Lister <aidan@php.net>  
 * @author   Tom Buskens <ortega@php.net>
 * @license  PHP 3.0 http://www.php.net/license/3_0.txt
 * @link     http://pear.php.net/package/Net_GameServerQuery
 */
class Net_GameServerQuery_Process
{
    
    /**
     * Hold an instance of the config class
     *
     * @var         object
     */
    private $_gamedata;
    
    /**
     * Array of options
     *
     * @var         object
     */
    private $_config;
    
    /**
     * Array holding all the loaded protocol objects
     *
     * @var         array
     */
    private $_protocols = array();


    /**
     * Constructor
     */
    public function __construct($gamedata, $config)
    {
        $this->_gamedata  = $gamedata;
        $this->_config    = $config;
    }
 
    
    /**
     * Factory for including protocols
     */
    static public function &factory($protocol)
    {
        $filename   = NET_GAMESERVERQUERY_BASE . 'Protocol/' . $protocol . '.php';
        $classname  = 'Net_GameServerQuery_Protocol_' .  $protocol;

        if (file_exists($filename)) {
            include_once $filename;
            return new $classname;
        }

        throw new DriverNotFoundException($protocol);
    }

    
    /**
     * Batch process all the results
     *
     * @param array $results Query results
     *
     * @return      array       Processed results
     */
    public function batch($results)
    {
        $processed = array();
        foreach ($results as $key => $result) {
            $processed[$key] = $this->process($result);
        }

        return $processed;
    }


    /**
     * Process a single result
     *
     * @param array $results Query result
     *
     * @return      array       Processed result
     */
    public function process($result)
    {
        // No reply means no parsing
        if ($result['response'] === false) {
            return false;
        }

        // Load the protocol file into cache
        if (!key_exists($result['protocol'], $this->_protocols)) {
            $classname = self::factory($result['protocol']);
            $this->_protocols[$result['protocol']] = new $classname;
        }
        
        // Parse the response
        $protocol =& $this->_protocols[$result['protocol']];
        $response = $result['response'];
        $data     = $protocol->parse($result['packetname'], $response, $this->_config);

        // Normalise the response
        if ($this->_config->getOption('normalise') === true) {
            $data = $this->normalise($data, $result['protocol'], $result['flag']);
        }
        
        return $data;
    }

    
    /**
     * Normalise result arrays
     *
     * This only normalises the status array
     *
     * @param array  $data     Server data
     * @param string $protocol Protocol name
     * @param string $flag     Query flag
     */
    public function normalise($data, $protocol, $flag)
    {
        if ($flag !== 'status') {
            return $data;
        }

        $normalkeys = $this->_gamedata->getProtocolNormals();
        $gamekeys = $this->_gamedata->getProtocolNormals($protocol);

        // If no normal keys are set return everything
        if (empty($normalkeys)) { 
            return $data;
        }

        $newdata = array();
        for ($i = 0, $ii = count($normalkeys); $i < $ii; $i++) {  
            if ($gamekeys[$i] === false) {
                $value = false;
            } else {
                $value = isset($data[$gamekeys[$i]]) ? $data[$gamekeys[$i]] : false;
            }
            
            $newdata[$normalkeys[$i]] = $value;
        }

        return $newdata;
    }

}

?>
