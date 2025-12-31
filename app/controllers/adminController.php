<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Admin.php';

class adminController
{
    private Admin $adminModel;

    public function __construct()
    {
        $this->adminModel = new Admin();
    }

    public function creerCompteInterne()
    {
        // La session est DÉJÀ active (public/admin/index.php)
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ADMINISTRATEUR') {
            header('Location: /public/index.php');
            exit;
        }

       $ok = $this->adminModel->creerCompteInterne(
    $_POST,
    $_SESSION['user']['id']
);

if ($ok) {
    $_SESSION['message'] = "Compte interne créé avec succès";
} else {
    $_SESSION['message'] = "Erreur lors de la création du compte interne";
}


        header('Location: /public/admin/?page=creer_compte');
        exit;
    }
}
