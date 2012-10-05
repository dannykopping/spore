<?php
class Aerial_Relationship
{

	public static function strpos_criteria($column)
	{
		$needle = array( "!=", ">=", "<=", "=", ">", "<",  "&", "IS ", "NOT ", "BETWEEN ", "LIKE ", "IN ");
		//$needle is an array of values.
		if(!is_array($needle))
		$needle = array($needle);

		foreach($needle as $what)
		{
			if(($pos = stripos($column, $what))!==false) return $pos;
		}

		return false;
	}

	public static function columns($dirty_key)
	{
		$cols = preg_split('#\w+$|.+\(\s*|\s*,\s*|\s*\)\s*#', $dirty_key, -1, PREG_SPLIT_NO_EMPTY);

		return $cols;
	}

	public function splitOR($column)
	{
		$cols = preg_split('#\s+OR\s+#', $column, -1, PREG_SPLIT_NO_EMPTY);

		return $cols;
	}

	public static function setSelectColumn(&$d_docTable, &$selectedTables, $sqlAlias, &$col)
	{
		$firstChar = substr($col, 0,1);
		$oper = ($firstChar == "-" ? "-" : "+");

		$colName = (($firstChar == "-") || ($firstChar ==  "+") ? substr($col, 1) : $col);
		$col = $colName;

		$criteriaPosition = self::strpos_criteria($colName);
		if($criteriaPosition !== false)
		{
			//If the criteria operator is preceeded by "_", use doctrine "WITH" in leftJoin.
			$useWith = substr($colName, $criteriaPosition-1, 1) == "_" ? true : false;
			$cri = substr($colName, $criteriaPosition);
			$cleanColumn = substr($colName, 0, $criteriaPosition - ($useWith ?  1 : 0));
		}else{
			$cleanColumn = $colName;
		}

		if($oper == "+"){
			if($cleanColumn == "*"){
				$selectedTables[$sqlAlias] =  $d_docTable->getFieldNames();
			}else{
				$found = array_search($cleanColumn, $selectedTables[$sqlAlias]);
				if(!$found) $selectedTables[$sqlAlias][] =  $cleanColumn;
			}
		}elseif($oper == "-"){
			if($cleanColumn == "*"){
				//May need to build some logic to handle user error here.
			}
			//Need to check if the column exists before removing.
			$found = array_search($cleanColumn, $selectedTables[$sqlAlias]);
			if($found) unset($selectedTables[$sqlAlias][$found]);
		}

		return $colName;
	}

	public static function key($dirty_key)
	{
		preg_match('#\w+#', $dirty_key, $key);

		return $key[0];
	}

	public static function merge_dirty_keys($dirty_key1, $dirty_key2)
	{
		$merged_columns = array_unique(array_merge(self::columns($dirty_key1), self::columns($dirty_key2)));

		$key1 = self::key($dirty_key1);

		if(count($merged_columns) > 0) $key1 = $key1 . "(" . implode("," , $merged_columns) . ")";

		return  $key1;
	}

	public static function key_exists($dirty_key, $array)
	{
		foreach ($array as $dk=>$v)
		if(self::key($dk) == self::key($dirty_key)) return $dk;

		return false;
	}

	public static function to_array($string, $rootTable) //Not using this function anywhere rigth now.
	{
		$array = explode(".", $string);
		if($array[0] <> $rootTable) array_unshift($array, $rootTable);

		$tmp =& $p;
		foreach($array as $table)
		{
			$table = str_replace(' ','', $table); //White spaces need to be stripped out.
			$tmp[$table] = array(); //Could comment this out so it equals null but would have to change logic everywhere else.
			$tmp =& $tmp[$table];
		}

		return $p;
	}

