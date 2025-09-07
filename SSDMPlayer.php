<?php
    require_once("SSDMDatabase.php");
    require_once("SSDMSession.php");
    require_once("SSDMPlayerBase.php");

    class SSDMPlayer extends SSDMPlayerBase
    {
        public function activate_account($incoming_player)
		{
            if(!property_exists($incoming_player,"session_ticket"))
            {
                $this->set_error("No session ticket");
                return;
            }
            $this->session = new SSDMSession($this->request_type, $incoming_player->client_id, $incoming_player->player_id, $incoming_player->session_ticket);
			if(!$this->session->error != "")
			{
				$this->set_error("Unable to activate account. active");
				return false;
			}
			return $this->activate_account_in_database();
		}

        function register_player($incoming_player)
        {
            if(!property_exists($incoming_player,"user_name"))
            {
                $this->set_error("No username");
                return;
            }
            if(!property_exists($incoming_player,"display_name"))
            {
                $this->set_error("No display name");
                return;
            }
            if(!property_exists($incoming_player,"email"))
            {
                $this->set_error("No email");
                return;
            }
            if(!property_exists($incoming_player,"password"))
            {
                $this->set_error("No password");
                return;
            }
            $this->set_user_name($incoming_player->user_name);
            $this->set_display_name($incoming_player->display_name);
            $this->set_email($incoming_player->email);
            if($this->player_exists())
            {
                $this->set_error("Record already exists");
                return;
            }
            $this->add_new_player_to_database($incoming_player->password);;
            $this->session = new SSDMSession($this->request_type, $incoming_player->client_id, $this->player_id);
            $this->send_activation_email();
        }

        public function __construct($incoming_player)
        {
            if(!isset($incoming_player) || !property_exists($incoming_player, 'request_type'))
            {
                $this->set_error("No request type set");
                return;
            }
            if(!property_exists($incoming_player, "client_id"))
            {
                $this->set_error("No client id set");
                return;
            }
            $this->set_request_type($incoming_player->request_type);
            switch($this->request_type)
            {
                case REGISTER_SESSION:
                    $this->register_player($incoming_player);
                    break;
                case ACTIVATE_SESSION:
                    $this->activate_account($incoming_player);
                    break;
                case LOGIN_SESSION:

                    break;
            }
        }
    }
?>