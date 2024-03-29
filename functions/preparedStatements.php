<?php
	//Define functions for preparing statements for interacting with database tables

	//Perform a select query
	function select($database, $statement, $values)
	{
		global $errors;

		$i=0;
		$stringPlaceHolders = array();
		while($i<count($values))
		{
			array_push($stringPlaceHolders, 's');
			$i++;
		}

		$stringPlaceHolders = implode('', $stringPlaceHolders);

		//echo $statement;
		//echo implode('", "', $values);

		$select = $database->prepare($statement);
		$select->bind_param($stringPlaceHolders, ...$values);

		if ($select->execute())
		{
			//without errors:
			return $select->get_result();
		}
		else
		{
			//error:
			$errors[] = array('type'=>'database','message'=>"Error selecting for ".$statement.": " . $select->error);
			return FALSE;
		}
		$select->close;
	}

	//Delete row from table
	function delete($database, $table, $column, $value)
	{
		global $errors;

		$statement = 'DELETE FROM `'.$table.'` WHERE `'.$column.'` = ?';

		$delete = $database->prepare($statement);
		$delete->bind_param("s", $value);

		if ($delete->execute())
		{
			//without errors:
			return TRUE;
		}
		else
		{
			//error:
			$errors[] = array('type'=>'database','message'=>"Error deleting from ".$table." table: " . $delete->error);
			return FALSE;
		}
		$delete->close;
	}

	//Insert new record to table
	function insert($database, $table, $columns, $values)
	{
		//print_r($values);

		global $errors;

		$i=0;
		$valuePlaceHolders = array();
		$stringPlaceHolders = array();
		while($i<count($columns))
		{
			array_push($valuePlaceHolders, '?');
			array_push($stringPlaceHolders, 's');
			$i++;
		}

		$statement = 'INSERT INTO '.$table.' (`'.implode('`, `', $columns).'`) VALUES ('.implode(', ', $valuePlaceHolders).')';

		$stringPlaceHolders = implode('', $stringPlaceHolders);

		//echo $statement;
		//echo implode('", "', $values);

		//Insert select fields to table
		$insert = $database->prepare($statement);
		$insert->bind_param($stringPlaceHolders, ...$values);
		$execute = $insert->execute();
		if ($execute)
		{
			//without errors:
			return TRUE;
		}
		else
		{
			//error:
			$errors[] = array('type'=>'database','message'=>"Error inserting to ".$table." table: " . $insert->error);
			return FALSE;
		}

		$insert->close;
	}

	//Update new record to table
	function update($database, $table, $columns, $values, $where)
	{
		global $errors;

		$i=0;
		$stringPlaceHolders = array();
		while($i<=count($columns))
		{
			array_push($stringPlaceHolders, 's');
			$i++;
		}

		$statement = 'UPDATE `'.$table.'` SET `'.implode('` = ?, `', $columns).'` = ? WHERE `'.$where.'` = ?';

		$stringPlaceHolders = implode('', $stringPlaceHolders);

		//Update select fields to table
		$update = $database->prepare($statement);
		$update->bind_param($stringPlaceHolders, ...$values);

		if ($update->execute())
		{
			//without errors:
			return TRUE;
		}
		else
		{
			//error:
			$errors[] = array('type'=>'database','message'=>"Error updating ".$table." table for ".$statement.": " . $update->error);
			return FALSE;
		}
		$update->close;
	}
