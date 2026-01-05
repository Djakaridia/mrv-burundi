<?php
class User {
    private $conn;
    private $table = 't_users';

    public $id;
    public $nom;
    public $prenom;
    public $password;
    public $username;
    public $role_id;
    public $validity;
    public $email;
    public $phone;
    public $structure_id;
    public $fonction;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (nom, prenom, username, password, role_id, validity, email, structure_id, phone, fonction) VALUES (:nom, :prenom, :username, :password, :role_id, true, :email, :structure_id, :phone, :fonction)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nom', $this->nom);
        $stmt->bindParam(':prenom', $this->prenom);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':role_id', $this->role_id);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':structure_id', $this->structure_id);
        $stmt->bindParam(':fonction', $this->fonction);

        if ($stmt->execute()) {
            $queryPass = "CALL pc_maj_pass_user(:username, :password)";
            $stmtPass = $this->conn->prepare($queryPass);
            $stmtPass->bindParam(':username', $this->username);
            $stmtPass->bindParam(':password', $this->password);
            $stmtPass->execute();

            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readById() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function profilUser($id) {
        $query = "SELECT nom, prenom, username, " . $this->table . ".email, " . $this->table . ".phone, sigle, t_roles.name, fonction, " . $this->table . ".state, " . $this->table . ".updated_at  FROM " . $this->table . " 
        inner join t_structures on t_structures.id = " . $this->table . ".structure_id 
        inner join t_roles on t_roles.id = " . $this->table . ".role_id 
        WHERE " . $this->table . ".id =  :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readByUsername() {
        $query = "SELECT * FROM " . $this->table . " WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $this->username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readByEmail() {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function readByStructure(){
        $query = "SELECT * FROM " . $this->table . " WHERE structure_id = :structure_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':structure_id', $this->structure_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function update() {
        $query = "UPDATE " . $this->table . " SET nom = :nom, prenom = :prenom, username = :username, role_id = :role_id, structure_id = :structure_id, phone = :phone, fonction = :fonction WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nom', $this->nom);
        $stmt->bindParam(':prenom', $this->prenom);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':role_id', $this->role_id);
        $stmt->bindParam(':structure_id', $this->structure_id);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':fonction', $this->fonction);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateState($state) {
        $query = "UPDATE " . $this->table . " SET state = :state WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':state', $state);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function authenticate($username, $password) {
        $query = "SELECT fc_check_login_mdp(:username, :password) as res";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row['res'] == 'O') {
            return true;
        }
        return false;
    }

    public function resetPassword($username, $password) {
        $query = "CALL pc_maj_pass_user(:username, :password)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function createCodeVerify($code) {
        $query = "INSERT INTO t_codes_verify (email, code) VALUES (:email, :code)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readCodeVerify($code) {
        $query = "SELECT * FROM t_codes_verify WHERE email = :email AND code = :code limit 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteCodeVerify() {
        $query = "DELETE FROM t_codes_verify WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>