<?php
    require_once("SSDMDatabase.php");
    require_once("SSDMSession.php");

    class SSDMPlayer
    {
        private $user_name = "";
        private $email = "";
        private $auth = -1;

        public $player_id = -1;
        public $display_name = "";
        public $session = null;
        public $error = "";

        protected function set_user_name($user_name)
        {
            $this->user_name = $user_name;
        }

        protected function set_email($email)
        {
            $this->email = $email;
        }

        protected function set_auth($auth)
        {
            $this->auth = $auth;
        }

        protected function get_user_name()
        {
            return $this->user_name;
        }

        protected function get_email()
        {
            return $this->email;
        }

        protected function get_auth()
        {
            return $this->auth;
        }

        /*private function set_player_by_user_name($user_name)
		{
			$sql = "SELECT id, display_name, email, password, auth FROM " . PLAYER_AUTH_TABLE . " WHERE user_name = ?";
			$data = SSDMDatabase::query($sql, "s", [$user_name]);
			if(!$data)
			{
                $this->error = "User not found";
				return false;
			}
			$this->set_new_player($user_name, $data['display_name'], $data['email']);
			$this->player_id = $data['id'];
            $this->auth = $data['auth'];
			return true;
		}*/
    }
?>