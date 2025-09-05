<?php
	class SSDMDatabase
	{
		private static function write_db_error($error)
		{
			$error_date = date('Y-m-d H:i:s');
			$message = "{$error} | {$error_date} \r\n";
			file_put_contents("db-log.txt", $message, FILE_APPEND);
		}

		private static function connect()
		{
			$mysqli = new mysqli(DB_SERVER, getenv('DB_USERNAME'), getenv('DB_PASSWORD'), DB_DATABASE);
			if($mysqli->connect_errno != 0)
			{
				SSDMDatabase::write_db_error($mysqli->connect_error);
				return false;
			}
			return $mysqli;
		}

		public static function db_query($sql, $arg_string, $arg_array, $query_type)
		{
			$mysqli = SSDMDatabase::connect();
			if(!$mysqli)
			{
				return false;
			}
			$stmt = $mysqli->prepare($sql);
			if(!$stmt)
			{
				$mysqli->close();
				SSDMDatabase::write_db_error("Prepare failed. " . $sql);
				return false;
			}
			
			if(!$stmt->bind_param($arg_string, ...$arg_array))
			{
				$mysqli->close();
				SSDMDatabase::write_db_error("Bind Param failed. " . $sql);
				return false;
			}
			if(!$stmt->execute())
			{
				$mysqli->close();
				SSDMDatabase::write_db_error("Execute failed. " . $sql);
				return false;
			}
			$data = null;
			switch($query_type)
			{
				case INSERT_QUERY_TYPE:
					$data['id'] = $stmt->insert_id;
					break;
				case SELECT_QUERY_TYPE:
					$result = $stmt->get_result();
					$data = mysqli_fetch_assoc($result);
					break;
				case DELETE_QUERY_TYPE:
					$data['delete'] = 1;
					break;
				case UPDATE_QUERY_TYPE:
					$affected_rows = $stmt->affected_rows;
					if($affected_rows > 0)
					{
						$data['update'] = 1;
					}
					break;
			}
			$mysqli->close();
			return $data;
		}

		public static function record_exists($table, $index, $value)
		{
			$arg_string="";
			if(is_string($value))
			{
				$arg_string = "s";
			}
			elseif(is_int($value))
			{
				$arg_string = "i";
			}
			else
			{
				return false;
			}
			$sql = "SELECT " . $index . " FROM " . $table . " WHERE " . $index . " = ?";
			$data = SSDMDatabase::db_query($sql, $arg_string, [$value], SELECT_QUERY_TYPE);
			if(!$data)
			{
				return false;
			}
			return true;
		}
	}
?>