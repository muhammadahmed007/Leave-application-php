<?php
/** Create DB Folder if not existing yet */
if(!is_dir(__DIR__.'./db'))
    mkdir(__DIR__.'./db');
/** Define DB File Path */
if(!defined('db_file')) define('db_file',__DIR__.'./db/leave_db.db');
/** Define DB File Path */
if(!defined('tZone')) define('tZone',"Asia/Manila");
if(!defined('dZone')) define('dZone',ini_get('date.timezone'));

/** DB Connection Class */
Class DBConnection extends SQLite3{
    protected $db;
    function __construct(){
        /** Opening Database */
        $this->open(db_file);
        $this->exec("PRAGMA foreign_keys = ON;");
        /** Closing Database */
        $this->exec("CREATE TABLE IF NOT EXISTS `user_list` (
            `user_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `fullname` INTEGER NOT NULL,
            `username` TEXT NOT NULL,
            `password` TEXT NOT NULL,
            `type` TINYINT(1) NOT NULL Default 0,
            `status` TINYINT(1) NOT NULL Default 0,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"); 
        $this->exec("CREATE TABLE IF NOT EXISTS `employee_list` (
            `employee_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `code` INTEGER NOT NULL,
            `firstname` TEXT NOT NULL,
            `middlename` TEXT NOT NULL,
            `lastname` TEXT NOT NULL,
            `email` TEXT NOT NULL,
            `contact` TEXT NOT NULL,
            `department` TEXT NOT NULL,
            `designation` TEXT NOT NULL,
            `status` TINYINT(2) NOT NULL Default 1,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $this->exec("CREATE TABLE IF NOT EXISTS `leave_priv_list` (
            `leave_priv_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `employee_id` INTEGER NOT NULL,
            `name` TEXT NOT NULL,
            `credits` TINYINT(5) NOT NULL,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(`employee_id`) REFERENCES `employee_list`(`employee_id`)
        )");
         $this->exec("CREATE TABLE IF NOT EXISTS `application_list` (
            `application_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `employee_id` INTEGER NOT NULL,
            `leave_priv_id` INTEGER NOT NULL,
            `from` DATE NOT NULL,
            `to` DATE NOT NULL,
            `type` TINYINT(1) NOT NULL DEFAULT 1,
            `remarks` TEXT NULL DEFAULT NULL,
            `status` TINYINT(1) NOT NULL DEFAULT 1,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(`employee_id`) REFERENCES `employee_list`(`employee_id`),
            FOREIGN KEY(`leave_priv_id`) REFERENCES `leave_priv_list`(`leave_priv_id`)
        )");
        $this->exec("INSERT OR IGNORE INTO `user_list` VALUES (1, 'Administrator', 'admin', '$2y$10\$Aj/jjNbcT1vNZrp.9ELpheF9rgjP9RInWb8RSuTGAKcoKJE26HCb6', 1, 1, CURRENT_TIMESTAMP)");

    }
    function __destruct(){
         $this->close();
    }
}

$conn = new DBConnection();