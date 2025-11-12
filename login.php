<?php

session_start();

if (isset($_SESSION["admin_id"]))
    {
        header('Location: admin/dashboard.php');
        exit();
    }

$error = '';
$login = '';
$password = '';

require_once __DIR__ . "/config/database.php";

if (isset($_SESSION["abonne_id"]))
{
    header('Location: index.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        $login = htmlspecialchars($_POST['login'] ?? '');
        $password = htmlspecialchars($_POST['password'] ?? '');
        
        if (!filter_var($login, FILTER_VALIDATE_EMAIL))
            {
                $error .= '<p>Format email invalide</p>';
            }
        if (iconv_strlen(trim($password)) < 5)
            {
                $error .= '<p>Mot de passe doit avoir 5 caractères minimum</p>';
            }
        
        if (empty($error))
        {
                $pdo = get_db_connection();

                $sql_admin = "SELECT * FROM administrateur WHERE email = :login LIMIT 1;";

                $log_adm = $pdo->prepare($sql_admin);
                $log_adm->bindParam(':login', $login, PDO::PARAM_STR);
                $log_adm->execute();

                $sql_abo = "SELECT * FROM abonne WHERE email = :login LIMIT 1;";

                $log_abo = $pdo->prepare($sql_abo);
                $log_abo->bindParam(':login', $login, PDO::PARAM_STR);
                $log_abo->execute();
                
                if ($log_adm->rowCount() === 1)
                {
                    $admin = $log_adm->fetch(PDO::FETCH_ASSOC);
                    if (password_verify($password, $admin['mot_de_passe']))
                        {
                            $_SESSION['admin_id'] = $admin['id_admin'];
                            $_SESSION['admin_login'] = $admin['login'];
                            $_SESSION['admin_nom'] = $admin['nom'];
                            $_SESSION['admin_prenom'] = $admin['prenom'];
                            $_SESSION['admin_email'] = $admin['email'];

                            $upd_sql = 'UPDATE administrateur SET dernier_acces = NOW() WHERE id_admin = :id_admin;';
                            $upd_val = $pdo->prepare($upd_sql);
                            $upd_val->bindParam(':id_admin', $admin['id_admin'], PDO::PARAM_INT);
                            $upd_val->execute();

                            $_SESSION['message'] = "Bienvenue " . htmlspecialchars($admin['prenom']);
                            
                            header('Location: admin/dashboard.php');
                            exit();
                        }
                    else
                        {
                            $error .= '<p>Mot de passe ou identifiant incorrect</p>';
                        }
                }
                else if ($log_abo->rowCount() === 1)
                    {
                        $abo = $log_abo->fetch(PDO::FETCH_ASSOC);
                        if (password_verify($password, $abo['mot_de_passe']))
                        {
                            $_SESSION['abonne_id'] = $abo['id_abonne'];
                            $_SESSION['abonne_nom'] = $abo['nom'];
                            $_SESSION['abonne_prenom'] = $abo['prenom'];
                            $_SESSION['abonne_email'] = $abo['email'];

                            $_SESSION['message'] = "Bienvenue " . htmlspecialchars($abo['prenom']);

                             header('Location: index.php');
                            exit();
                        }
                        else
                        {
                            $error .= '<p>Mot de passe ou identifiant incorrect</p>';
                        }
                    }
                else
                    {
                        $error .= '<p>Mot de passe ou identifiant incorrect</p>';
                    }
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
                    Connexion à la bibliotheque
                </h1>
            </header>
            <?php if (isset($_GET['inscription']) && $_GET['inscription'] === "success") : ?>
                <div class="mb-6 bg-green-100 border-l-4 border-green-600 text-green-700 p-4 rounded">
                <p>inscription réussie ! vous pouvez maintenant vous connecter.</p>
            </div>
            <?php endif; ?>

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

                    <!-- Bouton de soumission -->
                    <button
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition">
                        Se connecter
                    </button>
                </form>

                <!-- Lien inscription -->
                <div class="mt-6 text-center">pas encore de compte ? alors
                    <a href="/bibliotheque/inscription.php" class="text-blue-600 hover:text-blue-800 transition">
                        inscrivez-vous !
                    </a>
                </div>

                <!-- Lien de retour -->
                <div class="mt-6 text-center">
                    <a href="/bibliotheque/index.php" class="text-blue-600 hover:text-blue-800 transition">
                        ← Retour à l'accueil
                    </a>
                </div>
            </section>

        </div>
    </main>
    <?php include __DIR__ . "/includes/footer.php"; ?>