<?php
$autoloadPath = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    die("Veuillez installer les dépendances Composer. Exécutez 'composer install' dans le dossier racine de votre projet.");
}

require_once $autoloadPath;

// Utilisation des classes de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class UserMailer
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
    // a.	Création d'un compte.
    public function sendAccountCreate($to, $username, $login)
    {
        $subject = "Confirmation de votre inscription";
        $message = "
        <html>
        <head>
            <title>Confirmation d'inscription</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .button { 
                    display: inline-block; 
                    padding: 10px 20px; 
                    background-color: #4CAF50; 
                    color: white; 
                    text-decoration: none; 
                    border-radius: 5px; 
                    margin: 15px 0;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Bienvenue, $username !</h2>
                <p>Vous avez été ajouté(e) à la plateforme MRV - Burundi.</p>
                <p>Vous pouvez vous connecter en utilisant vos identifiants:</p>
                <ul>
                    <li>Identifiant: $login</li>
                    <li>Mot de passe: [Mot de passe fourni lors de la création du compte]</li>
                </ul>
                <p>Si vous avez besoin d'aide, contactez-nous à info@mrv-burundi.com</p>
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
    // a.   Bienvenue d'un utilisateur.
    public function sendWelcome($to, $username)
    {
        $subject = "Bienvenue sur notre plateforme";
        $message = "
        <html>
        <head>
            <title>Bienvenue</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Bienvenue parmi nous, $username !</h2>
                <p>Votre compte a été activé avec succès. Vous pouvez maintenant profiter de tous nos services.</p>
                <p>Voici quelques informations utiles :</p>
                <ul>
                    <li>Connectez-vous régulièrement pour voir les nouveautés</li>
                    <li>Consultez notre FAQ pour toute question</li>
                    <li>Contactez-nous si vous avez besoin d'aide</li>
                </ul>
                <p>Nous vous souhaitons une excellente expérience sur notre plateforme.</p>
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
    // b.	Connexion d'un utilisateur.
    public function sendLogin($to, $username, $loginTime, $ipAddress)
    {
        $subject = "Notification de connexion à votre compte";
        $message = "
        <html>
        <head>
            <title>Notification de connexion</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .alert { color: #cc0000; font-weight: bold; }
                .note { font-size: 0.8em; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Bonjour $username,</h2>
                <p>Une connexion à votre compte a été détectée :</p>
                <ul>
                    <li>Date et heure : $loginTime</li>
                    <li>Adresse IP : $ipAddress</li>
                </ul>
                <p class='alert'>Si vous n'êtes pas à l'origine de cette connexion, veuillez changer votre mot de passe immédiatement et nous contacter.</p>
                <p class='note'>Si vous avez besoin d'aide, contactez-nous à info@mrv-burundi.com</p>
                <p>Cordialement,<br>L'équipe de sécurité</p>
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
    // d.   Réinitialisation du mot de passe.
    public function sendPasswordReset($to, $username, $code)
    {
        $subject = "Réinitialisation de votre mot de passe";
        $message = "
        <html>
        <head>
            <title>Réinitialisation de mot de passe</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .note { font-size: 0.8em; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Bonjour $username,</h2>
                <p>Vous avez demandé à réinitialiser votre mot de passe. 
                <p>Veuillez entrer le code de vérification suivant : <strong>$code</strong></p>
                <p class='note'>Si vous n'êtes pas à l'origine de cette demande, veuillez ignorer cet email. Ce code expirera dans 24 heures.</p>
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
    // c.	Modification des informations du compte.
    public function sendAccountUpdate($to, $username)
    {
        $subject = "Confirmation de modification de votre profil";
        $message = "
        <html>
        <head>
            <title>Modification de profil</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .note { font-size: 0.8em; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Bonjour $username,</h2>
                <p>Vos informations de compte ont été mises à jour avec succès.</p>
                <p class='note'>Si vous n'êtes pas à l'origine de cette modification, veuillez nous contacter.</p>
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
    // d.	Suppression du compte.
    public function sendAccountDelete($to, $username)
    {
        $subject = "Votre compte a été supprimé";
        $message = "
        <html>
        <head>
            <title>Compte supprimé</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Bonjour $username,</h2>
                <p>Conformément à votre demande, votre compte a été définitivement supprimé de notre système.</p>
                
                <p>Toutes vos données personnelles ont été effacées conformément à notre politique de confidentialité.</p>
                
                <p>Nous regrettons votre départ et espérons vous revoir bientôt.</p>
                
                <p class='note'>Si cette suppression a été effectuée par erreur, vous avez 7 jours pour nous contacter à info@mrv-burundi.com pour tenter de récupérer votre compte.</p>
                
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
    // d.	Désactivation du compte.
    public function sendAccountDeactivate($to, $username)
    {
        $subject = "Votre compte a été désactivé";
        $message = "
        <html>
        <head>
            <title>Compte désactivé</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Bonjour $username,</h2>
                <p>Votre compte a été désactivé. Vous ne pouvez plus accéder à nos services pour le moment.</p>
                <p>Si vous souhaitez réactiver votre compte, veuillez nous contacter à info@mrv-burundi.com.</p>
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
    // e.	Activation du compte.
    public function sendAccountActivate($to, $username)
    {
        $subject = "Votre compte a été activé";
        $message = "
        <html>
        <head>
            <title>Compte activé</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Bonjour $username,</h2>
                <p>Votre compte a été activé avec succès. </p>
                <p>Vous pouvez maintenant vous connecter à votre compte avec vos identifiants habituels.</p>
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
