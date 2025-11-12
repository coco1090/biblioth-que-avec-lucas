<?php

session_start();

if (!isset($_SESSION["admin_id"]))
    {
        header("Location: ../login.php");
        exit();
    }

require_once __DIR__ . "/../config/database.php";

$pdo = get_db_connection();

// Requête principale : abonnés + statistiques d’emprunts
$sql_abonne = "
    SELECT 
        a.id_abonne,
        a.civilite,
        a.nom,
        a.prenom,
        a.email,
        COUNT(e.id_emprunt) AS totals_emprunts,
        COUNT(CASE WHEN e.date_rendu IS NULL THEN 1 END) AS emprunts_en_cours
    FROM abonne a
    LEFT JOIN emprunt e ON a.id_abonne = e.id_abonne
    GROUP BY a.id_abonne
    ORDER BY emprunts_en_cours DESC, a.nom ASC
";

$reqPrepare = $pdo->prepare($sql_abonne);
$reqPrepare->execute();
$abonnes = $reqPrepare->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . "/../includes/header.php";
include __DIR__ . "/../includes/nav.php";
?>

<main class="container mx-auto px-4 py-8 flex-grow" role="main">

    <!-- Titre -->
    <header class="mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">Tableau de bord administrateur</h1>
        <p class="text-gray-600">Gestion des abonnés et suivi des emprunts</p>
    </header>

    <!-- Statistiques -->
    <section class="mb-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Total abonnés -->
            <article class="bg-blue-100 rounded-lg p-6 shadow">
                <h2 class="text-lg font-semibold text-blue-800 mb-2">Total d'abonnés</h2>
                <p class="text-4xl font-bold text-blue-600"><?= count($abonnes); ?></p>
            </article>

            <!-- Emprunts en cours -->
            <article class="bg-orange-100 rounded-lg p-6 shadow">
                <h2 class="text-lg font-semibold text-orange-800 mb-2">Emprunts en cours</h2>
                <p class="text-4xl font-bold text-orange-600">
                    <?php 
                    $total_emprunt = 0;
                    foreach ($abonnes as $abonne) {
                        $total_emprunt += (int)$abonne['emprunts_en_cours'];
                    }
                    echo $total_emprunt;
                    ?>
                </p>
            </article>

            <!-- Retards (à calculer selon ta logique ultérieure) -->
            <article class="bg-red-100 rounded-lg p-6 shadow">
                <h2 class="text-lg font-semibold text-red-800 mb-2">Abonnés avec retards</h2>
                <p class="text-4xl font-bold text-red-600">4</p>
            </article>

        </div>
    </section>

    <!-- Liste des abonnés -->
    <section>
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Liste des abonnés</h2>

        <?php if (empty($abonnes)) : ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                <p class="font-bold">Aucun abonné</p>
                <p>La bibliothèque n'a actuellement aucun abonné enregistré.</p>
            </div>
        <?php else : ?>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Civilité</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prénom</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total emprunts</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">En cours</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($abonnes as $abonne) : ?>
                            <tr class="<?= ($abonne['emprunts_en_cours'] > 0) ? 'bg-red-50 hover:bg-red-100' : 'hover:bg-gray-50'; ?>">
                                <td class="px-6 py-4"><?= $abonne['id_abonne']; ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($abonne['civilite']); ?></td>
                                <td class="px-6 py-4 font-semibold"><?= htmlspecialchars($abonne['nom']); ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($abonne['prenom']); ?></td>
                                <td class="px-6 py-4">
                                    <a href="mailto:<?= htmlspecialchars($abonne['email']); ?>" class="text-blue-600 hover:text-blue-800">
                                        <?= htmlspecialchars($abonne['email']); ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-semibold">
                                        <?= $abonne['totals_emprunts']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($abonne['emprunts_en_cours'] > 0) : ?>
                                        <span class="bg-red-500 text-white px-2 py-1 rounded-full text-xs font-semibold">
                                            ⚠ <?= $abonne['emprunts_en_cours']; ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-semibold">
                                            ✓ 0
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="?voir_historique=true&id_abonne=<?= $abonne['id_abonne'] ?>#section1" class="text-blue-600 hover:text-blue-800 font-medium">
                                        Voir historique
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>
    </section>

    <?php
    // Section historique
    if (isset($_GET['voir_historique'], $_GET['id_abonne']) && $_GET['voir_historique'] === 'true') {
        $id_abonne = intval($_GET['id_abonne']);

        $sql_historique = "
            SELECT 
                e.id_emprunt,
                e.date_sortie,
                e.date_rendu,
                l.titre,
                l.auteur,
                DATEDIFF(
                    COALESCE(e.date_rendu, CURDATE()),
                    e.date_sortie
                ) AS duree_jours
            FROM emprunt e
            JOIN livre l ON l.id_livre = e.id_livre
            WHERE e.id_abonne = :id_abonne
            ORDER BY e.date_sortie DESC
        ";
        $historique = $pdo->prepare($sql_historique);
        $historique->bindParam(':id_abonne', $id_abonne, PDO::PARAM_INT);
        $historique->execute();
        $emprunts = $historique->fetchAll(PDO::FETCH_ASSOC);

        // Récupération des infos abonné
        $abonne_info = null;
        foreach ($abonnes as $a) {
            if ($a['id_abonne'] == $id_abonne) {
                $abonne_info = $a;
                break;
            }
        }
    }
    ?>

    <?php if (isset($emprunts)) : ?>
        <section class="mt-8 bg-blue-50 rounded-lg p-6">
            <header class="mb-4 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800" id="section1">Historique des emprunts</h2>
                    <?php if ($abonne_info) : ?>
                        <p class="text-gray-600 mt-1">
                            <?= htmlspecialchars($abonne_info['civilite'] . ' ' . $abonne_info['prenom'] . ' ' . $abonne_info['nom'] . ' (' . $abonne_info['email'] . ')'); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <a href="?" class="text-blue-600 hover:text-blue-800 font-medium">✕ Fermer</a>
            </header>

            <?php if (empty($emprunts)) : ?>
                <p class="text-gray-600">Cet abonné n'a effectué aucun emprunt.</p>
            <?php else : ?>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Emprunt</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Livre</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date sortie</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date retour</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durée</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($emprunts as $e) : ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4"><?= $e['id_emprunt']; ?></td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium"><?= htmlspecialchars($e['titre']); ?></div>
                                        <div class="text-gray-500 text-xs"><?= htmlspecialchars($e['auteur']); ?></div>
                                    </td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($e['date_sortie']); ?></td>
                                    <td class="px-6 py-4"><?= $e['date_rendu'] ?? 'Non rendu'; ?></td>
                                    <td class="px-6 py-4"><?= $e['duree_jours']; ?> jour(s)</td>
                                    <td class="px-6 py-4">
                                        <?php if ($e['date_rendu']) : ?>
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-semibold">
                                                ✓ Rendu
                                            </span>
                                        <?php else : ?>
                                            <span class="bg-red-500 text-white px-2 py-1 rounded-full text-xs font-semibold">
                                                ⚠ En cours
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

</main>

<?php include __DIR__ . "/../includes/footer.php"; ?>
