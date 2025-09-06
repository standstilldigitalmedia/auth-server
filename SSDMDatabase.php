<?php
	class SSDMDatabase
	{
		public static function clean_string($string)
		{
			$string = isset($string) ? trim($string) : '';
			$string = isset($string) ? strip_tags($string) : '';
			return $string;
		}

		private static function connect()
		{
			$mysqli = new mysqli(getenv('DB_SERVER'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'), getenv('DB_DATABASE'));
			if($mysqli->connect_errno != 0)
			{
				SSDMDatabase::write_db_error($mysqli->connect_error);
				return false;
			}
			return $mysqli;
		}

		public static function write_db_error($error)
		{
			$error = SSDMDatabase::clean_string($error);
			$error_date = date('Y-m-d H:i:s');
			$message = "{$error} | {$error_date} \r\n";
			file_put_contents(getenv('DB_LOG_FILE'), $message, FILE_APPEND);
		}

		public static function db_query($sql, $arg_string, $arg_array, $query_type)
		{
			$arg_string = SSDMDatabase::clean_string($arg_string);
			foreach ($arg_array as $key => $value) 
			{
				$arg_array[$key] = SSDMDatabase::clean_string($value);
			}
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
				case QueryType::Insert:
					$data['id'] = $stmt->insert_id;
					break;
				case QueryType::Select:
					$result = $stmt->get_result();
					if($result)
					{
						$data = mysqli_fetch_assoc($result);
					}
					break;
				case QueryType::Delete:
					$data['delete'] = 1;
					break;
				case QueryType::Update:
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
	}
?>