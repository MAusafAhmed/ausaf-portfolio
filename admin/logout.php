<?php
require_once __DIR__ . '/../api/auth.php';

log_out();
header('Location: index.php');
exit;
