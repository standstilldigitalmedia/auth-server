<?php
	session_start();
	define('DB_SERVER', 'localhost');
	define('DB_DATABASE', 'standstill');
	define('PLAYER_AUTH_TABLE', 'player_auth');
	define('SESSIONS_TABLE', 'sessions');

	define('API_URL_BASE', 'localhost');
	
	define('MAX_NAME_LENGTH', 30);
	define('MAX_EMAIL_LENGTH', 255);
	define('MIN_NAME_LENGTH', 6);
	define('MIN_PASSWORD_LENGTH', 8);
	define('MAX_ACTIVATE_TICKET_LENGTH', 8);
	define('MAX_LOGIN_TICKET_LENGTH', 16);

	define('REGISTER_NEW_PLAYER_SESSION_TYPE', 0);
	define('ACTIVATE_SESSION_TYPE', 1);
	define('LOGIN_SESSION_TYPE', 2);
	define('REMEMBER_ME_SESSION_TYPE', 2);

	define('ACTIVATE_EXPIRATION_DATE', '+15 minutes');
	define('REMEMBER_ME_EXPIRATION_DATE', '+1 month');
	define('LOGIN_EXPIRATION_DATE', '+2 minutes');

	define('INSERT_QUERY_TYPE', 0);
	define("SELECT_QUERY_TYPE", 1);
	define('DELETE_QUERY_TYPE', 2);
	define('UPDATE_QUERY_TYPE', 3);
?>