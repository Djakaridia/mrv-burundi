<?php
$routePath = '../';

require $routePath . 'vendor/autoload.php';

// Utilisation des classes de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class GroupeMailer
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
    // a.	Clôture d'un groupe.
    public function sendClosed($to, $username, $groupeName)
    {
        $subject = "Clôture d'un groupe";
        $message = "
        <html>
        <head>
            <title>Clôture d'un groupe</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Bonjour, $username !</h2>
                <p>Le groupe $groupeName a été clôturé.</p>
                <p>Vous pouvez le rouvrir si nécessaire.</p>
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
    // b.	Non-tenue d'une réunion dans les délais prévus.
    public function sendOutMeet($to, $username, $meetname)
    {
        $subject = "Non-tenue d'une réunion dans les délais prévus";
        $message = "
        <html>
        <head>
            <title>Non-tenue d'une réunion dans les délais prévus</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Bonjour, $username !</h2>
                <p>La réunion $meetname n'a pas été tenue dans les délais prévus.</p>
                <p>Vous pouvez la retenir si nécessaire.</p>
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
    // c.	Publication d'un PV de réunion.
    public function sendPublishPV($to, $username, $meetname)
    {
        $subject = "Publication d'un PV de réunion";
        $message = "
        <html>
        <head>
            <title>Publication d'un PV de réunion</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Bonjour $username,</h2>
                <p>Le PV de la réunion $meetname a été publié.</p>
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
    // d.	Annonce d’une rencontre ou activité.
    public function sendAddMeet($to, $username, $meetname, $meetdate)
    {
        $subject = "Annonce d’une activité";
        $message = "
        <html>
        <head>
            <title>Annonce d’une activité</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Bonjour $username,</h2>
                <p>Une nouvelle réunion a été annoncée :</p>
                <p>La réunion <strong>$meetname</strong> a été annoncée pour le <strong>$meetdate</strong>.</p>
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
    // e.   Ajoute d'un membre au groupe.
    public function sendAddMember($to, $username, $groupeName)
    {
        $subject = "Ajout d'un membre au groupe";
        $message = "
        <html>
        <head>
            <title>Ajout d'un membre au groupe</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Bonjour $username,</h2>
                <p>Vous avez été ajouté au groupe $groupeName.</p>
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
