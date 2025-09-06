<?php
    require_once("SSDMPlayer.php");

    class SSDMRegisterPlayer extends SSDMPlayer
    {
		public function __construct($incoming_player)
        {
			parent::__construct($incoming_player);

			if($this->error == "")
			{
				$this->register_new_player($incoming_player->password);
			}
        }

        private function add_new_player_to_database($password)
		{
			$password = password_hash($password, PASSWORD_DEFAULT);
			$sql = "INSERT INTO player_auth (user_name, display_name, email, password) VALUES(?,?,?,?)";
			$arg_array = [$this->user_name, $this->display_name, $this->email, $password];
			$data = SSDMDatabase::db_query($sql, "ssss", $arg_array, QueryType::Insert);
			if(!$data)
			{
				SSDMDatabase::write_db_error("Unable to add new player to database");
                $this->set_error("Database error.");
				return false;
			}
            $this->player_id = $data['id'];
			return true;
		}

        private function send_activation_email()
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

        private function register_new_player($password)
        {
            if(!$this->add_new_player_to_database($password))
            {
				$this->set_error("Database error");
                return false;
            }
            return $this->send_activation_email();
        }
    }
?>