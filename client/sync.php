<?php   

/**
 * Synchronous REST CLient.
 *     
 * @package   REST
 * @version   1.0 Beta
 * @author    Ken Erickson AKA Bookworm http://bookwormproductions.net   
 * @author    Nicolas Thouvenin <nthouvenin@gmail.com>
 * @author    Stéphane Gully <stephane.gully@gmail.com>
 * @copyright Copyright 2009 - 2011 Design BreakDown, LLC.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2       
 */
class REST_Client_Sync extends REST_Client
{
  /**
   * Options.
   *
   * @var array
   **/   
  var $options = array(
      'queue_size'  => 1,
      'verbose'     => null,
  );
  
  /**
   * A reference to the curl request.
   *
   * @var array
   **/ 
  var $handle = null;
  
  /**
   * An array of response objects for the current client.
   *
   * @var array
   **/
  var $responses = array();  
  
  /**
   * Handles count.
   *
   * @var int
   **/
  private static $handles = 0;
  
  /**
   * Constructor.       
   *
   * @param array  $options Options.
   * @return void
   */
  public function __construct($options = array())
  {
    $this->options = $options;
    $this->handle = curl_init(); 
  }     
  
// ------------------------------------------------------------------------    
 
  /**
   * Closes the CURL handle.       
   *
   */
  public function __destruct()
  {
    curl_close($this->handle);
  }    
   
// ------------------------------------------------------------------------    
  
  /**
   * Closes the CURL handle.       
   *
   */
  public function close()
  {
    curl_close($this->handle);
  }
  
// ------------------------------------------------------------------------    

  /**
   * Set the option 
   *
   * @param string $name 
   * @param string $value. 
   * @return Rest_Client
   */
  public function setOption($name, $value)
  {
    $this->options[$name] = $value;
    return $this;
  }

// ------------------------------------------------------------------------   

  /**
   * Get option. 
   *
   * @param string
   * @return mixed
   */
  public function getOption($name = null)
  {
    if (is_null($name)) 
      return $this->options;
    else 
      return isset($this->options[$name]) ? $this->options[$name] : null;  
  }
  
// ------------------------------------------------------------------------   
  
  /**
   * Run a synchronized request
   * 
   * @param Rest_Request $request object.
   * @return mixed returns the request identifier or false if fire is aborted        
   */
  public function fire(REST_Request $request)
  {
    if ($this->loads === 0)
      $this->time = microtime(true);
    ++$this->loads;

    self::$handles++; // create a fresh request identifier
    $request->setCurlOption(CURLOPT_USERAGENT, 'REST_Client/'.self::$version);
  
    foreach($this->fireHook as $hook) 
    {
      $ret = call_user_func($hook, $request, self::$handles, $this);
      if ($ret === false) 
      {
        ++$this->loadsNull;
        self::$handles--;
        return false;   
      }  
    }

    // configure curl client
    curl_setopt_array($this->handle, $request->toCurl());  
    curl_setopt($this->handle, CURLOPT_HTTPHEADER,   array_values($request->headers));  

    if (!is_resource($this->handle)) {
      ++$this->loadsNull;
      return trigger_error(sprintf('%s::%s() cURL session was lost', __CLASS__, $method), E_USER_ERROR);      
    }
  
    // send the request and create the response object
    ++$this->requests;
    $response = new REST_Response(curl_exec($this->handle), curl_errno($this->handle), curl_error($this->handle));   
    
    if (!$response->isError()) 
    {
      foreach(REST_Response::$properties as $name => $const) {
        $response->$name = curl_getinfo($this->handle, $const);
      }  
    }                  
    
    $response->id = self::$handles;

    $this->responses[] = $response; // append the response to the stack
    return self::$handles; // return a unique identifier for the request  
  }  
  
// ------------------------------------------------------------------------   
  
  /**
   * Fetch the response.
   *
   * @return mixed returns the request identifier or false if fire is aborted  
   */
  public function fetch()
  {
    ++$this->fetchs;
    ++$this->pulls;

    $response = array_pop($this->responses);
    if (is_null($response)) return false;

    // launch the fetch hooks
    foreach($this->fetchHook as $hook) {
      call_user_func($hook, $response, $response->id, $this);
    }
    
    return $response;     
  }
  
// ------------------------------------------------------------------------   

  /**
   * Check if fire queue is overflowed.    
   *
   * @return boolean
   */
  public function overflow()
  {
    return self::$handles > 0;
  }
}