<?php

abstract class Aerial_Record extends Doctrine_Record
{
	protected function _set($fieldName, $value, $load = true)
	{
		if($this->isRelation($fieldName))
		{
			/*echo "$fieldName is a relation on ".$this->getTable()->getTableName()." > ";
				echo (gettype($value) == "object") ? get_class($value) : gettype($value)."\n";*/

			$value = $this->arrayToCollection($fieldName, $value);
		}

		return parent::_set($fieldName, $value, $load);
	}

	/**
	 * @param  $fieldName
	 * @param  $array
	 * @return Doctrine_Collection|array
	 *
	 * Returns a Doctrine_Collection instance if the field key is a relation alias, otherwise return the array
	 */
	protected function arrayToCollection($fieldName, $array)
	{
		$relationKey = $fieldName;
		$table = $this->getTable();
			
		$relation = $table->getRelation($relationKey);

		if($relation->isOneToOne())
		{
			if(!$array || is_undefined($array))
			return Doctrine_Record::initNullObject(new Doctrine_Null());

			return $array;
		}
		else
		{
			$coll = new Doctrine_Collection($relation->getTable());
			if(!$array || is_undefined($array))
			return $coll;

			foreach($array as $post)
			$coll->add($post);

			return $coll;
		}
	}

	protected function isRelation($alias)
	{
		return $this->getTable()->hasRelation($alias);
	}

	public function fromArray(array $array, $deep = true)
	{
		$refresh = false;
		foreach ($array as $key => $value) {
			if(is_undefined($value))
			continue;

			if ($key == '_identifier') {
				$refresh = true;
				$this->assignIdentifier($value);
				continue;
			}

			if ($deep && $this->getTable()->hasRelation($key)) {
				if ( ! $this->$key) {
					$this->refreshRelated($key);
				}

				if($value instanceof Aerial_Record)
				{
					$this->$key = $value;
					if(!is_undefined($value->id))
					{
						$refresh = true;
						$value->assignIdentifier($value->id);
					}
				}
				else if (is_array($value)) {
					if($value[0] instanceof Aerial_Record)
					$this->$key = $value;
					else if (isset($value[0]) && ! is_array($value[0])) {
						$this->unlink($key, array(), false);
						$this->link($key, $value, false);
					} else {
						$this->$key = $this->fromArray($value, $deep);
					}
				}
			} else if ($this->getTable()->hasField($key) || array_key_exists($key, $this->_values)) {
				$this->set($key, $value);
			} else {
				$method = 'set' . Doctrine_Inflector::classify($key);

				try {
					if (is_callable(array($this, $method))) {
						$this->$method($value);
					}
				} catch (Doctrine_Record_Exception $e) {}
			}
		}

		if ($refresh) {
			$this->refresh();
		}
	}

	/**
	 * loads all the uninitialized properties from the database.
	 * Used to move a record from PROXY to CLEAN/DIRTY state.
	 *
	 * @param array $data  overwriting data to load in the record. Instance is hydrated from the table if not specified.
	 * @return boolean
	 */
	public function load(array $data = array())
	{
		// only load the data from database if the Doctrine_Record is in proxy state
		if ($this->exists() && $this->isInProxyState()) {
			$id = $this->identifier();

			if ( ! is_array($id)) {
				$id = array($id);
			}

			if (empty($id)) {
				return false;
			}

			$data = empty($data) ? $this->getTable()->find($id, Doctrine_Core::HYDRATE_ARRAY) : $data;

			if($data)
			foreach ($data as $field => $value) {
				if ( ! array_key_exists($field, $this->_data) || $this->_data[$field] === self::$_null) {
					$this->_data[$field] = $value;
				}
			}

			if ($this->isModified()) {
				$this->_state = Doctrine_Record::STATE_DIRTY;
			} else if (!$this->isInProxyState()) {
				$this->_state = Doctrine_Record::STATE_CLEAN;
			}

			return true;
		}

		return false;
	}

	public function getIdentifier()
	{
		return $this[$this->table->getIdentifier()];
	}
}
?>