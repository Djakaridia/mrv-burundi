<?php

class UserGreen
{
    private $urlInstance;
    private $indicatifNumber;

    public function __construct($apiIdInstance, $apiTokenInstance, $indicatifNumber)
    {
        $this->indicatifNumber = $indicatifNumber;
        $this->urlInstance = "https://api.green-api.com/waInstance" . $apiIdInstance . "/sendMessage/" . $apiTokenInstance;
    }

    private function getContextConfig($number, $message){
        $options = array(
            'http' => array(
                'header' => "Content-Type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode(['chatId' => "$this->indicatifNumber$number@c.us", 'message' => $message,])
            )
        );

        $context = stream_context_create($options);
        return $context;
    }


    //#############################################################
    // a. Notification de création de compte
    public function sendCreateAccount($number, $username, $email)
    {
        $number = str_replace(' ', '', $number);
        $message = "🔹 *MRV-Burundi - Notification de création de compte* 🔹\n\n" .
            "Cher(e) " . $username . ",\n" .
            "Votre compte a été créé avec succès sur notre plateforme de Monitoring et Reporting.\n" .
            "📧 Un email contenant vos informations de connexion a été envoyé à : $email\n" .
            "Pour toute assistance, veuillez contacter notre équipe support.\n" .
            "\nCordialement,\n" .
            "L'équipe MRV-Burundi";

        $context = $this->getContextConfig($number, $message);
        $response = file_get_contents($this->urlInstance, false, $context);
        $data = json_decode($response, true);

        if (isset($data['idMessage'])) {
            return json_encode(['Message ID: ' => $data['idMessage']]);
        } else {
            return json_encode(['Error: ' => 'idMessage not found in the response.']);
        }
    }

    //#############################################################
    // b. Notification de suppression de compte
    public function sendDeleteAccount($number, $username)
    {
        $number = str_replace(' ', '', $number);
        $message = "🔹 *MRV-Burundi - Notification de suppression de compte* 🔹\n\n" .
            "Cher(e) " . $username . ",\n" .
            "Votre compte a été supprimé avec succès sur notre plateforme de Monitoring et Reporting.\n" .
            "Si vous n'êtes pas à l'origine de cette suppression, veuillez contacter notre équipe support.\n" .
            "Pour toute assistance, veuillez contacter notre équipe support.\n" .
            "\nCordialement,\n" .
            "L'équipe MRV-Burundi";

        $context = $this->getContextConfig($number, $message);
        $response = file_get_contents($this->urlInstance, false, $context);
        $data = json_decode($response, true);

        if (isset($data['idMessage'])) {
            return json_encode(['Message ID: ' => $data['idMessage']]);
        } else {
            return json_encode(['Error: ' => 'idMessage not found in the response.']);
        }
    }

    //#############################################################
    // c. Notification de blocage de compte
    public function sendAccountDeactivate($number, $username)
    {
        $number = str_replace(' ', '', $number);
        $message = "🔹 *MRV-Burundi - Notification de blocage de compte* 🔹\n\n" .
            "Cher(e) " . $username . ",\n" .
            "Votre compte a été bloqué sur notre plateforme de Monitoring et Reporting.\n" .
            "Si vous n'êtes pas à l'origine de ce blocage, veuillez contacter notre équipe support.\n" .
            "Pour toute assistance, veuillez contacter notre équipe support.\n" .
            "\nCordialement,\n" .
            "L'équipe MRV-Burundi";

        $context = $this->getContextConfig($number, $message);
        $response = file_get_contents($this->urlInstance, false, $context);
        $data = json_decode($response, true);

        if (isset($data['idMessage'])) {
            return json_encode(['Message ID: ' => $data['idMessage']]);
        } else {
            return json_encode(['Error: ' => 'idMessage not found in the response.']);
        }
    }

    //#############################################################
    // d. Notification de réinitialisation de mot de passe
    public function sendAccountActivate($number, $username)
    {
        $number = str_replace(' ', '', $number);
        $message = "🔹 *MRV-Burundi - Notification d'activation de compte* 🔹\n\n" .
            "Cher(e) " . $username . ",\n" .
            "Votre compte a été activé sur notre plateforme de Monitoring et Reporting.\n" .
            "Votre compte est maintenant actif et vous pouvez vous connecter avec vos identifiants.\n" .
            "Pour toute assistance, veuillez contacter notre équipe support.\n" .
            "\nCordialement,\n" .
            "L'équipe MRV-Burundi";

        $context = $this->getContextConfig($number, $message);
        $response = file_get_contents($this->urlInstance, false, $context);
        $data = json_decode($response, true);

        if (isset($data['idMessage'])) {
            return json_encode(['Message ID: ' => $data['idMessage']]);
        } else {
            return json_encode(['Error: ' => 'idMessage not found in the response.']);
        }
    }

    //#############################################################
    // e. Notification de réinitialisation de mot de passe
    public function sendPasswordCode($number, $username, $code)
    {
        $number = str_replace(' ', '', $number);
        $message = "🔹 *MRV-Burundi - Réinitialisation de mot de passe* 🔹\n\n" .
            "Cher(e) " . $username . ",\n" .
            "Votre demande de modification de mot de passe a bien été prise en compte.\n" .
            "Votre code de vérification est : *" . $code . "*\n" .
            "Ce code est valable pour une durée limitée. Ne le partagez avec personne.\n" .
            "Si vous n'êtes pas à l'origine de cette demande, veuillez contacter immédiatement notre support.\n" .
            "\nCordialement,\n" .
            "L'équipe de sécurité MRV-Burundi";

        $context = $this->getContextConfig($number, $message);
        $response = file_get_contents($this->urlInstance, false, $context);
        $data = json_decode($response, true);

        if (isset($data['idMessage'])) {
            return json_encode(['Message ID: ' => $data['idMessage']]);
        } else {
            return json_encode(['Error: ' => 'idMessage not found in the response.']);
        }
    }
}
