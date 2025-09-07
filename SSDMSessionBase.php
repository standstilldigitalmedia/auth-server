<?php
    class SSDMSessionBase
    {
        public $session_type = -1;
		public $client_id = "";
		public $player_id = 0;
		public $session_ticket = "";
		public $expiration_date = "";
		public $error = "";

		protected function session_exists()
        {
            $sql = "SELECT id FROM sessions WHERE client_id=? AND player_id=?";
            $data = SSDMDatabase::db_query($sql, "ss", [$this->client_id, $this->player_id], QueryType::Select);
            if($data)
			{
				return true;
			}
            return false;
        }

        public function get_session_ticket()
		{
			return $this->session_ticket;
		}

        private function get_max_ticket_length()
        {
            switch($this->session_type)
            {
                case REGISTER_SESSION:
                    return REGISTER_TICKET_LENGTH;
                    break;
                case LOGIN_SESSION:
                    return LOGIN_TICKET_LENGTH;
                    break;
                case REMEMBER_ME_SESSION:
                    return REMEMBER_ME_TICKET_LENGTH;
                    break;
            }
        }

		protected function get_new_expiration_date()
		{
			$current_date_time = new DateTime();
			
			switch($this->session_type)
			{
				case ACTIVATE_SESSION:
					$current_date_time->modify(ACTIVATE_EXPIRATION_DATE);
					break;
				case REMEMBER_ME_SESSION:
					$current_date_time->modify(REMEMBER_ME_EXPIRATION_DATE);
					break;
				case LOGIN_SESSION:
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

		protected function set_session_type($session_type)
        {
            if(empty($session_type))
            {
                $this->set_error("Session type required");
                return false;
            }
            $this->session_type = (int)$session_type;
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
			
			if(strlen($this->session_ticket) < $this->get_max_ticket_length())
			{
                $this->client_id = "";
				$this->set_error("Invalid session ticket");
				return false;
			}
		}
    }
?>