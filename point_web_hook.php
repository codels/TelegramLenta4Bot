<?php

include_once('base_loader.php');

error_log(json_encode(array($_REQUEST, file_get_contents('php://input'))));