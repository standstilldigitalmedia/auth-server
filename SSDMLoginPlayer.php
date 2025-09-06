<?php
    require_once("SSDMPlayer.php");

    class SSDMLoginPlayer extends SSDMPlayer
    {
        public function __construct($incoming_player)
        {
            parent::__construct($incoming_player);

			if($this->error == "")
			{
				//$this->session->login_account();
			}
        }

        private function get_player()
        {
            $sql = "SELECT password, id, auth, display_name FROM player_auth WHERE user_name=?";
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
            $sql = "UPDATE player_auth SET last_login=? WHERE id=?";
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
        /*function login_user($user_name, $password)
        {
            if(!password_verify($password, $data['password']))
            {
                return ["error" => "Wrong username or password"];
            }
        }*/
    }
?>