<?php

class ProjetGreen
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

    // #########################################################
    // a. Notification de creation de projet
    public function sendAssignProject($number, $username, $projet_name)
    {
        $number = str_replace(' ', '', $number);
        $message = "🔹 *MRV-Burundi - Notification d'attribution de projet* 🔹\n\n" .
            "Cher(e) " . $username . ",\n" .
            "Un projet a été attribué à vous sur notre plateforme de Monitoring et Reporting.\n" .
            "Vous pouvez consulter votre projet *" . $projet_name . "* pour plus de détails.\n" .
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

    // #########################################################
    // b. Notification de création de tache
    public function sendAssignTask($number, $username, $tache_name)
    {
        $number = str_replace(' ', '', $number);
        $message = "🔹 *MRV-Burundi - Notification d'attribution de tâche* 🔹\n\n" .
            "Cher(e) " . $username . ",\n" .
            "Une tâche a été attribuée à vous sur notre plateforme de Monitoring et Reporting.\n" .
            "Vous pouvez consulter votre tâche *" . $tache_name . "* pour plus de détails.\n" .
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

    
}
