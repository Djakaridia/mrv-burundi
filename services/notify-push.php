<?php
$modelsDir =  __DIR__ . '/../models';
foreach (glob("$modelsDir/*.php") as $modelFile) {
    require_once $modelFile;
}

// #####################################################################
// #####################################################################
// Fonctions d'envoi de notification
function addReunionNotif($db, $name_reunion, $groupe_id, $payload)
{
    $response = ['success' => false, 'errors' => []];

    try {
        $groupe = new GroupeTravail($db);
        $groupe->id = $groupe_id;
        $data_groupe = $groupe->readById();
        if (!$data_groupe) {
            throw new Exception("Groupe introuvable");
        }

        $structure = new Structure($db);
        $structure->id = $data_groupe['monitor'];
        $data_structure = $structure->readById();
        if (!$data_structure) {
            throw new Exception("Structure introuvable");
        }

        $user = new User($db);
        $user->structure_id = $data_structure['id'];
        $data_users = $user->readByStructure();
        if (empty($data_users)) {
            throw new Exception("Aucun utilisateur trouvé dans cette structure");
        }

        foreach ($data_users as $user) {
            try {
                $notification = new Notification($db);
                $notification->titre = "Nouvelle réunion";
                $notification->message = $name_reunion . " a été ajoutée pour le groupe " . $data_groupe['name'];
                $notification->type = "info";
                $notification->entity_type = "group";
                $notification->entity_id = $groupe_id;
                $notification->user_id = $user['id'];
                $notification->add_by = $payload['user_id'];
                $notification->create();
            } catch (Exception $e) {
                error_log("Notification error: " . $e->getMessage());
            }
        }

        $response['success'] = true;
        $response['message'] = "Notifications envoyées";
    } catch (Exception $e) {
        error_log("sendReunionNotif error: " . $e->getMessage());
    }

    return $response;
}

function addProjetNotif($db, $name_projet, $projet_id, $structure_id, $payload)
{
    $response = ['success' => false, 'errors' => []];

    try {
        $structure = new Structure($db);
        $structure->id = $structure_id;
        $data_structure = $structure->readById();
        if (!$data_structure) {
            throw new Exception("Structure introuvable");
        }

        $user = new User($db);
        $user->structure_id = $data_structure['id'];
        $data_users = $user->readByStructure();
        if (empty($data_users)) {
            throw new Exception("Aucun utilisateur trouvé dans cette structure");
        }

        foreach ($data_users as $user) {
            try {
                $notification = new Notification($db);
                $notification->titre = "Nouveau projet";
                $notification->message = $name_projet . " a été ajouté pour la structure " . $data_structure['sigle'];
                $notification->type = "info";
                $notification->entity_type = "project";
                $notification->entity_id = $projet_id;
                $notification->user_id = $user['id'];
                $notification->add_by = $payload['user_id'];
                $notification->create();
            } catch (Exception $e) {
                error_log("Notification error: " . $e->getMessage());
            }
        }

        $response['success'] = true;
        $response['message'] = "Notifications envoyées";
    } catch (Exception $e) {
        error_log("sendProjetNotif error: " . $e->getMessage());
    }

    return $response;
}


