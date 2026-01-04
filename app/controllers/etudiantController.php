<?php
require_once __DIR__ . '/../models/Etudiant.php';
require_once __DIR__ . '/../models/Attestation.php';
class EtudiantController
{
    private Etudiant $model;

    public function __construct()
    {
        $this->model = new Etudiant();
    }

    public function modifierProfil(): void
    {
        try {
            $this->model->modifierProfil(
                (int)$_SESSION['user']['idutilisateur'],
                $_POST['adresse'] ?? null,
                $_POST['competence'] ?? null
            );
            $_SESSION['success'] = "Profil mis à jour avec succès.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors de la mise à jour du profil.";
        }

        header('Location: /public/etudiant/?page=compte');
        exit;
    }

    public function activerProfil(): void
    {
        $this->model->activerProfil((int)$_SESSION['user']['idutilisateur']);
        $_SESSION['success'] = "Profil rendu visible.";
        header('Location: /public/etudiant/?page=compte');
        exit;
    }

    public function desactiverProfil(): void
    {
        $this->model->desactiverProfil((int)$_SESSION['user']['idutilisateur']);
        $_SESSION['success'] = "Profil rendu invisible.";
        header('Location: /public/etudiant/?page=compte');
        exit;
    }

    public function activerRecherche(): void
    {
        $this->model->activerRecherche((int)$_SESSION['user']['idutilisateur']);
        $_SESSION['success'] = "Recherche active activée.";
        header('Location: /public/etudiant/?page=compte');
        exit;
    }

    public function desactiverRecherche(): void
    {
        $this->model->desactiverRecherche((int)$_SESSION['user']['idutilisateur']);
        $_SESSION['success'] = "Recherche active désactivée.";
        header('Location: /public/etudiant/?page=compte');
        exit;
    }

    public function changerMotDePasse(): void
    {
        try {
            $this->model->changerMotDePasse(
                (int)$_SESSION['user']['idutilisateur'],
                $_POST['nouveau_mdp']
            );
            $_SESSION['success'] = "Mot de passe modifié.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur mot de passe.";
        }

        header('Location: /public/etudiant/?page=compte');
        exit;
    }

    public function deposerAttestation(): void
    {
        try {
            $att = new Attestation();

            $att->deposerAttestation(
                (int) $_SESSION['user']['idutilisateur'],
                $_POST['date_debut'],
                $_POST['date_fin']
            );

            $_SESSION['success'] =
                "Attestation déposée. Elle est en attente de validation par le secrétariat.";

        } catch (Exception $e) {
            $_SESSION['error'] =
                "Erreur lors du dépôt de l’attestation.";
        }

        header('Location: /public/etudiant/?page=attestation');
        exit;
    }

public function postuler(): void
{
    $idEtudiant = (int) $_SESSION['user']['idutilisateur'];
    $idOffre    = (int) $_POST['idoffre'];

    $check = $this->model->peutPostuler($idEtudiant, $idOffre);

    if (!$check['ok']) {
        $_SESSION['error'] = $check['msg'];
        header('Location: /public/etudiant/?page=offres');
        exit;
    }

    $this->model->deposerCandidature($idEtudiant, $idOffre);

    $_SESSION['success'] = "Candidature envoyée avec succès.";
    header('Location: /public/etudiant/?page=offres');
    exit;
}

public function renoncerCandidature(): void
{
    try {
        $this->model->renoncerCandidature(
            (int) $_POST['idcandidature'],
            (int) $_SESSION['user']['idutilisateur'],
            $_POST['justification'] ?? null
        );

        $_SESSION['success'] = "Renoncement enregistré avec succès.";

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }

    header('Location: /public/etudiant/?page=candidatures');
    exit;
}



}
