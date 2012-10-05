<?php

class Aerial_Hydrator_ArrayDriver  extends Doctrine_Hydrator_Graph
{
	public function getElementCollection($component)
	{
		return array();
	}

	public function getElement(array $data, $component)
	{
		$component = $this->_getClassNameToReturn($data, $component);
		$this->_tables[$component]->setData($data);
		$record = $this->_tables[$component]->getRecord();
		$type = $record->get("_explicitType");
		
		$amfRecord = new Aerial_AmfRecord($type);
		
		foreach($data as $key=>$value)
			$amfRecord->$key = $value;

		return $amfRecord;
	}


	public function registerCollection($coll)
	{
	}

	public function initRelated(&$record, $name)
	{
		if ( ! isset($record[$name])) {
			$record[$name] = array();
		}
		
		return true;
	}

	public function getNullPointer()
	{
		return null;
	}

	public function getLastKey(&$coll)
	{
		end($coll);
        return key($coll);
	}

	public function setLastElement(&$prev, &$coll, $index, $dqlAlias, $oneToOne) //$coll is the return $result.
	{
		if ($coll === null) {
			unset($prev[$dqlAlias]); 
			return;
		}

		if ($index !== false) {
			$prev[$dqlAlias] =& $coll[$index];
			return;
		}

		if ($coll) {
            if ($oneToOne) {
                $prev[$dqlAlias] =& $coll;
            } else {
                end($coll);
                $prev[$dqlAlias] =& $coll[key($coll)];
            }
        }
	}
	
}
