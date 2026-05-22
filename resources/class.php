<?php
	require_once("objects/db_config.php");
	require_once("objects/main_class.php");
	require_once("migrations.php");
	(new MigrationRunner())->runPending();
?>