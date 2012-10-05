<?php 

class Aerial_AmfRecord implements ArrayAccess, Countable
{
	private $_type;
	
	function Aerial_AmfRecord($_explicitType = null){
		if($_explicitType) $this->_explicitType = $_explicitType;
	}
	
	//ArrayAccess Implementation
 	public function offsetGet($offset) {
        return isset($this->$offset) ? $this->$offset : null;
    }
    
 	public function offsetSet($offset, $value) {
        if (is_null($offset)) {
          	throw new Doctrine_Exception('AmfRecord key is null while setting value: ' . strval($value));
        } else {
            $this->$offset = $value;
        }
    }
    
    public function offsetExists($offset) {
        return isset($this->$offset);
    }
    
    public function offsetUnset($offset) {
        unset($this->$offset);
    }
    
    public function setType($type)
    {
    	$this->_type = $type;
    }
    
    public function getType()
    {
    	return $this->_type;
    }
    
    //Countable Implemenation
	public function count() {
     	return count((array)$this);
    }
    
 	public function getLast()
    {
        return $this;
    }
	

}