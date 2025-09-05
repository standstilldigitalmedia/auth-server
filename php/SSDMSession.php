<?php
	require_once("config.php");
	require_once("SSDMDatabase.php");

	class SSDMSession 
	{
		private $session_type = -1;
		private $expiration_date = null;
		private $client_id = "";
		private $player_id = 0;
		public $session_ticket = "";
		public $error = "";

		public function __construct($client_id, $player_id, $session_ticket="")
        {
			$this->client_id = $client_id;
			$this->player_id = $player_id;
			$this->session_ticket = $session_ticket;
        }

		private function remove_session_from_database()
		{
			$sql = "DELETE FROM " . SESSIONS_TABLE . " WHERE session_ticket=? AND client_id=? AND player_id=?";
			$data = SSDMDatabase::db_query($sql, "ssi", [$this->session_ticket, $this->client_id, $this->player_id], DELETE_QUERY_TYPE);
			if(!$data)
			{
				$this->error = "Database error";
				return false;
			}
			return true;
		}

		private function ticket_has_expired()
		{
			$current_date_time = new DateTime();
			if($this->expiration_date < $current_date_time)
			{
				return true;
			}
			return false;
		}

		private function load_session_from_database()
		{
			$sql = "SELECT session_type, client_id, player_id, session_ticket, expiration_date FROM " . SESSIONS_TABLE . " WHERE session_ticket=?";
			$data = SSDMDatabase::db_query($sql, "s", [$this->session_ticket], SELECT_QUERY_TYPE);
			if(!$data)
			{
				$this->error = "Database error";
				return false;
			}

			$this->session_type = $data['session_type'];
			$this->client_id = $data['client_id'];
			$this->player_id = $data['player_id'];
			$this->session_ticket = $data['session_ticket'];
			$this->expiration_date = DateTime::createFromFormat('Y-m-d H:i:s', $data['expiration_date']);
			return true;
		} 

		private function validate_session($session)
		{
			if($session->session_type == -1)
			{
				return "Session error";
			}

			if(!$session->expiration_date)
			{
				return "Session error";
			}

			if($session->client_id == "")
			{
				return "Session error";
			}
			if($session->player_id == 0)
			{
				return "Session error";
			}
			if($session->session_ticket == "")
			{
				return "Session error";
			}
			return "";
		}

		private function get_new_expiration_date()
		{
			$current_date_time = new DateTime();
			
			switch($this->session_type)
			{
				case ACTIVATE_SESSION_TYPE:
					$current_date_time->modify(ACTIVATE_EXPIRATION_DATE);
					break;
				case REMEMBER_ME_SESSION_TYPE:
					$current_date_time->modify(REMEMBER_ME_EXPIRATION_DATE);
					break;
				case LOGIN_SESSION_TYPE:
					$current_date_time->modify(LOGIN_EXPIRATION_DATE);
					break;
			}
			return $current_date_time;
		}

		private function add_session_to_database()
		{
			$valid_session = $this->validate_session($this);
			if($valid_session != "")
			{
				$this->error = $valid_session;
				return false;
			}
			$sql = "INSERT INTO " . SESSIONS_TABLE . "(session_type, session_ticket, expiration_date, client_id, player_id) VALUES (?,?,?,?,?)";
			$arg_array = [$this->session_type,$this->session_ticket, $this->expiration_date->format('Y-m-d H:i:s'), $this->client_id, $this->player_id];
			if(!SSDMDatabase::db_query($sql, "ssssi", $arg_array, INSERT_QUERY_TYPE))
			{
				$this->error = "Database error";
				return false;
			}
			return true;
		}

		private function generate_random_string($length) 
		{
			$length = (int)$length;
			if($length < 1)
			{
				return false;
			}
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$characters_length = strlen($characters);
			$random_string = '';

			$bytes = random_bytes($length);

			for ($i = 0; $i < $length; $i++) {
				$random_string .= $characters[ord($bytes[$i]) % $characters_length];
			}

			return $random_string;
		}

		private function create_session_ticket($max_ticket_length, $to_upper = false)
		{
			if($max_ticket_length < 1)
			{
				$this->error = "Error creating session ticket.";
				return false;
				
			}

			$this->session_ticket = $this->generate_random_string($max_ticket_length);
			if(!$this->session_ticket)
			{
				$this->error = "Error creating session ticket.";
				return false;
			}
			if($to_upper)
			{
				$this->session_ticket = strtoupper($this->session_ticket);
			}
			
			$expiration_date = $this->get_new_expiration_date();			
			$this->expiration_date = $expiration_date;
			return true;
		}

		public function activate_account()
		{
			if(!$this->load_session_from_database())
			{
				return false;
			}
			if($this->session_type != ACTIVATE_SESSION_TYPE)
			{
				$this->error = "Invalid session type";
				return false;
			}
			if($this->ticket_has_expired())
			{
				$this->error = "Session has expired.";
			}
			return true;
			return $this->remove_session_from_database();
		}

		public function create_login_session_ticket()
		{
			$this->session_type = LOGIN_SESSION_TYPE;
			if(!$this->create_session_ticket(MAX_LOGIN_TICKET_LENGTH, LOGIN_EXPIRATION_DATE))
			{
				return false;
			}
			return $this->add_session_to_database();
		}
		
		public function create_activate_session_ticket()
		{
			$this->session_type = ACTIVATE_SESSION_TYPE;
			if(!$this->create_session_ticket(MAX_ACTIVATE_TICKET_LENGTH, ACTIVATE_EXPIRATION_DATE, true))
			{
				return false;
			}
			return $this->add_session_to_database();
		}
	}
?>