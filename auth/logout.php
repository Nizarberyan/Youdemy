<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Auth.php';

$auth = new Auth();
$auth->logout();

header('Location: index.php');
exit;
