<?php

require_once __DIR__ . '/../models/Secretaire.php';

class secretaireController
{
    private Secretaire $secretaireModel;

    public function __construct()
    {
        $this->secretaireModel = new Secretaire();
    }

    /**
     * ================================
     * CRÉATION DES ÉTUDIANTS
     * ================================
     * Input : textarea
     * Format : nom;prenom;email;date_naissance;formation
     */
    public function creerEtudiants(): void
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'SECRETAIRE') {
            header('Location: /public/index.php');
            exit;
        }

        $raw = trim($_POST['liste_etudiants'] ?? '');

        if ($raw === '') {
            $_SESSION['error'] = "Aucune donnée fournie.";
            header('Location: /public/secretaire/?page=creer_etudiants');
            exit;
        }

        $etudiants = [];

        foreach (explode("\n", $raw) as $line) {

            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = array_map('trim', explode(';', $line));

            if (count($parts) !== 5) {
                $_SESSION['error'] = "Format invalide détecté.";
                header('Location: /public/secretaire/?page=creer_etudiants');
                exit;
            }

            [$nom, $prenom, $email, $dateNaissance, $formation] = $parts;

            $etudiants[] = [
                'nom'             => $nom,
                'prenom'          => $prenom,
                'email'           => $email,
                'date_naissance'  => $dateNaissance,
                'formation'       => $formation
            ];
        }

        try {
            $this->secretaireModel->creerEtudiants($etudiants);
            $_SESSION['success'] = "Étudiants créés avec succès.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors de la création des étudiants.";
        }

        header('Location: /public/secretaire/?page=creer_etudiants');
        exit;
    }
}
