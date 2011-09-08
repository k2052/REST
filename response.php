<?php

/**
 * REST Response
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
class REST_Response
{ 
  /**
   * CURL Properties.
   *
   * @var array
   **/
  static $properties = array(  
    'code'  => CURLINFO_HTTP_CODE,  
    'time'  => CURLINFO_TOTAL_TIME,  
    'length'=> CURLINFO_CONTENT_LENGTH_DOWNLOAD,  
    'type'  => CURLINFO_CONTENT_TYPE    
  );
  
  /**
   * CURL Properties.
   *
   * @var array
   **/
  var $id = null;
  
  /**
   * Response HTTP headers
   *
   * @var array
   **/
  var $headers = array();   
  
  /**
   * Response Content.
   *
   * @var string
   **/
  var $content = '';   
  
  /**
   * Response HTTP status code
   *
   * @var string
   **/
  var $status = ''; 
  
  /**
   * Response code e.g 404.
   *
   * @var array
   **/
  var $code = 0; 
  
  /**
   * Response type.
   *
   * @var mixed
   **/
  var $type;  
  
  /**
   * Response time.
   *
   * @var int
   **/
  var $time; 
  
  /**
   * Response length
   *
   * @var int
   **/
  var $length;  
  
  /**
   * Is there an error?
   *
   * @var mixed
   **/
  var $isError = CURLE_OK;
  
  /**
   * Error message.
   *
   * @var string
   **/
  var $error = '';  
  
  /**
   * Constructor.
   * 
   * @param mixed  $httpResponse The Curl http response
   * @param bool   $isError  Is there an error? 
   * @param string $error  Error message. 
   * @return void
   **/
  public function __construct($httpResponse, $isError = false, $error = '') 
  {
    list($this->headers, $this->content, $this->status) = self::parseHttpResponse($httpResponse);
    $this->isError = $isError;
    $this->error = $error;    
  }    
  
// ------------------------------------------------------------------------   
   
  /**
   * Is there an error?
   * 
   * @return bool
   **/     
  public function isError() 
  {
    return $this->isError == CURLE_OK ? false : $this->errno;
  }  

// ------------------------------------------------------------------------   

  /**
   * Parses an http response string.
   *  
   * @param string $string The Curl http response
   * @return bool
   **/  
  static function parseHttpResponse ($string) 
  {
    $headers = array();
    $status  = '';
    $content = '';
    $str     = strtok($string, "\n");
    $h       = null;       
    
    while ($str !== false) 
    {
      if ($h and trim($str) === '') {                
        $h = false;
        continue;   
      }
      if ($h !== false and false !== strpos($str, ':')) 
      {
        $h = true;
        list($headername, $headervalue) = explode(':', trim($str), 2);
        $headername = strtolower($headername);
        $headervalue = ltrim($headervalue);   
      
        if (isset($headers[$headername])) 
          $headers[$headername] .= ',' . $headervalue;
        else 
          $headers[$headername] = $headervalue;    
      }
      elseif ($h !== false and $status === '') 
        $status = $str;
      if ($h === false)
        $content .= $str."\n";    
      
      $str = strtok("\n");   
    }
    return array($headers, trim($content), $status);   
  } 
}