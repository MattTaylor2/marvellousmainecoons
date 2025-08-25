<?php
// includes/db.php

// Load the PDO instance from config.php
require_once __DIR__ . '/../config.php';

/**
 * Return the central PDO instance.
 *
 * @return \PDO
 */
function getDb(): \PDO
{
    global $pdo;
    return $pdo;
}
