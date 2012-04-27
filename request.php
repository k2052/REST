<?php

/**
 * REST Request
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
class REST_Request
{   
  /**
   * CURL Options.
   *
   * @var array
   **/
  var $curlOptions = array();      
  
  /**
   * Name of the current method, e.g GET, POST etc.
   *
   * @var string
   **/    
  var $method = 'GET';    
  
  /**
   * Name of the current protocol, e.g HTTP, HTTPS etc.
   *
   * @var string
   **/    
  var $protocol = 'http';
  
  /**
   * Hostname/domain.
   *
   * @var string
   **/    
  var $host = null;
  
  /**
   * Port e.g 80:
   *
   * @var int
   **/    
  var $port = 80;  
  
  /**
   * URL e.g /api/books/:
   *
   * @var int
   **/    
  var $url = '/';    
  
  /**
   * Body.
   *
   * @var mixed
   **/
  var $body;
  
  /**
   * Headers
   *
   * @var array.
   **/ 
  var $headers = array(); 
  
  /**
   * Constructor.
   * 
   * @param array $curlOptions CURL Options.
   * @return void
   **/
  public function __construct($curlOptions = array())
  {
    $this->curlOptions = $curlOptions;
  } 
  
// ------------------------------------------------------------------------  
   
  /**
   * Set a CURL specific option
   *   
   * @param string $k Key
   * @param string $v Value
   * @return REST_Request
   */
  public function setCurlOption($k, $v)
  {
    $this->curlOptions[$k] = $v;
    return $this;  
  }
  
// ------------------------------------------------------------------------  

  /**
   * Set CURL options 
   *   
   * @param array $a Array of of CURL options in the form of key,value.
   * @return REST_Request
   */
  public function setCurlOptions($a)
  {
    foreach($a as $k => $v)
      $this->setCurlOption($k, $v);
    return $this; 
  } 
  
// ------------------------------------------------------------------------   
   
  /**   
   * Sets the current protocol.
   *
   * @param string $protocol The protocol e.g http  
   * @return REST_Request
   */
  public function setProtocol($protocol)
  {
    if (in_array(strtolower($protocol), array('http', 'https')))
      $this->protocol = $protocol;
    return $this;    
  } 
  
// ------------------------------------------------------------------------   
  
  /**
   * Sets the Hostname
   *
   * @param string $host The hostname
   * @return REST_Request
   */
  public function setHost($host)
  {
    $this->host = $host;
    return $this;  
  }
  
// ------------------------------------------------------------------------   
  
  /**
   * Sets the Port  
   *
   * @param int $port e
   * @return REST_Request
   */
  public function setPort($port)
  {
    $this->port = $port;
    return $this;    
  } 
  
// ------------------------------------------------------------------------   
  
  /**
   * Sets the method.
   * 
   * @param string $method The method e.g GET.
   * @return REST_Request
   */
  public function setMethod($method)
  {
    $this->method = strtoupper($method);
    return $this;  
  } 
  
// ------------------------------------------------------------------------   

  /**
   * Sets the URL
   * 
   * @param string $url The url e.g /bob/api.
   * @return REST_Request 
   */
  public function setURL($url)
  {
    $this->url = $url;
    return $this; 
  }   
   
// ------------------------------------------------------------------------   

  /**
   * Sets the body.
   * 
   * @param string $body
   * @return REST_Request 
   */   
  public function setBody($body)
  {
    $this->body = $body;
    return $this;   
  }   
  
  
// ------------------------------------------------------------------------     

  /**
   * Add Http Header. 
   *
   * @param string $k Key
   * @param string $v Value
   * @return REST_Request
   */
  public function setHeader($k, $v)
  {    
    $this->headers[$k] = $v;
    return $this;   
  }

// ------------------------------------------------------------------------     

  /**
   * Get HTTP header.
   *
   * @param string $k Name of the header.
   * @return string
   */  
  public function getHeader($k)
  {
    return $this->headers[$k];   
  }  

// ------------------------------------------------------------------------     

  /**
   * Get HTTP headers.
   *
   * @return array $this->headers
   */ 
  public function getHeaders()
  {
    return $this->headers;   
  } 
  
// ------------------------------------------------------------------------   
      
  /**
   * Setup a HTTP proxy  
   *
   * @param string $proxy The proxy string. 
   * @return REST_Request
   */
  public function setHttpProxy($proxy)
  {
    $proxy = (string)trim(str_replace('http://','',$proxy));
    if (!empty($proxy)) {
      list($host, $port) = explode(':',$proxy);
      $this->setCurlOption(CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
      $this->setCurlOption(CURLOPT_PROXY,     $host);
      $this->setCurlOption(CURLOPT_PROXYPORT, $port);  
    }
    return $this;    
  }  
  
// ------------------------------------------------------------------------   
  
  /**
   * Convert REST_Request data to CURL format   
   *
   * @return array
   */
  public function toCurl()
  {
    $options = $this->curlOptions + array(
      CURLOPT_PORT           => $this->port,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FOLLOWLOCATION => false,
      CURLOPT_HEADER         => true,    
    );
    
    $options[CURLOPT_URL]           = $this->protocol.'://'.$this->host.':'.$this->port.$this->url;
    $options[CURLOPT_CUSTOMREQUEST] = $this->method;        
    
    if (!is_null($this->body) and $this->body !== '')
      $options[CURLOPT_POSTFIELDS] = $this->body;
    
    if ($this->method === 'POST') 
      $options[CURLOPT_POST] = true;
    
    return $options;
  }        
   
// ------------------------------------------------------------------------   
  
  /**  
   *  Routes calls to the this. Allows for pretty syntax like $request->get();   
   *   
   * @param string $method Method name.
   * @param mixed $arguments
   * @return REST_Request
   */
  public function __call($method, $arguments) 
  {
    if (count($arguments) === 0)
      return trigger_error(sprintf('%s::%s() expects at least 1 parameter, 0 given', __CLASS__, $method), E_USER_WARNING);

    if (!is_string($arguments[0]))
      return trigger_error(sprintf('%s::%s() expects parameter 1 to be string, %s given', __CLASS__, $method, gettype($arguments[0])), E_USER_WARNING);

    $url = trim($arguments[0]);
    if ($url === '')
      return trigger_error(sprintf('%s::%s() expects parameter 1 to be not empty', __CLASS__, $method), E_USER_WARNING);

    $this->setURL($url);
    $this->setMethod($method);
    $this->setBody(isset($arguments[1]) ? $arguments[1] : null);          
      
    return $this;
  }
}