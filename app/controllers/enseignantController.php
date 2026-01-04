<?php

require_once __DIR__ . '/../models/Enseignant.php';
require_once __DIR__ . '/../models/Attestation.php';

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


    public function validerAffectation(): void
{
    try {
        $this->model->validerAffectation((int)$_POST['idcandidature'],
    (int)$_SESSION['user']['idutilisateur']
);
        $_SESSION['success'] = "Affectation validée avec succès.";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }

    header('Location: /public/enseignant/?page=affectations');
    exit;
}

public function rejeterAffectation(): void
{
    try {
        $this->model->rejeterAffectation((int)$_POST['idcandidature'],
    (int)$_SESSION['user']['idutilisateur']
);
        $_SESSION['success'] = "Affectation rejetée.";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }

    header('Location: /public/enseignant/?page=affectations');
    exit;
}
public function validerAffectationUrgente(): void
{
    try {
        $this->model->validerAffectationUrgente(
            (int)$_POST['idcandidature'],
            (int)$_SESSION['user']['idutilisateur']
        );

        $_SESSION['success'] = "Affectation urgente validée. Vous êtes désormais responsable.";

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }

    header('Location: /public/enseignant/?page=affectations');
    exit;
}
public function declencherRelances(): void
{
    $this->model->envoyerRelancesAffectations();
}

/* =========================
   MODIFIER RÈGLEMENTATION
   ========================= */
public function modifierReglementation(): void
{
    try {
        require_once __DIR__ . '/../models/Reglementation.php';
        $model = new Reglementation();

        $model->mettreAJourReglementation(
            (int) $_POST['idReglementation'],
            (int) $_POST['duree_min'],
            (int) $_POST['duree_max'],
            (float) $_POST['remuneration_min']
        );

        $_SESSION['success'] = "Réglementation mise à jour avec succès.";

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }

    header('Location: /public/enseignant/?page=reglementation');
    exit;
}



public function accederAttestations(): void
{
    header('Location: /public/enseignant/?page=attestations');
    exit;
}

public function validerAttestation(): void
{
    try {
        $att = new Attestation();
        $att->validerAttestation(
    (int)$_POST['idattestation'],
    (int)$_SESSION['user']['idutilisateur'],
    $_SESSION['user']['role']
);

        $_SESSION['success'] = "Attestation validée.";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }

    header('Location: /public/enseignant/?page=attestations');
    exit;
}

public function rejeterAttestation(): void
{
    try {
        $att = new Attestation();
        $att->rejeterAttestation((int)$_POST['idattestation'],
            (int)$_SESSION['user']['idutilisateur'] );

        $_SESSION['success'] = "Attestation rejetée.";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }

    header('Location: /public/enseignant/?page=attestations');
    exit;
}


}
