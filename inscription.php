<?php

session_start();

if (isset($_SESSION["admin_id"]))
    {
        header('Location: admin/dashboard.php');
        exit();
    }

if (isset($_SESSION["abonne_id"]))
{
    header('Location: index.php');
    exit();
}

$error = '';
$login = '';
$password = '';

require_once __DIR__ . "/config/database.php";

function ft_dupli_verif($email) {
    $pdo = get_db_connection();

    $sql_admin = "SELECT email FROM administrateur";
    $log_adm = $pdo->query($sql_admin);
    $admins = $log_adm->fetchAll(PDO::FETCH_COLUMN);

    $sql_abo = "SELECT email FROM abonne";
    $log_abo = $pdo->query($sql_abo);
    $abonnes = $log_abo->fetchAll(PDO::FETCH_COLUMN);

    if (in_array($email, $admins) || in_array($email, $abonnes)) {
        return false;
    }
    return true;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        $login = htmlspecialchars($_POST['login'] ?? '');
        $password = htmlspecialchars($_POST['password'] ?? '');
        $conf_password = htmlspecialchars($_POST['conf_password'] ?? '');
        $prenom = htmlspecialchars($_POST['prenom'] ??'');
        $nom = htmlspecialchars($_POST['nom'] ??'');
        $civilite = htmlspecialchars($_POST['civilite'] ??'');
        
        if (!filter_var($login, FILTER_VALIDATE_EMAIL))
            {
                $error .= '<p>Format email invalide</p>';
            }
        if (iconv_strlen(trim($password)) < 5)
            {
                $error .= '<p>Mot de passe doit avoir 5 caractères minimum</p>';
            }
        if ($password !== $conf_password)
            {
                $error .= '<p>Les deux mots de passe ne sont pas identique</p>';
            }
        if (ft_dupli_verif($login) === false) {
            $error .= '<p>le compte existe déjà</p>';
        }
        if (empty($error))
        {
                $pdo = get_db_connection();

                $sql_insc = "INSERT INTO abonne (prenom, nom, civilite, email, mot_de_passe) VALUES (:prenom, :nom, :civilite, :email, :mot_de_passe);";

                $stmt = $pdo->prepare($sql_insc);
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt->execute([
                    ':prenom' => $prenom,
                    ':nom' => $nom,
                    ':civilite' => $civilite,
                    ':email' => $login,
                    ':mot_de_passe' => $hashed_password
                ]);
                header("Location: login.php?inscription=success");
                exit;
        }
    }

include __DIR__ . "/includes/header.php";
include __DIR__ . "/includes/nav.php";


?>

    <!-- Contenu principal de la page -->
    <main class="container mx-auto px-4 py-8 flex-grow" role="main">
        <div class="max-w-md mx-auto">
            <!-- Titre de la page -->
            <header class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">
                    Inscription à la bibliotheque
                </h1>
            </header>

            <!-- Formulaire de connexion -->
            <section class="bg-white rounded-lg shadow-md p-8">
                <!-- Affichage des erreurs -->
                 <?php if (!empty($error)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Erreur</p>
                    <div><?= $error ?></div>
                </div>
                <?php endif; ?>

                <!-- Formulaire -->
                <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>">

                    <!-- Champ prenom -->
                    <div class="mb-6">
                        <label for="prenom" class="block text-gray-700 font-semibold mb-2">
                            Prénom
                        </label>
                        <input
                            type="text"
                            id="prenom"
                            name="prenom"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Entrez votre prenom">
                    </div>

                    <!-- Champ nom -->
                    <div class="mb-6">
                        <label for="nom" class="block text-gray-700 font-semibold mb-2">
                            Nom
                        </label>
                        <input
                            type="text"
                            id="nom"
                            name="nom"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Entrez votre nom">
                    </div>

                    <!-- Champ civilité -->
                    <div class="mb-6">
                        <p>Choisissez votre civilité :</p>
                        <br>
                        <label class="block text-gray-700 font-semibold mb-2">
                            <input type="radio" name="civilite" value="M." required>
                            M.
                        </label>

                        <label class="block text-gray-700 font-semibold mb-2">
                            <input type="radio" name="civilite" value="Mme">
                            Mme
                        </label>
                        <label class="block text-gray-700 font-semibold mb-2">
                            <input type="radio" name="civilite" value="autre">
                            autre
                        </label>
                    </div>

                    <!-- Champ Login -->
                    <div class="mb-6">
                        <label for="login" class="block text-gray-700 font-semibold mb-2">
                            Email
                        </label>
                        <input
                            type="email"
                            id="login"
                            name="login"
                            required
                            autocomplete="email"
                            value="<?= htmlspecialchars($login); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Entrez votre email">
                    </div>

                    <!-- Champ Mot de passe -->
                    <div class="mb-6">
                        <label for="password" class="block text-gray-700 font-semibold mb-2">
                            Mot de passe
                        </label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Entrez votre mot de passe">
                    </div>

                    <!-- Champ confirmation Mot de passe -->
                    <div class="mb-6">
                        <label for="password" class="block text-gray-700 font-semibold mb-2">
                            Confirmez le mot de passe
                        </label>
                        <input
                            type="password"
                            id="conf_password"
                            name="conf_password"
                            required
                            autocomplete="current-password"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Confirmez le mot de passe">
                    </div>

                    <!-- Bouton de soumission -->
                    <button
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition">
                        S'inscrire'
                    </button>
                </form>

                <!-- Lien de retour -->
                <div class="mt-6 text-center">
                    <a href="/bibliotheque/login.php" class="text-blue-600 hover:text-blue-800 transition">
                        ← Retour à la connexion
                    </a>
                </div>
            </section>

        </div>
    </main>
    <?php include __DIR__ . "/includes/footer.php"; ?>