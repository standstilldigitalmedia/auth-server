<?php
    class SSDMPlayerBase
    {
        public $user_name = "";
        public $email = "";
        public $auth = -1;
        public $session = null;

        public $request_type = -1;
        public $player_id = -1;
        public $display_name = "";
        public $session_ticket = "";
        public $error = "";

        protected function player_exists()
        {
            $sql = "SELECT player_id FROM player_auth WHERE email=?";
            $data = SSDMDatabase::db_query($sql, "s", [$this->email], QueryType::Select);
            if($data)
			{
				return true;
			}

            $sql = "SELECT player_id FROM player_auth WHERE display_name=?";
            $data = SSDMDatabase::db_query($sql, "s", [$this->display_name], QueryType::Select);
            if($data)
			{
				return true;
			}

            $sql = "SELECT player_id FROM player_auth WHERE user_name=?";
            $data = SSDMDatabase::db_query($sql, "s", [$this->user_name], QueryType::Select);
            if($data)
			{
				return true;
			}
            return false;
        }

        private function get_player()
        {
            $sql = "SELECT password, player_id, auth, display_name FROM player_auth WHERE user_name=?";
            $data = SSDMDatabase::db_query($sql, "s", [$this->user_name], QueryType::Select);
            if(!$data)
            {
                $this->error = "Invalid username and password combination.";
				return false;
            }
            return $data;
        }

        private function set_last_login()
        {
            $sql = "UPDATE player_auth SET last_login=? WHERE player_id=?";
            $current_date_time = new DateTime();
            $data = SSDMDatabase::db_query($sql, "si", [$current_date_time->format('Y-m-d H:i:s'), (int)$this->player_id], QueryType::Update);
            if(!$data)
            {
                SSDMDatabase::write_db_error("Unable to set last login");
                $this->error = "Database error";
				return false;
            }
            return $data;
        }

        protected function activate_account_in_database()
		{
			$sql = "UPDATE player_auth SET auth=1 WHERE player_id=?";
			if(!SSDMDatabase::db_query($sql, "i", [$this->player_id], QueryType::Update))
			{
				SSDMDatabase::write_db_error("Unable to update player auth in activate_account_in_database");
				$this->set_error("Database error");
				return false;
			}
			return true;
		}

        protected function add_new_player_to_database($password)
		{
            $player_id = SSDMDatabase::generate_unique_id(3,ID_TICKET_LENGTH);
			$password = password_hash($password, PASSWORD_DEFAULT);
			$sql = "INSERT INTO player_auth (player_id, user_name, display_name, email, password) VALUES(?,?,?,?,?)";
			$arg_array = [$player_id, $this->user_name, $this->display_name, $this->email, $password];
			$data = SSDMDatabase::db_query($sql, "sssss", $arg_array, QueryType::Insert);
			if(!$data)
			{
				SSDMDatabase::write_db_error("Unable to add new player to database");
                $this->set_error("Database error.");
				return false;
			}
            $this->set_player_id($data['id']);
			return true;
		}

        protected function send_activation_email()
        {
            $headers = "From: " . getenv('ADMIN_EMAIL') . "\r\n" .
            "Reply-To: " . getenv('REPLY_TO_EMAIL') . "\r\n" .
            "X-Mailer: PHP/" . phpversion();

            $subject = "Activate your Standstill Digital Media account";

            $message = "Thanks for creating an account with us. Once activated, you can use this account to log into a variety of games on the Standstill Digital Media platform. \r\n \r\n" .
            "Account Details:\r\n" .
            "User Name: " . $this->user_name . "\r\n" .
            "Display Name: " . $this->display_name . "\r\n \r\n" .
            "Activation Code: \r\n" .
            $this->session->get_session_ticket();

            if(!mail($this->email, $subject, $message, $headers)) 
            {
                $this->set_error("Unable to send activation email");
                return false;
            } 
            return true;
        }

        protected function set_auth($auth)
        {
            if(empty($auth))
            {
                $this->set_error("Auth required");
                return false;
            }
            $this->auth = (int)$auth;
        }

        public function set_request_type($request_type)
        {
            if(empty($request_type))
            {
                $this->set_error("Request type required");
                return false;
            }
            $this->request_type = (int)$request_type;
        }

        public function set_player_id($player_id)
        {
            if(empty($player_id))
            {
                $this->set_error("Player ID required");
                return false;
            }
            $this->player_id = (int)$player_id;
        }

        protected function set_error($error)
        {
            if($this->error == "")
            {
                $this->error = SSDMDatabase::clean_string($error);
            }
        }

        protected function set_email($email)
		{
            $this->email = SSDMDatabase::clean_string($email);
			if(empty($this->email))
			{
                $this->email = "";
				$this->set_error("Email address required");
				return false;
            }
			
			if(strlen($this->email) > MAX_EMAIL_LENGTH)
			{
                $this->email = "";
				$this->set_error("Email is too long");
				return false;
			}
			
			if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) 
			{
                $this->email = "";
				$this->set_error("Email is invalid.");
				return false;
			}
		}

        protected function set_display_name($display_name)
		{
            $this->display_name = SSDMDatabase::clean_string($display_name);
			if(empty($this->display_name))
			{
				$this->set_error("Display name required");
				return false;
			}
			
			if(strlen($this->display_name) > MAX_NAME_LENGTH)
			{
                $this->display_name = "";
				$this->set_error("Display name is too long");
				return false;
			}
			
			if(strlen($this->display_name) < MIN_NAME_LENGTH)
			{
                $this->display_name = "";
				$this->set_error("Display name is too short");
				return false;
			}
		}

        public function set_user_name($user_name)
        {
            $this->user_name = SSDMDatabase::clean_string($user_name);

            if(empty($this->user_name))
			{
				$this->set_error("User name required");
				return false;
			}
			
			if(strlen($this->user_name) > MAX_NAME_LENGTH)
			{
                $this->user_name = "";
				$this->set_error("Username is too long");
				return false;
			}
			
			if(strlen($this->user_name) < MIN_NAME_LENGTH)
			{
                $this->user_name = "";
				$this->set_error("Username is too short");
				return false;
			}
        }

        protected function validate_password($password)
		{
			if(empty($password))
			{
				$this->set_error("Password required");
				return false;
			}
			
			if(strlen($password) < MIN_PASSWORD_LENGTH)
			{
				$this->set_error("Password is too short");
				return false;
			}
		}
    }
?>