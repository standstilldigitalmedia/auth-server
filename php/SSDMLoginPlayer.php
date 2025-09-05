<?php
    require_once("SSDMPlayer.php");

    class SSDMLoginPlayer extends SSDMPlayer
    {
        public function __construct($user_name, $password, $client_id)
        {
            $this->set_user_name($user_name);
            $data = $this->get_player_password();
            if(!$data)
            {
                return false;
            }
            if(!$this->verify_password($data, $password))
            {
                return false;
            }
            $this->session = new SSDMSession($client_id, $data['id']);
            $this->delete_all_sessions_from_database($data['id']);
            $this->session->create_login_session_ticket();
        }

        private function verify_password($data, $password)
        {
            if(!password_verify($password, $data['password']))
            {
                $this->error = "Invalid username and password combination.";
				return false;
            }
            return true;
        }

        private function delete_all_sessions_from_database($player_id)
        {
            $sql = "DELETE FROM " . SESSIONS_TABLE . " WHERE player_id=?";
            $data = SSDMDatabase::db_query($sql, "s", [(int)$player_id], DELETE_QUERY_TYPE);
            if(!$data)
            {
                $this->error = "Invalid username and password combination.";
				return false;
            }
        }

        private function get_player_password()
        {
            $sql = "SELECT password, id FROM " . PLAYER_AUTH_TABLE . " WHERE user_name=?";
            $data = SSDMDatabase::db_query($sql, "s", [$this->get_user_name()], SELECT_QUERY_TYPE);
            if(!$data)
            {
                $this->error = "Invalid username and password combination.";
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