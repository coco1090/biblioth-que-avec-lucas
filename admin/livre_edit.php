<?php

session_start();

if (!isset($_SESSION["admin_id"]))
    {
        header("Location: ../login.php");
        exit();
    }

require_once __DIR__ . "/../config/database.php";

$pdo = get_db_connection();

$sql = "SELECT l.id_livre, l.auteur, l.titre, l.couverture, CASE WHEN e.id_emprunt IS NOT NULL THEN 'emprunté' ELSE 'disponible' END AS statut, CONCAT(a.nom, ' ', a.prenom) AS emprunteur FROM livre l LEFT JOIN emprunt e ON l.id_livre = e.id_livre AND e.date_rendu IS NULL LEFT JOIN abonne a ON e.id_abonne = a.id_abonne;";

$reqPrepare = $pdo->prepare($sql);
$reqPrepare->execute();

$livres = $reqPrepare->fetchALL();

$ids = array_column($livres, 'id_livre'); // récupère tous les id_livre

include __DIR__ . "/../includes/header.php";
include __DIR__ . "/../includes/nav.php";
?>


    <!-- Contenu principal de la page -->
    <main class="container mx-auto px-4 py-8 flex-grow" role="main">
        <div class="max-w-2xl mx-auto">
            <!-- En-tête de la page -->
            <header class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">
                    Modifier un livre
                </h1>
                <p class="text-gray-600">
                    Livre ID : <?= $_GET['id_livre'] ?>
                </p>
            </header>

            <!-- Formulaire de modification -->
            <section class="bg-white rounded-lg shadow-md p-8">
                <?php if (!(isset($_GET["id_livre"]) && in_array($_GET["id_livre"], $ids))) :
                    header("Location: livres.php");
                endif; ?>

                <!-- Formulaire -->
                <form method="POST" action="" novalidate>
                    <!-- Champ Titre -->
                    <div class="mb-6">
                        <label for="titre" class="block text-gray-700 font-semibold mb-2">
                            Titre du livre <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="titre"
                            name="titre"
                            required
                            maxlength="30"
                            value="<?php
                            foreach ($livres as $livre) {
                                if ($livre['id_livre' ] == $_GET['id_livre']) {
                                    echo $livre['titre'];
                                }
                            }
                            ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Ex: Les Misérables">
                        <p class="text-gray-500 text-sm mt-1">Maximum 30 caractères</p>
                    </div>

                    <!-- Champ Auteur -->
                    <div class="mb-6">
                        <label for="auteur" class="block text-gray-700 font-semibold mb-2">
                            Auteur <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="auteur"
                            name="auteur"
                            required
                            maxlength="25"
                            value="<?php
                            foreach ($livres as $livre) {
                                if ($livre['id_livre' ] == $_GET['id_livre']) {
                                    echo $livre['auteur'];
                                }
                            }
                            ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Ex: VICTOR HUGO">
                        <p class="text-gray-500 text-sm mt-1">Maximum 25 caractères</p>
                    </div>

                    <!-- Champ Couverture -->
                    <div class="mb-6">
                        <label for="couverture" class="block text-gray-700 font-semibold mb-2">
                            URL de la couverture
                        </label>
                        <input
                            type="text"
                            id="couverture"
                            name="couverture"
                            maxlength="100"
                            value="<?php
                            foreach ($livres as $livre) {
                                if ($livre['id_livre' ] == $_GET['id_livre']) {
                                    echo $livre['couverture'];
                                }
                            }
                            ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Ex: images/couvertures/les_miserables.jpg">
                        <p class="text-gray-500 text-sm mt-1">Optionnel - Chemin relatif vers l'image de couverture</p>
                    </div>

                    <!-- Légende des champs obligatoires -->
                    <p class="text-gray-600 text-sm mb-6">
                        <span class="text-red-500">*</span> Champs obligatoires
                    </p>

                    <!-- Boutons d'action -->
                    <div class="flex justify-between items-center">
                        <!-- Bouton Annuler -->
                        <a href="/bibliotheque/admin/livres.php" class="text-gray-600 hover:text-gray-800 transition font-medium">
                            ← Annuler
                        </a>

                        <!-- Bouton Enregistrer -->
                        <button
                            type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition shadow">
                            ✓ Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </main>

    <?php include __DIR__ . "/../includes/footer.php"; ?>