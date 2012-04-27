<?php

class RequestTest  extends PHPUnit_Framework_TestCase  
{
  public function setUp()
  {
    $this->request = new REST_Request();     
    $this->request->setProtocol('http');
    $this->request->host = 'google.com'; 
    $this->request->port = 80;
  }    
  
  public function testToCurl()
  {        
    $ch  = curl_init();     
    curl_setopt_array($ch, $this->request->toCurl());  
    $output = curl_exec($ch);    
    $this->assertNotEmpty($output);
    curl_close($ch);
  }
}