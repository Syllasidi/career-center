<?php

require_once __DIR__ . '/../models/Offre.php';

class EntrepriseController
{
    /**
     * Création d’une offre
     * Diagramme de séquence respecté
     */
    public function creerOffre(): void
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ENTREPRISE') {
            header('Location: /public/');
            exit;
        }

        $data = [
            'type_contrat' => $_POST['type_contrat'] ?? '',
            'titre'        => trim($_POST['titre'] ?? ''),
            'description'  => trim($_POST['description'] ?? ''),
            'pays'         => $_POST['pays'] ?? '',
            'ville'        => trim($_POST['ville'] ?? ''),
            'date_debut'   => $_POST['date_debut'] ?? '',
            'date_fin'     => $_POST['date_fin'] ?? '',
            'remuneration' => $_POST['remuneration'] ?? 0,
            'idEntreprise' => $_SESSION['user']['idutilisateur']
        ];

        try {
            $offre = new Offre();

            // 1️⃣ Vérification réglementaire automatique
            $offre->verifierConformiteOffre($data);

            // 2️⃣ Enregistrement de l’offre
            $offre->enregistrerOffre($data);

            $_SESSION['success'] =
                "Offre créée avec succès. Elle est en attente de validation.";

        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: /public/entreprise/?page=creer_offre');
        exit;
    }
}
