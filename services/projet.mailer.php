<?php
$routePath = '../';

require $routePath . 'vendor/autoload.php';

// Utilisation des classes de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ProjetMailer
{
    private $mail;
    private $config = [
        'charset' => 'UTF-8',
        'smtp_secure' => 'ssl',
        'debug' => 0 // 0 = off, 1 = client, 2 = client and server
    ];

    public function __construct($host, $port, $username, $password, $from_email, $from_name)
    {
        try {
            $this->mail = new PHPMailer(true);
            $this->mail->isSMTP();
            $this->mail->Host = $host;
            $this->mail->Port = $port;
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $username;
            $this->mail->Password = $password;

            $this->mail->SMTPSecure = $this->config['smtp_secure'];
            $this->mail->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];

            $this->mail->isHTML(true);
            $this->mail->CharSet = $this->config['charset'];
            $this->mail->setFrom($from_email, $from_name);

            $this->mail->SMTPDebug = $this->config['debug'];
            $this->mail->Debugoutput = function ($str, $level) {
                error_log("SMTP debug level $level: $str");
            };
        } catch (Exception $e) {
            error_log("Erreur d'initialisation PHPMailer: " . $e->getMessage());
            throw new Exception("Impossible d'initialiser le service d'envoi d'emails");
        }
    }

    //#############################################################
    // a.	Affectation d'un projet à une structure.
    public function sendAffectStructure($to, $strucname, $projetname)
    {
        $subject = "Affectation d'un projet à une structure";
        $message = "
        <html>
        <head>
            <title>Affectation d'un projet à une structure</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Bonjour, $strucname !</h2>
                <p>Le projet <strong>$projetname</strong> a été affecté à votre structure.</p>
                <p>Vous pouvez consulter le projet sur le portail <a href='https://admin.mrv-burundi.com/projects.php' target='_blank'>ici</a>.</p>
                <p>Cordialement,<br>L'équipe de support MRV - Burundi</p>
            </div>
        </body>
        </html>
        ";

        $this->mail->clearAddresses();
        $this->mail->addAddress($to);
        $this->mail->Subject = $subject;
        $this->mail->Body = $message;
        $this->mail->send();
    }

    //#############################################################
    // b.	Ajout d’actions
    public function sendAddAction($to, $strucname, $actionname, $projetname, $projetid)
    {
        $subject = "Ajout d’une action";
        $message = "
        <html>
        <head>
            <title>Ajout d’une action</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Bonjour, $strucname !</h2>
                <p>Une action <strong>$actionname</strong> a été ajouté au projet <strong>$projetname</strong>.</p>
                <p>Vous pouvez consulter le projet sur le portail <a href='https://admin.mrv-burundi.com/project_view.php?id=$projetid' target='_blank'>ici</a>.</p>
                <p>Cordialement,<br>L'équipe de support MRV - Burundi</p>
            </div>
        </body>
        </html>
        ";

        $this->mail->clearAddresses();
        $this->mail->addAddress($to);
        $this->mail->Subject = $subject;
        $this->mail->Body = $message;
        $this->mail->send();
    }

     //#############################################################
    // b.	Ajout d'une tâches.
    public function sendAddTask($to, $strucname, $taskname, $projetname, $projetid)
    {
        $subject = "Ajout d'une tâches";
        $message = "
        <html>
        <head>
            <title>Ajout d’une tâches</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Bonjour, $strucname !</h2>
                <p>Une tâches <strong>$taskname</strong> a été ajouté au projet <strong>$projetname</strong>.</p>
                <p>Vous pouvez consulter le projet sur le portail <a href='https://admin.mrv-burundi.com/project_view.php?id=$projetid' target='_blank'>ici</a>.</p>
                <p>Cordialement,<br>L'équipe de support MRV - Burundi</p>
            </div>
        </body>
        </html>
        ";

        $this->mail->clearAddresses();
        $this->mail->addAddress($to);
        $this->mail->Subject = $subject;
        $this->mail->Body = $message;
        $this->mail->send();
    }

     //#############################################################
    // b.	Ajout d'un indicateur.
    public function sendAddIndicateur($to, $strucname, $indicateurname, $projetname, $projetid)
    {
        $subject = "Ajout d’un indicateur";
        $message = "
        <html>
        <head>
            <title>Ajout d’un indicateur</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Bonjour, $strucname !</h2>
                <p>Un indicateur <strong>$indicateurname</strong> a été ajouté au projet <strong>$projetname</strong>.</p>
                <p>Vous pouvez consulter le projet sur le portail <a href='https://admin.mrv-burundi.com/project_view.php?id=$projetid' target='_blank'>ici</a>.</p>
                <p>Cordialement,<br>L'équipe de support MRV - Burundi</p>
            </div>
        </body>
        </html>
        ";

        $this->mail->clearAddresses();
        $this->mail->addAddress($to);
        $this->mail->Subject = $subject;
        $this->mail->Body = $message;
        $this->mail->send();
    }

    //#############################################################
    // c.	Clôture d'une tâche. 
    public function sendClosedTask($to, $strucname, $taskname, $projetname, $projetid)
    {
        $subject = "Clôture d'une tâche";
        $message = "
        <html>
        <head>
            <title>Clôture d'une tâche</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Bonjour, $strucname !</h2>
                <p>La tâche <strong>$taskname</strong> du projet <strong>$projetname</strong> a été clôturé.</p>
                <p>Vous pouvez consulter le projet sur le portail <a href='https://admin.mrv-burundi.com/project_view.php?id=$projetid' target='_blank'>ici</a>.</p>
                <p>Cordialement,<br>L'équipe de support MRV - Burundi</p>
            </div>
        </body>
        </html>
        ";

        $this->mail->clearAddresses();
        $this->mail->addAddress($to);
        $this->mail->Subject = $subject;
        $this->mail->Body = $message;
        $this->mail->send();
    }

    //#############################################################
    // d.	Validation et diffusion des rapports périodiques (semestriels, trimestriels).
    public function sendValideReport($to, $strucname, $reportname, $projetname)
    {
        $subject = "Validation des rapports périodiques";
        $message = "
        <html>
        <head>
            <title>Validation des rapports périodiques</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Bonjour, $strucname !</h2>
                <p>Le rapport périodique <strong>$reportname</strong> du projet <strong>$projetname</strong> a été validé.</p>
                <p>Vous pouvez consulter le rapport sur le portail <a href='https://admin.mrv-burundi.com/rapport_periodique.php' target='_blank'>ici</a>.</p>
                <p>Cordialement,<br>L'équipe de support MRV - Burundi</p>
            </div>
        </body>
        </html>
        ";

        $this->mail->clearAddresses();
        $this->mail->addAddress($to);
        $this->mail->Subject = $subject;
        $this->mail->Body = $message;
        $this->mail->send();
    }
}
