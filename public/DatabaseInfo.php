
<?php

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class DatabaseInfo
{
    public $serverName; 
    public $userName; 
    public $password; 
    public $dbName; 
}

function getDatabaseInfo()
{
    $result = new DatabaseInfo(); 
    $result->serverName = "localhost";
    $result->userName = "nf-dienstplan-user";
    $result->password = "DesN3wForce,Ne,Des1sSch0EchtFet7.";
    $result->dbName = "NewForceRoster"; 
    return $result; 
}

class InstallInfo
{
    public $adminPassword;
    public $adminEmail;
    public $installPassword;
}

function getInstallInfo()
{
    $result = new InstallInfo();
    $result->installPassword = "SuperSecret|Install.P4ssw0rd"; // Password that is required to operate install.php page
    $result->adminPassword = "adminPW"; // Initial password for admin account when resetting/installing
    $result->adminEmail = "admin@newforcedienstplan.de"; // Initial email for admin account when resetting/installing
    return $result;
}

function makePHPMail()
{
    $mail = new PHPMailer(true);

    // SMTP configuration
    $mail->isSMTP();
    $mail->Host = 'newforce.de';
    $mail->SMTPAuth = true;
    $mail->Username = 'dienstplan-neu@newforce.de';
    $mail->Password = 'my-password'; // Replace with your app-specific password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    // from
    $mail->setFrom('dienstplan@newforce.de', 'New Force Dienstplaner');

    return $mail;
}

?>
