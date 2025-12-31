<?php
/**
 * ================================
 * Modèle : Admin
 * Projet : IDMC Career Center (CSI)
 * ================================
 * Gère uniquement la logique métier ADMIN :
 *  - statistiques
 *  - création de comptes internes
 *  - récupération des comptes
 */

require_once __DIR__ . '/../config/database.php';

class Admin
{
    /**
     * Connexion PDO PostgreSQL
     */
    private PDO $db;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * ================================
     * STATISTIQUES ADMIN
     * ================================
     */
    public function getStats(): array
    {
        // Total utilisateurs
        $totalUsers = (int) $this->db
            ->query("SELECT COUNT(*) FROM utilisateur")
            ->fetchColumn();

        // Total entreprises
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM utilisateur WHERE role = :role"
        );
        $stmt->execute(['role' => 'ENTREPRISE']);

        $totalEntreprises = (int) $stmt->fetchColumn();

        return [
            'total_users'       => $totalUsers,
            'total_entreprises' => $totalEntreprises
        ];
    }

    /**
     * ================================
     * LISTE DES COMPTES INTERNES
     * ================================
     */
    public function getComptesInternes(): array
    {
        $sql = "
            SELECT 
                u.idutilisateur,
                u.nom,
                u.prenom,
                u.email,
                u.role,
                u.statut,
                c.identifiant,
                c.date_creation
            FROM utilisateur u
            JOIN compte c ON c.idutilisateur = u.idutilisateur
            WHERE u.role IN ('ENSEIGNANT', 'SECRETAIRE')
            ORDER BY u.role, u.nom, u.prenom
        ";

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * ================================
     * CRÉATION D’UN COMPTE INTERNE
     * ================================
     */
    public function creerCompteInterne(array $data, int $adminId): bool
    {
        try {
            $this->db->beginTransaction();

            /**
             * 1️⃣ Création utilisateur
             */
            $stmt = $this->db->prepare("
                INSERT INTO utilisateur (nom, prenom, email, role, statut)
                VALUES (:nom, :prenom, :email, :role, 'ACTIF')
                RETURNING idutilisateur
            ");

            $stmt->execute([
                'nom'    => $data['nom'],
                'prenom' => $data['prenom'],
                'email'  => $data['email'],
                'role'   => $data['role']
            ]);

            $idUtilisateur = (int) $stmt->fetchColumn();

            /**
             * 2️⃣ Table métier liée au rôle
             */
            if ($data['role'] === 'ENSEIGNANT') {
                $this->db->prepare(
                    "INSERT INTO enseignant (idutilisateur) VALUES (:id)"
                )->execute(['id' => $idUtilisateur]);
            }

            if ($data['role'] === 'SECRETAIRE') {
                $this->db->prepare(
                    "INSERT INTO secretaire (idutilisateur, en_conge)
                     VALUES (:id, FALSE)"
                )->execute(['id' => $idUtilisateur]);
            }

            /**
             * 3️⃣ Création du compte
             * → identifiant = email
             * → mot de passe par défaut
             */
            $motDePasseParDefaut = 'csi2025';
            $hash = password_hash($motDePasseParDefaut, PASSWORD_DEFAULT);

            $stmt = $this->db->prepare("
                INSERT INTO compte (identifiant, mdp, idutilisateur, id_admin_createur)
                VALUES (:identifiant, :mdp, :idutilisateur, :admin)
            ");

            $stmt->execute([
                'identifiant'   => $data['email'],
                'mdp'           => $hash,
                'idutilisateur' => $idUtilisateur,
                'admin'         => $adminId
            ]);

            /**
             * 4️⃣ Notification
             */
            $stmt = $this->db->prepare("
                INSERT INTO notification (message, type, idutilisateur)
                VALUES (:message, 'CREATION_COMPTE', :admin)
            ");

            $stmt->execute([
                'message' => "Création du compte interne : {$data['email']}",
                'admin'   => $adminId
            ]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
 * ================================
 * NOTIFICATIONS ADMIN
 * ================================
 * Récupère les notifications liées à l’admin
 */
public function getNotificationsAdmin(int $adminId): array
{
    $sql = "
        SELECT 
            idnotification,
            message,
            type,
            date_notification
        FROM notification
        WHERE idutilisateur = :admin
        ORDER BY date_notification DESC
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(['admin' => $adminId]);

    return $stmt->fetchAll();
}

}
