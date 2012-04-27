<?php

/** 
* Uses a few different APIs to put things through it's paces. Why? 
* Because at the time of this writing, I was unable to figure out a way to mock web requests properly in PHP :(
*
* 1. Uses requestbin.padrino 
**/     

class SimpleClientTest extends PHPUnit_Framework_TestCase
{       
  public function setUp()
  {
    # code...
  }      
  
  public function testGet()
  {                     
    $rc = new REST_SimpleClient('127.0.0.1', 3000);    
    $result = $rc->get('/get');      
    $rc->close();
    $this->assertFalse($result->isError());  
    $this->assertEquals(200, $result->code);
    $this->assertNotEmpty($result->content);
  }    
  
  public function testPost()
  {
    $rc = new REST_SimpleClient('127.0.0.1', 3000);        
    $result = $rc->post('/post');      
    $rc->close();      
    $this->assertFalse($result->isError());  
    $this->assertEquals(200, $result->code);
    $this->assertNotEmpty($result->content);  
  }  
  
  public function testPut()
  {
    $rc = new REST_SimpleClient('127.0.0.1', 3000);        
    $result = $rc->put('/put', array("cats" => 'cats'));      
    $rc->close();      
    $this->assertFalse($result->isError());
    $this->assertEquals(200, $result->code);
    $this->assertNotEmpty($result->content);  
  }
  
  public function testDelete()
  {
    $rc = new REST_SimpleClient('127.0.0.1', 3000);        
    $result = $rc->delete('/delete');      
    $rc->close();      
    $this->assertFalse($result->isError());    
    $this->assertEquals(200, $result->code);
    $this->assertNotEmpty($result->content);  
  }   
  
  public function testShouldError()
  {
    $rc = new REST_SimpleClient('127.0.0.1', 3000);        
    $result = $rc->get('/post');      
    $rc->close();   
    $this->assertFalse($result->isError());
    $this->assertEquals(405, $result->code);     
    $this->assertEmpty($result->content);
  }
}