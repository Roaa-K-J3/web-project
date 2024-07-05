<?php
session_start();
// remove user from session
unset($_SESSION['user_id']);
// return 200
http_response_code(200);