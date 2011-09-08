<?php

/**
 * REST CLient.
 *
 * @note Inspired by and parts of this code borrowed from: https://github.com/touv/rest_client 
 *    
 * @package   REST
 * @version   1.0 Beta
 * @author    Ken Erickson AKA Bookworm http://bookwormproductions.net   
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @author    Stéphane Gully <stephane.gully@gmail.com>
 * @copyright Copyright 2009 - 2011 Design BreakDown, LLC.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2       
 */
class REST_SimpleClient
{ 
  /**
   * Holds the request object.
   *
   * @var object
   **/
  var $request;
  
  /**
   * Holds the client object.
   *
   * @var object
   **/
  var $client; 
  
  /**
   * Constructor.
   * 
   * @param string $host Name of the host.
   * @param string $port 
   * @param array  $options CURL Options.
   * @return void
   **/
  public function __construct($host = 'localhost', $port = 80, $options = array())
  {
    $this->request = new REST_Request();
    $this->request->setProtocol('http');
    $this->request->host = $host; 
    $this->request->port = $port;
    $this->request->setCurlOptions($options);  
    
    $this->client = REST_Client::factory('sync',  array('verbose' => false));
  }  
  
// ------------------------------------------------------------------------
  
  /**
   * Destructor.
   * 
   * @return void
   **/
  public function __destruct()
  {
    $this->request = null;
    $this->client  = null;    
  }    
  
// ------------------------------------------------------------------------

  /**
   * Routes calls to the request object. Allows for pretty syntax like $request->get(); 
   *   
   * @param string $method Method name.
   * @param mixed $arguments 
   * @return void
   **/ 
  public function __call($method, $arguments)
  {
    if (count($arguments) === 0)
      return trigger_error(sprintf('REST_SimpleClient::%s() expects at least 1 parameter, 0 given', $method), E_USER_WARNING);

    if (!is_string($arguments[0]))
      return trigger_error(sprintf('REST_SimpleClient::%s() expects parameter 1 to be string, %s given', $method, gettype($arguments[0])), E_USER_WARNING);

    $url = trim($arguments[0]);
    if ($url === '')
      return trigger_error(sprintf('REST_SimpleClient::%s() expects parameter 1 to be not empty', $method), E_USER_WARNING);

    $this->request
      ->setMethod($method)
      ->setURL($url)
      ->setBody(isset($arguments[1]) ? $arguments[1] : null);

    $this->client->fire($this->request);
    return $this->client->fetch();  
  }  
  
  
// ------------------------------------------------------------------------    

  /**
   * Closes the CURL handle.       
   *
   */
  public function close()
  {
    curl_close($this->client->handle);
  } 
  
// ------------------------------------------------------------------------
    
  /**
   * Setup a HTTP proxy        
   *
   * @return REST_SimpleClient
   */
  public function setHttpProxy($proxy)
  {
    $this->request->setHttpProxy($proxy);
    return $this;       
  }
}