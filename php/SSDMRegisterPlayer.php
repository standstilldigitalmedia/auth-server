<?php
    require_once("SSDMPlayer.php");

    class SSDMRegisterPlayer extends SSDMPlayer
    {
		public function __construct($user_name, $display_name, $email, $password, $client_id)
        {
            $this->set_user_name($user_name);
            $this->display_name = $display_name;
            $this->set_email($email);
			$this->register_new_player($password, $client_id);
        }

        private function clean_string($string)
		{
			$string = trim($string);
			$string = strip_tags($string);
			return $string;
		}

		private function validate_password($password)
		{
			if(empty($password))
			{
				$this->error = "Password required";
				return false;
			}
			
			if(strlen($password) < MIN_PASSWORD_LENGTH)
			{
				$this->error = "Password is too short";
				return false;
			}
			return true;
		}

		private function validate_email($email)
		{
			if(empty($email))
			{
				$this->error = "Email address required";
				return false;
			}
			
			if(strlen($email) > MAX_EMAIL_LENGTH)
			{
				$this->error = "Email is too long";
				return false;
			}
			
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
			{
				$this->error = "Email is invalid.";
				return false;
			}

            if(SSDMDatabase::record_exists(PLAYER_AUTH_TABLE,"email", $email))
			{
				$this->error = "Record already exists";
				return false;
			}
			return true;
		}

		private function validate_display_name($display_name)
		{
			if(empty($display_name))
			{
				$this->error = "Display name required";
				return false;
			}
			
			if(strlen($display_name) > MAX_NAME_LENGTH)
			{
				$this->error = "Display name is too long";
				return false;
			}
			
			if(strlen($display_name) < MIN_NAME_LENGTH)
			{
				$this->error = "Display name is too short";
				return false;
			}
			
			if(SSDMDatabase::record_exists(PLAYER_AUTH_TABLE,"display_name", $display_name))
			{
				$this->error = "Record already exists";
				return false;
			}
			return true;
		}

		private function validate_user_name($user_name)
		{
			if(empty($user_name))
			{
				$this->error = "User name required";
				return false;
			}
			
			if(strlen($user_name) > MAX_NAME_LENGTH)
			{
				$this->error = "Username is too long";
				return false;
			}
			
			if(strlen($user_name) < MIN_NAME_LENGTH)
			{
				$this->error = "Username is too short";
				return false;
			}
			
			if(SSDMDatabase::record_exists(PLAYER_AUTH_TABLE,"user_name", $user_name))
			{
				$this->error = "Record already exists";
				return false;
			}
			return true;
		}

        private function validate_player($password)
        {
            if(!$this->validate_user_name($this->get_user_name()))
            {
                return false;
            }
			
			if(!$this->validate_display_name($this->display_name))
            {
                return false;
            }
			
			if(!$this->validate_email($this->get_email()))
            {
                return false;
            }
			
            if(!$this->validate_password($password))
            {
                return false;
            }
            return true;
        }

        private function clean_player()
        {
            $this->set_user_name($this->clean_string($this->get_user_name()));
			$this->display_name = $this->clean_string($this->display_name);
			$this->set_email($this->clean_string($this->get_email()));
        }

        private function add_new_player_to_database($password)
		{
            $valid_player = $this->validate_player($password);
            if(!$valid_player)
            {
                return false;
            }
			$password = password_hash($password, PASSWORD_DEFAULT);
			$sql = "INSERT INTO " . PLAYER_AUTH_TABLE . " (user_name, display_name, email, password) VALUES(?,?,?,?)";
			$arg_array = [$this->get_user_name(), $this->display_name, $this->get_email(), $password];
			$data = SSDMDatabase::db_query($sql, "ssss", $arg_array, INSERT_QUERY_TYPE);
			if(!$data)
			{
                $this->error = "Database error.";
				return false;
			}
            $this->player_id = $data['id'];
			return true;
		}

        private function send_activation_email()
        {
            $headers = "From: standstilldigitalmedia@gmail.com" . "\r\n" .
            "Reply-To: no-reply@gmail.com" . "\r\n" .
            "X-Mailer: PHP/" . phpversion();

            $subject = "Activate your Standstill Digital Media account";

            $message = "Thanks for creating an account with us. Once activated, you can use this account to log into a variety of games on the Standstill Digital Media platform. \r\n \r\n" .
            "Account Details:\r\n" .
            "User Name: " . $this->get_user_name() . "\r\n" .
            "Display Name: " . $this->display_name . "\r\n \r\n" .
            "Activation Code: \r\n" .
            $this->session->session_ticket;

            if(!mail($this->get_email(), $subject, $message, $headers)) 
            {
                $this->error = "Unable to send activation email";
                return false;
            } 
            return true;
        }

        private function register_new_player($password, $client_id)
        {
            $this->clean_player();
            if(!$this->validate_player($password))
            {
                return false;
            }
            if(!$this->add_new_player_to_database($password))
            {
                return false;
            }
            $this->session = new SSDMSession($client_id, $this->player_id);
            $this->session->create_activate_session_ticket();
            return $this->send_activation_email();
        }
    }
?>