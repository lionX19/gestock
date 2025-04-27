<?php

session_start();
require_once("../db/db.php");

if (isset($_SESSION["id"])) {
    $id = $_SESSION["id"];
}
