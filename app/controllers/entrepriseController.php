<?php

require_once __DIR__ . '/../models/Offre.php';
require_once __DIR__ . '/../models/Candidature.php';

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
    public function enregistrerBrouillon(): void
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
        'date_debut'   => $_POST['date_debut'] ?? null,
        'date_fin'     => $_POST['date_fin'] ?? null,
        'remuneration' => $_POST['remuneration'] ?? null,
        'idEntreprise' => $_SESSION['user']['idutilisateur']
    ];

    try {
        $offre = new Offre();

        // ❌ AUCUNE vérification réglementaire
        // ✅ Statut BROUILLON
        $offre->enregistrerBrouillon($data);

        $_SESSION['success'] = "Brouillon enregistré avec succès.";

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }

    header('Location: /public/entreprise/?page=creer_offre');
    exit;
}

public function modifierOffrePost(): void
{
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ENTREPRISE') {
        header('Location: /public/');
        exit;
    }

    $idOffre = (int)($_POST['idoffre'] ?? 0);
    if ($idOffre <= 0) {
        $_SESSION['error'] = "Offre invalide.";
        return;
    }

    $data = [
        'type_contrat' => $_POST['type_contrat'] ?? '',
        'titre'        => trim($_POST['titre'] ?? ''),
        'description'  => trim($_POST['description'] ?? ''),
        'pays'         => $_POST['pays'] ?? '',
        'ville'        => trim($_POST['ville'] ?? ''),
        'date_debut'   => $_POST['date_debut'] ?? null,
        'date_fin'     => $_POST['date_fin'] ?? null,
        'remuneration' => $_POST['remuneration'] ?? null
    ];

    $soumettre = ($_POST['action'] === 'soumettre_modification');

    try {
        $offre = new Offre();
        $offre->modifierOffre(
            $idOffre,
            (int)$_SESSION['user']['idutilisateur'],
            $data,
            $soumettre
        );

        $_SESSION['success'] = $soumettre
            ? "Modifications enregistrées et soumises pour validation."
            : "Modifications enregistrées avec succès.";

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }

    // e
}

public function gererOffreAction(): void
{
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ENTREPRISE') {
        header('Location: /public/');
        exit;
    }

    $idOffre = (int)($_POST['idoffre'] ?? 0);
    if ($idOffre <= 0) {
        $_SESSION['error'] = "Offre invalide.";
        return;
    }

    try {
        $offre = new Offre();
        $idEntreprise = (int)$_SESSION['user']['idutilisateur'];

        switch ($_POST['action']) {
            case 'publier_offre':
                $offre->publierOffre($idOffre, $idEntreprise);
                $_SESSION['success'] = "Offre publiée avec succès.";
                break;

            case 'desactiver_offre':
                $offre->desactiverOffre($idOffre, $idEntreprise);
                $_SESSION['success'] = "Offre désactivée.";
                break;

            case 'reactiver_offre':
                $offre->reactiverOffre($idOffre, $idEntreprise);
                $_SESSION['success'] = "Offre réactivée.";
                break;

            default:
                throw new Exception("Action non autorisée.");
        }

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}





public function gererCandidatureAction(): void
{
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ENTREPRISE') {
        header('Location: /public/');
        exit;
    }

    $idCandidature = (int)($_POST['idcandidature'] ?? 0);
    if ($idCandidature <= 0) {
        $_SESSION['error'] = "Candidature invalide.";
        header('Location: /public/entreprise/?page=candidatures');
        exit;
    }

    try {
        require_once __DIR__ . '/../models/Candidature.php';

        $model = new Candidature();
        $idEntreprise = (int)$_SESSION['user']['idutilisateur'];

        if ($_POST['action'] === 'accepter_candidature') {
            $model->accepterCandidature($idCandidature, $idEntreprise);
            $_SESSION['success'] = "Candidature acceptée et transmise à l’enseignant.";
        }

        if ($_POST['action'] === 'refuser_candidature') {
            $model->refuserCandidature($idCandidature, $idEntreprise);
            $_SESSION['success'] = "Candidature refusée.";
        }

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }

    // ✅ REDIRECTION PROPRE (POST → GET)
    header('Location: /public/entreprise/?page=candidatures');
    exit;
}

public function proposerOffreAction(): void
{
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ENTREPRISE') {
        return;
    }

    $idOffre = (int)($_POST['idoffre'] ?? 0);
    $idEtudiant = (int)($_POST['id_etudiant'] ?? 0);
    $idEntreprise = (int)$_SESSION['user']['idutilisateur'];

    if ($idOffre <= 0 || $idEtudiant <= 0) {
        $_SESSION['error'] = "Données invalides.";
        return;
    }

    try {
        $model = new Candidature();
        $model->proposerOffre($idOffre, $idEtudiant, $idEntreprise);
        $_SESSION['success'] = "Offre proposée avec succès.";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }

    // ⚠️ PAS DE HEADER / PAS DE REDIRECTION
    // on reste sur la même page
}
}
