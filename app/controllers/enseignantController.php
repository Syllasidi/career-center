<?php

require_once __DIR__ . '/../models/Enseignant.php';

class EnseignantController
{
    private Enseignant $model;

    public function __construct()
    {
        $this->model = new Enseignant();
    }

    /* =========================
       VALIDATION OFFRE
       ========================= */
    public function validerOffre(): void
    {
        try {
            $this->model->validerOffre(
                (int)$_POST['idOffre'],
                (int)$_SESSION['user']['idutilisateur']
            );

            $_SESSION['success'] =
                "Offre validée. Vous êtes désormais enseignant responsable.";

        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: /public/enseignant/?page=offres');
        exit;
    }

    /* =========================
       REFUS OFFRE
       ========================= */
    public function rejeterOffre(): void
    {
        try {
            $this->model->rejeterOffre(
                (int)$_POST['idOffre'],
                (int)$_SESSION['user']['idutilisateur']
            );

            $_SESSION['success'] = "Offre rejetée.";

        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: /public/enseignant/?page=offres');
        exit;
    }

    /* =========================
       NOTIFICATIONS
       ========================= */
    public function getNotifications(): array
    {
        return $this->model->getNotifications(
            (int)$_SESSION['user']['idutilisateur']
        );
    }

    /* =========================
       CHANGER MOT DE PASSE
       (réutilisation logique secrétaire)
       ========================= */
    public function changerMdp(): void
    {
        try {
            $this->model->changerMotDePasse(
                (int)$_SESSION['user']['idutilisateur'],
                $_POST['ancien_mdp'],
                $_POST['nouveau_mdp']
            );

            $_SESSION['success'] = "Mot de passe modifié avec succès.";

        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: /public/enseignant/?page=compte');
        exit;
    }
}
