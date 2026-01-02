<?php

require_once __DIR__ . '/../config/database.php';

class Reglementation
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Récupérer toutes les règles
     */
    public function getToutesReglementations(): array
    {
        $sql = "
            SELECT
                idreglementation,
                type_contrat,
                pays,
                duree_min,
                duree_max,
                remuneration_min
            FROM reglementation
            ORDER BY type_contrat, pays
        ";

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Mise à jour d’une règle
     */
    public function mettreAJourReglementation(
        int $id,
        int $dureeMin,
        int $dureeMax,
        float $remunerationMin
    ): void {

        if ($dureeMax < $dureeMin) {
            throw new Exception("Durée maximale inférieure à la durée minimale");
        }

        $sql = "
            UPDATE reglementation
            SET
                duree_min = :duree_min,
                duree_max = :duree_max,
                remuneration_min = :remuneration_min
            WHERE idreglementation = :id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'duree_min'        => $dureeMin,
            'duree_max'        => $dureeMax,
            'remuneration_min' => $remunerationMin,
            'id'               => $id
        ]);
    }
}
