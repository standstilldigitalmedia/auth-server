<?php
    require_once("SSDMDatabase.php");
    require_once("SSDMSession.php");

    class SSDMPlayer
    {
        protected $user_name = "";
        protected $email = "";
        protected $auth = -1;
        protected $session = null;

        public $request_type = -1;
        public $player_id = -1;
        public $display_name = "";
        public $session_ticket = "";
        
        public $error = "";

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

        protected function player_exists()
        {
            $sql = "SELECT id FROM player_auth WHERE email=?";
            $data = SSDMDatabase::db_query($sql, "s", [$this->email], QueryType::Select);
            if($data)
			{
				$this->set_error("Record already exists");
				return true;
			}

            $sql = "SELECT id FROM player_auth WHERE display_name=?";
            $data = SSDMDatabase::db_query($sql, "s", [$this->display_name], QueryType::Select);
            if($data)
			{
				$this->set_error("Record already exists");
				return true;
			}

            $sql = "SELECT id FROM player_auth WHERE user_name=?";
            $data = SSDMDatabase::db_query($sql, "s", [$this->user_name], QueryType::Select);
            if($data)
			{
				$this->set_error("Record already exists");
				return true;
			}
            return false;
        }

        public function __construct($player_request)
        {
            if(!$player_request)
            {
                $this->set_error("Request is empty");
                return false;
            }

            if(property_exists($player_request, "request_type"))
            {
                $this->set_request_type($player_request->request_type);
            }
            else
            {
                $this->set_error("Request type required");
                return false;
            }
             
            $client_id = "";
            if(property_exists($player_request, "client_id"))
            {
                $client_id = $player_request->client_id;
            }
            else
            {
                $this->set_error("Client ID required");
                return false;
            }

            $session_ticket = "";
            if(property_exists($player_request, "session_ticket"))
            {
                $session_ticket = $player_request->session_ticket;
            }

            if(property_exists($player_request, "player_id"))
            {
                $this->set_player_id($player_request->player_id);
            }
            if(property_exists($player_request, "user_name"))
            {
                $this->set_user_name($player_request->user_name);
            }
            if(property_exists($player_request, "display_name"))
            {
                $this->set_display_name($player_request->display_name);
            }
            if(property_exists($player_request, "email"))
            {
                $this->set_email($player_request->email);
            }
            if(property_exists($player_request, "auth"))
            {
                $this->set_auth($player_request->auth);
            }
            if(property_exists($player_request, "error"))
            {
                $this->set_error($player_request->error);
            }

            if($this->error == "")
            {
                $this->session = new SSDMSession($this->request_type, $client_id, $this->player_id, $session_ticket);
            }
        }
        
    }
?>