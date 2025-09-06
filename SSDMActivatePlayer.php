<?php
    require_once("SSDMPlayer.php");

    class SSDMActivatePlayer extends SSDMPlayer
    {
        public function __construct($incoming_player)
        {
			parent::__construct($incoming_player);

			if($this->error == "")
			{
				$this->activate_account();
			}
        }

        private function activate_account_in_database()
		{
			$sql = "UPDATE player_auth SET auth=1 WHERE id=?";
			if(!SSDMDatabase::db_query($sql, "i", [$this->player_id], QueryType::Update))
			{
				SSDMDatabase::write_db_error("Unable to update player auth in activate_account_in_database");
				$this->set_error("Database error");
				return false;
			}
			return true;
		}

        public function activate_account()
		{
			if(!$this->session->activate_account())
			{
				$this->set_error("Unable to activate account. active");
				return false;
			}
			return $this->activate_account_in_database();
		}
    }
?>