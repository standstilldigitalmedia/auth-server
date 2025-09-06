<?php
	session_start();

	define('API_URL_BASE', 'localhost');
	
	define('MAX_NAME_LENGTH', 30);
	define('MAX_EMAIL_LENGTH', 255);
	define('MIN_NAME_LENGTH', 6);
	define('MIN_PASSWORD_LENGTH', 8);
	define('MAX_CLIENT_ID_LENGTH', 255);

	define('MAX_REGISTER_TICKET_LENGTH', 16);
	define('MAX_ACTIVATE_TICKET_LENGTH', 8);
	define('MAX_LOGIN_TICKET_LENGTH', 16);
	define('MAX_REMEMBER_ME_TICKET_LENGTH', 16);


	define('ACTIVATE_EXPIRATION_DATE', '+15 minutes');
	define('REMEMBER_ME_EXPIRATION_DATE', '+1 month');
	define('LOGIN_EXPIRATION_DATE', '+2 minutes');

	enum QueryType
	{
		case Insert;
		case Select;
		case Delete;
		case Update;
	}

	enum SessionType: string
	{
		case Register = "0";
		case Activate = "1";
		case Login = "2";
		case Remember_me = "3";
	}
?>