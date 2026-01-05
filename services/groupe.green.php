<?php

class GroupeGreen
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
    // Send notification to user when he is added to a group
    public function sendAddMember($number, $username, $groupe_name)
    {
        $number = str_replace(' ', '', $number);
        $message = "🔹 *MRV-Burundi - Notification d'ajout de membre* 🔹\n\n" .
            "Cher(e) " . $username . ",\n" .
            "Vous avez été ajouté au groupe *" . $groupe_name . "* sur notre plateforme de Monitoring et Reporting.\n" .
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
    // Send notification to user when he is removed from a group
    public function sendRemoveMember($number, $username, $groupe_name)
    {
        $number = str_replace(' ', '', $number);
        $message = "🔹 *MRV-Burundi - Notification de suppression de membre* 🔹\n\n" .
            "Cher(e) " . $username . ",\n" .
            "Vous avez été supprimé du groupe *" . $groupe_name . "* sur notre plateforme de Monitoring et Reporting.\n" .
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
    // Send notification to user when new meet is created
    public function sendNewMeet($number, $username, $meet_name, $meet_date, $meet_time)
    {
        $number = str_replace(' ', '', $number);
        $message = "🔹 *MRV-Burundi - Notification de création de réunion* 🔹\n\n" .
            "Cher(e) " . $username . ",\n" .
            "Une nouvelle réunion *" . $meet_name . "* a été créée sur notre plateforme de Monitoring et Reporting.\n" .
            "La réunion se déroulera le *" . $meet_date . "* à *" . $meet_time . "*.\n" .
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
