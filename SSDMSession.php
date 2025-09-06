<?php
	require_once("config.php");
	require_once("SSDMDatabase.php");

	class SSDMSession 
	{
		private $session_type = -1;
		private $client_id = "";
		private $player_id = 0;
		private $session_ticket = "";
		private $expiration_date = "";
		private $error = "";

		private function get_max_ticket_length()
        {
            switch($this->session_type)
            {
                case SessionType::Register:
                    return MAX_REGISTER_TICKET_LENGTH;
                    break;
                case SessionType::Activate:
                    return MAX_ACTIVATE_TICKET_LENGTH;
                    break;
                case SessionType::Login:
                    return MAX_LOGIN_TICKET_LENGTH;
                    break;
                case SessionType::Remember_me:
                    return MAX_REMEMBER_ME_TICKET_LENGTH;
                    break;
            }
        }

		private function get_new_expiration_date()
		{
			$current_date_time = new DateTime();
			
			switch($this->session_type)
			{
				case SessionType::Activate->value:
					$current_date_time->modify(ACTIVATE_EXPIRATION_DATE);
					break;
				case SessionType::Remember_me->value:
					$current_date_time->modify(REMEMBER_ME_EXPIRATION_DATE);
					break;
				case SessionType::Login->value:
					$current_date_time->modify(LOGIN_EXPIRATION_DATE);
					break;
			}
			return $current_date_time->format('Y-m-d H:i:s');
		}

		protected function set_error($error)
        {
            if($this->error == "")
            {
                $this->error = SSDMDatabase::clean_string($error);
            }
        }

		protected function set_session_type($sesion_type)
        {
            if(empty($session_type))
            {
                $this->set_error("Session type required");
                return false;
            }
            $this->session_type = (int)$sesion_type;
        }

		protected function set_player_id($player_id)
        {
            if(empty($player_id))
            {
                $this->set_error("Player ID required");
                return false;
            }
            $this->player_id = (int)$player_id;
        }

		protected function set_client_id($client_id)
		{
            $this->client_id = SSDMDatabase::clean_string($client_id);
			if(empty($this->client_id))
			{
                $this->client_id = "";
				$this->set_error("Client ID required");
				return false;
            }
			
			if(strlen($this->client_id) > MAX_CLIENT_ID_LENGTH)
			{
                $this->client_id = "";
				$this->set_error("Client ID too long");
				return false;
			}
		}

		protected function set_session_ticket($session_ticket)
		{
            $this->session_ticket = SSDMDatabase::clean_string($session_ticket);
			if(empty($this->session_ticket))
			{
                $this->session_ticket = "";
				$this->set_error("Session ticket required");
				return false;
            }
			
			if(strlen($this->session_ticket) != $this->get_max_ticket_length())
			{
                $this->client_id = "";
				$this->set_error("Invalid session ticket");
				return false;
			}
		}

		protected function set_expiration_date()
		{
			$this->expiration_date = $this->get_new_expiration_date();
		}

		private function add_session_to_database()
		{
			$sql = "INSERT INTO sessions (session_type, session_ticket, expiration_date, client_id, player_id) VALUES (?,?,?,?,?)";
			$arg_array = [$this->session_type,$this->session_ticket, $this->expiration_date, $this->client_id, $this->player_id];
			if(!SSDMDatabase::db_query($sql, "ssssi", $arg_array, QueryType::Insert))
			{
				SSDMDatabase::write_db_error("Unable to add session to database");
				$this->set_error("Database error");
				return false;
			}
			return true;
		}

		private function load_session_from_database()
		{
			$sql = "SELECT session_type, client_id, player_id, session_ticket, expiration_date FROM sessions WHERE session_ticket=? AND client_id=? AND player_id=? LIMIT 1";
			$data = SSDMDatabase::db_query($sql, "s", [$this->session_ticket, $this->client_id, $this->player_id], QueryType::Select);
			if(!$data)
			{
				SSDMDatabase::write_db_error("Unable to load session from database");
				$this->set_error("Database error");
				return false;
			}

			$this->session_type = $data['session_type'];
			$this->client_id = $data['client_id'];
			$this->player_id = $data['player_id'];
			$this->session_ticket = $data['session_ticket'];
			$this->expiration_date = DateTime::createFromFormat('Y-m-d H:i:s', $data['expiration_date']);
			return true;
		} 

		private function delete_all_sessions_from_database()
        {
            $sql = "DELETE FROM sessions WHERE player_id=?";
            $data = SSDMDatabase::db_query($sql, "s", [(int)$this->player_id], QueryType::Delete);
            if(!$data)
            {
				SSDMDatabase::write_db_error("Unable to delete all sessions from database.");
                $this->set_error("Database error.");
				return false;
            }
			return true;
        }

		private function ticket_has_expired()
		{
			$current_date_time = new DateTime();
			if($this->expiration_date < $current_date_time)
			{
				$this->set_error("Session has expired");
				return true;
			}
			return false;
		}

		private function generate_session_ticket() 
		{
			$characters = "";
			$length = SSDMSession::get_max_ticket_length();
		
			if($length < 1)
			{
				SSDMDatabase::write_db_error("random string length less than 1");
				$this->set_error("Error creating session ticket");
				return false;
			}

			if($this->session_type == SessionType::Register)
			{
				$characters = "0123456789";
			}
			else
			{
				$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			}
			
			$characters_length = strlen($characters);
			$random_string = '';
			$bytes = random_bytes($length);
			for ($i = 0; $i < $length; $i++) 
			{
				$random_string .= $characters[ord($bytes[$i]) % $characters_length];
			}
			$this->session_ticket = $random_string;
			return true;
		}

		public function activate_account()
		{
			if(!$this->load_session_from_database())
			{
				return false;
			}
			if($this->session_type != SessionType::Activate->value)
			{
				$this->set_error("Invalid session type");
				return false;
			}
			if($this->ticket_has_expired())
			{
				$this->set_error("Session has expired.");
			}
			return $this->delete_all_sessions_from_database();
		}

		public function create_new_session()
		{
			$this->expiration_date = $this->get_new_expiration_date();
			if(!$this->generate_session_ticket())
			{
				$this->set_error("Error creating session ticket.");
				return false;
			}

			$delete = $this->delete_all_sessions_from_database();
			if(!$delete)
			{
				$this->set_error("Database error.");
				return false;
			}
			return $this->add_session_to_database();
		}

		public function get_session_ticket()
		{
			return $this->session_ticket;
		}

		public function __construct($incoming_session)
        {
			if(empty($incoming_session))
			{
				$this->set_error("Session is empty");
                return false;
			}

			if(property_exists($incoming_session, "client_id"))
            {
                $this->set_client_id($incoming_session->client_id);
            }
			else
			{
				$this->set_error("Client ID required");
				return false;
			}

			if(property_exists($incoming_session, "player_id"))
            {
                $this->set_player_id($incoming_session->player_id);
            }
			else
			{
				$this->set_error("Player ID required");
			}

			if(property_exists($incoming_session, "session_type"))
            {
                $this->set_session_type($incoming_session->session_type);
            }
			if(property_exists($incoming_session, "session_ticket"))
            {
                $this->set_session_ticket($incoming_session->session_ticket);
            }
			if(property_exists($incoming_session, "expiration_date"))
            {
                $this->set_expiration_date($incoming_session->expiration_date);
            }
			if(property_exists($incoming_session, "error"))
            {
                $this->set_expiration_date($incoming_session->error);
            }
        }
	}
?>