	public static function merge( array &$array1,  $string )
	{
		$tree = $array1;
		@list($dirty_key, $value) = explode(".", $string, 2);

		$source_dirty_key = self::key_exists($dirty_key,$tree);

		if($source_dirty_key ) //$value && is_array($tree[$source_dirty_key])
		{
			$new_dirty_key = self::merge_dirty_keys($source_dirty_key, $dirty_key);
			if($new_dirty_key <> $source_dirty_key)
			{
				$tree[$new_dirty_key] = $tree[$source_dirty_key];
				unset($tree[$source_dirty_key]);
				$source_dirty_key = $new_dirty_key;
			}
			if($value)
			$tree [$source_dirty_key] = self::merge($tree[$source_dirty_key], $value);
		}
		else
		{
			$tree [$dirty_key] = array();
			if($value)
			$tree[$dirty_key] = self::merge($tree[$dirty_key], $value);
		}

		return $tree;
	}

	public static function relationParts($relations)
	{
		$pTable = self::key(key($relations));
		$dAlias = $relations;
		$joins = array();
		$selects = array();
		$criteria = array();

		self::internal_relationParts($dAlias, $pTable, $joins, $selects, $criteria);

		//Combine the SELECT array elements into a single string.
		$start = true;
		foreach($selects as $key=>$val)
		{
			//Iterate through the columns
			foreach($val as $col)
			{
				if($start){
					$selectStr = "$key.$col";
					$start = false;
				}else{
					$selectStr .= ", $key.$col";
				}
			}
		}

		return array("selects"=>$selectStr, "joins"=>$joins, "criteria"=>$criteria);
	}

	private static function internal_relationParts($dAlias, $pTable, &$leftJoins, &$selectedTables, &$criteria, $sAlias=null, &$i=0)
	{
		$isRoot = ($sAlias == null) ? true : false;

		$_table = Doctrine_Core::getTable($pTable);

		foreach($dAlias as $d => $dd)
		{
			if($isRoot){
				$sqlAlias = "r";
				$d_docTable = $_table;
			}else {
				$i++;
				$sqlAlias = "n" . $i;
				$rd = self::key($d);

				$d_name = $_table->getRelation($rd)->getClass(); //String
				$d_docTable = Doctrine_Core::getTable($d_name); //DoctrineTable

				$leftJoin = "$sAlias." . $rd . " $sqlAlias";
			}

			// Split the columns out by ","
			$columns = self::columns($d);
			$selectedTables[$sqlAlias] = array();

			if(count($columns) == 0){
				$selectedTables[$sqlAlias][] = $d_docTable->getIdentifier(); //Add the Primary Key column
			}

			foreach($columns as $col) // columns are separated by ",".  Could have "OR" clause in there.
			{
				// Set WHERE and WITH and split the "OR" parts.
				$colORs = self::splitOR($col);  // firstName='Rob' OR -lastName='Cesaric'

				foreach($colORs as &$c)
				{
					$column = self::setSelectColumn($d_docTable, $selectedTables, $sqlAlias, $c); //Returns the column & criteria with the opperator stripped off.

						
					$criteriaPosition = self::strpos_criteria($column);

					if($criteriaPosition !== false)
					{
						//If the criteria operator is preceeded by "_", use doctrine "WITH" in leftJoin.
						$useWith = substr($column, $criteriaPosition-1, 1) == "_" ? true : false;
						$cri = substr($column, $criteriaPosition);
						$cleanColumn = substr($column, 0, $criteriaPosition - ($useWith ?  1 : 0));

						//Clean the column name
						$column = $cleanColumn;
					}
					$c =  $sqlAlias . "." . $c;
				}

				if($criteriaPosition !== false){
					if($useWith){
						$leftJoin .= " WITH $sqlAlias." .  $cleanColumn . " $cri";
					}else{
						//$criteria[] = "$sqlAlias." . $cleanColumn . " $cri";
						$criteria[] = implode(" OR ",$colORs);
					}
				}

			}

			if(!$isRoot)
			{
				$leftJoins[] = $leftJoin;
			}

			//Recursion
			if(count($dd) > 0){
				if($isRoot){
					$d_name = $pTable;
				}
				self::internal_relationParts($dd, $d_name, $leftJoins, $selectedTables, $criteria, $sqlAlias, $i);
			}

		}//Table Loop
	}


}
?>