<?php

session_start();

include_once 'initdb.php';
include_once 'pages.php';

ensureLoggedIn();

echo "<h2>Pending Users</h2>";
dumpPendingUsers();

echo "<h2>Users</h2>";
dumpUsers();

?>
