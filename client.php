<?php     

// Requires
require_once 'response.php';
require_once 'request.php';  
require_once 'client' . DS . 'sync.php';

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
abstract class REST_Client
{   
  /**
   * CURL Options
   *
   * @var array
   **/ 
  var $options = array();
  
  /**
   * FireHook callbacks. Used for Async.
   *
   * @var array
   **/
  var $fireHook = array();   
  
  /**
   * FetchHook callbacks. Used for Async.
   *
   * @var array
   **/
  var $fetchHook = array();
 
  /**
   * Number of requests made.
   *
   * @var int
   **/ 
  var $requests = 0;   
  
  /**
   * Number of loads made.
   *
   * @var int
   **/
  var $loads = 0; 
  
  /**
   * Number of failed loads.
   *
   * @var int
   **/
  var $loadsNull = 0;   
  
  /**
   * Number of fetches made.
   *
   * @var int
   **/
  var $fetchs = 0;  
  
  /**
   * Number of failed fetche.
   *
   * @var int
   **/
  var $fetchsNull = 0;   
  
  /**
   * Number of pulls made.
   *
   * @var int
   **/
  var $pulls = 0;      
  
  /**
   * Number of failed pulls.
   *
   * @var int
   **/
  var $pullsNull = 0;      
  
  /**
   * Time elapsed.
   *
   * @var int
   **/
  var $time = 0;   
  
  /**
   * Version number.
   *
   * @var string
   **/
  static $version = '2.1.0'; 
  
  /**
   * Constructor.       
   *
   * @param array  $options CURL options.
   * @return REST_Client
   */
  public function __construct($options = array())
  {
    $this->options = $options;
  } 
  
// ------------------------------------------------------------------------   
  
  /**
   * Factory.       
   *
   * @note Used to create REST_Client_Async or REST_Client_Sync instances. 
   * 
   * @param string $type The type of class to create.
   * @param array  $options CURL options.
   * @return REST_Client
   */
  public static function factory($type = 'sync', $options = array())
  {
    $class_name = 'REST_Client_'.ucfirst($type);

    if (class_exists($class_name, false))
      $instance = new $class_name($options);
    else 
    {
      $file = strtr($class_name, '_', '/').'.php';
      $paths = explode(PATH_SEPARATOR, ini_get('include_path'));
      foreach ($paths as $path) 
      {
        $fullpath = $path . '/' . $file;
        if (file_exists($fullpath)) 
        {
          include_once($fullpath);        
          
          if (class_exists($class_name, false))
            $instance = new $class_name($options);
            
          break; 
        } 
      } 
    }  
    
    return $instance;  
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
   * Register a fire hook. 
   *
   * @param mixd Name of the callback function or alternatively an object + method name.
   *  like this:  array($object, 'method')
   * @return REST_Client
   */
  public function addFireHook($callback)
  {
    if (!is_callable($callback))
      throw Exception('FireHook callback is not callable');
    if (!in_array($callback, $this->fireHook))
      $this->fireHook[] = $callback;
      
    return $this; 
  }  
  
// ------------------------------------------------------------------------     
  
  /**
   * Register a fetch hook    
   *
   * @param mixd Name of the callback function or alternatively an object + method name.
   *  like this:  array($object, 'method')
   * @return REST_Client
   */
  public function addFetchHook($callback)
  {
    if (!is_callable($callback))
      throw Exception('FetchHook callback is not callable');
    if (!in_array($callback, $this->fetchHook)) 
      $this->fetchHook[] = $callback;     
      
    return $this;      
  } 
  
// ------------------------------------------------------------------------        

  /**
   * Run a request.
   *
   * @param  array
   * @return integer the request identifier
   */
  abstract public function fire(REST_Request $request);
  
// ------------------------------------------------------------------------        
  
  /**
   * Get a request response (after a fire)       
   *
   * @return REST_Response
   */
  abstract public function fetch();
  
// ------------------------------------------------------------------------        

  /**
   * Check if fire queue is overflowed. 
   *
   * @return boolean
   */
  abstract public function overflow();

  /**
   * Get some stats
   *
   * @return mixed
   */
  public function getInfo($k = null)
  {
    $t = microtime(true) - $this->time;  
    
    $a =  array(
      'requests'      => $this->requests,
      'requests_avg'  => round($this->requests/$this->loads, 2),
      'requests_sec'  => round($this->requests/$t, 2),
      'fetchs_hit'    => round(($this->fetchs - $this->fetchs_null) / $this->fetchs, 2),
      'pulls_hit'     => round(($this->pulls - $this->pulls_null) / $this->pulls, 2),
      'loads_hit'     => round(($this->loads - $this->loads_null) / $this->loads, 2),
      'time'          => round($t, 2),
    );
    
    if (is_null($k) or !isset($a[$k])) 
      return $a;
    else 
      return $a[$k];  
  }
}