<?php 

class Aerial_ArrayCollection implements ArrayAccess, Countable
{
	public $_explicitType;
	public $source = array();

	function Aerial_ArrayCollection($component = null)
	{
		$this->_explicitType = ConfigXml::getInstance()->collectionClass;
	}
	
	//ArrayAccess Implementation
 	public function offsetGet($offset) {
        return isset($this->source[$offset]) ? $this->source[$offset] : null;
    }
    
 	public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->source[] = $value;
        } else {
            $this->source[$offset] = $value;
        }
    }
    
    public function offsetExists($offset) {
        return isset($this->source[$offset]);
    }
    
    public function offsetUnset($offset) {
        unset($this->source[$offset]);
    }
    
    //Countable Implemenation
	public function count() {
     	return count($this->source);
    }
    
    //Helpers
 	public function getLast()
    {
        return end($this->source);
    }
    
 	public function key()
    {
        return key($this->source);
    }
	

}