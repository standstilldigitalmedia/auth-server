<?php
    require_once("SSDMPlayer.php");

    class SSDMActivatePlayer extends SSDMPlayer
    {
        public function __construct($client_id, $player_id, $session_ticket)
        {
            $this->session = new SSDMSession($client_id, $player_id, $session_ticket);
            $this->player_id = $player_id;
            $this->activate_account();
        }

        private function activate_account_in_database()
		{
			$sql = "UPDATE " . PLAYER_AUTH_TABLE . " SET auth=1 WHERE id=?";
			if(!SSDMDatabase::db_query($sql, "i", [$this->player_id], UPDATE_QUERY_TYPE))
			{
				$this->error = "Database error";
				return false;
			}
			return true;
		}

        public function activate_account()
		{
			if(!$this->session->activate_account())
			{
				return false;
			}
			return $this->activate_account_in_database();
		}
    }
?>