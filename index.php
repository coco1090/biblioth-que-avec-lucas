<?php
session_start();

require_once __DIR__ . "/config/database.php";

$page_title = "accueil - Bibliothèque";

$pdo = get_db_connection();

$sql = "SELECT l.id_livre, l.auteur, l.titre, l.couverture, CASE WHEN e.id_emprunt IS NOT NULL THEN 'emprunte' ELSE 'disponible' END AS statut FROM livre l LEFT JOIN (SELECT id_livre, id_emprunt FROM emprunt WHERE date_rendu IS NULL) e ON l.id_livre = e.id_livre ORDER BY l.titre ASC;";

//prepar + exec requete
$reqPrepare = $pdo->prepare($sql);
$reqPrepare->execute();

$livres = $reqPrepare->fetchALL();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['abonne_id'])) {
    $id_livre = $_POST['id_livre'] ?? null;
    if ($id_livre) {
        foreach ($livres as $livre) {
            if ($livre['id_livre'] == $id_livre)
            {
                $titre = $livre['titre'];
                $auteur = $livre['auteur'];
            }
        }
        $sql_emprunt = "INSERT INTO emprunt (id_abonne, id_livre, date_sortie) VALUES (:id_abonne, :id_livre, NOW());";
        $req_emprunt = $pdo->prepare($sql_emprunt);
        $req_emprunt->execute([
            ':id_abonne' => $_SESSION['abonne_id'],
            ':id_livre' => $id_livre
        ]);
        $_SESSION['emprunt'] = "le livre $titre de $auteur a été emprunté avec succès.";
        header("Location: index.php");
        exit();
    }
}

include __DIR__ . "/includes/header.php";
include __DIR__ . "/includes/nav.php";
?>


    <!-- Contenu principal de la page -->
    <main class="container mx-auto px-4 py-8 flex-grow" role="main">
        <!-- Titre de la page -->
        <header class="mb-8">
            <?php if (isset($_GET['logout']) && $_GET['logout'] === "success") : ?>
            <div class="mb-6 bg-red-100 border-l-4 border-red-600 text-red-700 p-4 rounded">
                <p>déconnexion réussite</p>
            </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['emprunt'])) : ?>
            <div class="mb-6 bg-green-100 border-l-4 border-green-600 text-green-700 p-4 rounded">
                <p><?= $_SESSION['emprunt'] ?></p>
            </div>
            <?php endif;?>
            <?php unset($_SESSION['emprunt']); ?>
            <h1 class="text-4xl font-bold text-gray-800 mb-2">
                Catalogue de la Bibliothèque
            </h1>
            <p class="text-gray-600">
                Découvrez notre collection de <?= count($livres) ?> livres
            </p>
        </header>

        <!-- Section des livres -->
        <section aria-label="Liste des livres">
            <?php if(empty($livres)) : ?>
            <!-- Message si aucun livre n'est disponible -->
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                <p class="font-bold">Aucun livre disponible</p>
                <p>La bibliothèque ne contient actuellement aucun livre.</p>
            </div>
            <?php else : ?>
            <!-- Grille de livres -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach($livres as $livre) : ?>
                <!-- Carte de livre -->
                <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                    <!-- Image de couverture du livre -->
                    <div class="h-64 bg-gray-200 flex items-center justify-center relative">
                        <!-- Affichage de la couverture si disponible -->
                         <?php if (!empty($livre['couverture'])) : ?>
                        <img
                            src="<?= $livre['couverture']; ?>"
                            alt="Couverture de Père Goriot"
                            class="h-full w-full object-cover">
                        <?php else : ?>
                        <!-- Placeholder si aucune couverture n'est disponible -->
                        <span class="text-6xl" aria-hidden="true">📖</span>
                        <?php endif; ?>

                        <!-- Étiquette de statut -->
                        <div class="absolute top-2 right-2">
                            <?php if($livre['statut'] == 'disponible') : ?>
                            <!-- Badge vert pour les livres disponibles -->
                            <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold shadow">
                                ✓ Disponible
                            </span>
                            <?php else : ?>
                            <!-- Badge rouge pour les livres empruntés -->
                            <span class="bg-red-500 text-white px-3 py-1 rounded-full text-sm font-semibold shadow">
                                ✗ En prêt
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Informations du livre -->
                    <div class="p-4">
                        <!-- Titre du livre -->
                        <h2 class="text-xl font-bold text-gray-800 mb-2 line-clamp-2">
                            <?= $livre['titre']; ?>
                        </h2>

                        <!-- Auteur du livre -->
                        <p class="text-gray-600 mb-2">
                            <span class="font-semibold">Auteur :</span>
                            <?= htmlspecialchars($livre['auteur']); ?>
                        </p>

                        <!-- ID du livre -->
                        <p class="text-gray-500 text-sm">
                            Référence : <?= $livre['id_livre']; ?>
                        </p>

                        <!-- Bouton d'emprunt -->
                        <?php if($livre['statut'] === 'disponible' && isset($_SESSION['abonne_id'])): ?>
                        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                            <button type="submit" name="id_livre" value="<?= $livre['id_livre']; ?>" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">
                                Emprunter
                            </button>
                        </form>
                        <?php elseif($livre['statut'] === 'disponible' && isset($_SESSION['admin_id'])): ?>
                        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                            <button type="submit" name="id_livre" value="<?= $livre['id_livre']; ?>" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition" disabled>
                                Emprunter
                            </button>
                        </form>
                        <?php else: ?>
                        <button class="w-full bg-gray-400 text-white font-bold py-2 px-4 rounded cursor-not-allowed" disabled>
                            Indisponible
                        </button>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>
    </main>
    <?php include __DIR__ . "/includes/footer.php"; ?>