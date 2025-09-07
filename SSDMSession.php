<?php
	require_once("config.php");
	require_once("SSDMDatabase.php");
	require_once("SSDMSessionBase.php");

	class SSDMSession extends SSDMSessionBase
	{
		public function __construct($session_type, $client_id, $player_id = -1, $session_ticket = "")
        {
            $this->set_session_type($session_type);
            $this->set_client_id($client_id);
            if($this->session_type == ACTIVATE_SESSION)
			{
				$this->set_session_ticket($session_ticket);
			}
			if($this->session_type == ACTIVATE_SESSION || $this->session_type == REGISTER_SESSION)
			{
				$this->set_player_id($player_id);
			}
			if($this->error == "")
			{
				switch($this->session_type)
				{
					case REGISTER_SESSION:
						$this->create_new_session();
						break;
					case ACTIVATE_SESSION:
						$this->activate_account();
						break;
					case REMEMBER_ME_SESSION:
						//$current_date_time->modify(REMEMBER_ME_EXPIRATION_DATE);
						break;
					case LOGIN_SESSION:
						$this->create_new_session();
						break;
				}
			}
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

		private function add_session_to_database($session)
		{
			$sql = "INSERT INTO sessions (session_type, session_ticket, expiration_date, client_id, player_id) VALUES (?,?,?,?,?)";
			$arg_array = [$session->session_type,$session->session_ticket, $session->expiration_date, $session->client_id, $session->player_id];
			if(!SSDMDatabase::db_query($sql, "ssssi", $arg_array, QueryType::Insert))
			{
				SSDMDatabase::write_db_error("Unable to add session to database");
				$this->error = "Database Error";
				return false;
			}
			return true;
		}

		private function load_session_from_database($session)
		{
			$sql = "SELECT session_type, expiration_date, session_ticket FROM sessions WHERE session_ticket=? AND client_id=? AND player_id=? LIMIT 1";
			$data = SSDMDatabase::db_query($sql, "sii", [$session->session_ticket, $session->client_id, $session->player_id], QueryType::Select);
			if(!$data)
			{
				SSDMDatabase::write_db_error("Unable to load session from database");
				$this->error = "Database Error";
				return false;
			}
			$this->set_session_type($data['session_type']);
			$this->set_session_ticket($data['session_ticket']);
			$this->expiration_date = DateTime::createFromFormat('Y-m-d H:i:s', $data['expiration_date']);
			return true;
		} 

		private function delete_all_sessions_from_database($session)
        {
            $sql = "DELETE FROM sessions WHERE player_id=? AND client_id=?";
            $data = SSDMDatabase::db_query($sql, "ii", [$session->player_id, $session->client_id], QueryType::Delete);
            if(!$data)
            {
				SSDMDatabase::write_db_error("Unable to delete all sessions from database.");
				$this->error = "Database Error";
				return false;
            }
			return true;
        }

		private function create_new_session()
		{
			$this->expiration_date = $this->get_new_expiration_date();
			$strength = 0;
			$length = 0;
			switch($this->session_type)
			{
				case REGISTER_SESSION:
					$strength = 1;
					$length = 6;
					break;
				case LOGIN_SESSION:
					$strength = 3;
					$length = 16;
					break;
			}
			$this->session_ticket = SSDMDatabase::generate_unique_id($strength, $length);
			if(!$this->session_ticket)
			{
				$this->set_error("Error creating session ticket.");
				return false;
			}

			return $this->add_session_to_database($this);
		}

		private function activate_account()
		{
			if(!$this->load_session_from_database($this))
			{
				$this->set_error("Error loading session");
				return false;
			}
			if($this->ticket_has_expired())
			{
				$this->set_error("Session has expired.");
			}
			return $this->delete_all_sessions_from_database($this);
		}
	}
?>