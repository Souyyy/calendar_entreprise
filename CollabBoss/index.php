<?php

require 'src/config/db.php'; // Inclure la connexion à la base de données

// Vérifier si l'utilisateur veut créer une tâche
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_task'])) {
    // Traitez la création de la tâche ici
    $titre = $_POST['titre'];
    $description = $_POST['description'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $utilisateur_id = $_POST['utilisateur_id']; // ID de l'utilisateur
    $calendrier_id = $_POST['calendrier_id']; // ID du calendrier

    // Insérer la tâche dans la base de données
    $stmt = $pdo->prepare("INSERT INTO tache (titre, description, date_debut, date_fin, utilisateur_id, calendrier_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$titre, $description, $date_debut, $date_fin, $utilisateur_id, $calendrier_id]);
}

// Récupération des tâches
$taches = $pdo->query("SELECT t.*, u.nom AS utilisateur_nom FROM tache t JOIN utilisateur u ON t.utilisateur_id = u.id")->fetchAll();

// Récupérer la liste des collaborateurs
$collaborateurs = $pdo->query("SELECT * FROM utilisateur")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application de Gestion de Planning</title>
</head>

<body>
    <h1>Gestion de Planning</h1>

    <h2>Créer une Tâche</h2>
    <form method="POST" action="">
        <label for="titre">Titre:</label>
        <input type="text" name="titre" required>

        <label for="description">Description:</label>
        <textarea name="description"></textarea>

        <label for="date_debut">Date de Début:</label>
        <input type="datetime-local" name="date_debut" required>

        <label for="date_fin">Date de Fin:</label>
        <input type="datetime-local" name="date_fin">

        <label for="utilisateur_id">Collaborateur:</label>
        <select name="utilisateur_id" required>
            <?php foreach ($collaborateurs as $collaborateur): ?>
                <option value="<?php echo $collaborateur['id']; ?>"><?php echo htmlspecialchars($collaborateur['nom']); ?></option>
            <?php endforeach; ?>
        </select>

        <input type="hidden" name="calendrier_id" value="1"> <!-- Remplacez par un ID calendrier valide -->

        <button type="submit" name="create_task">Créer Tâche</button>
    </form>

    <h2>Liste des Tâches</h2>
    <ul>
        <?php foreach ($taches as $tache): ?>
            <li>
                <strong><?php echo htmlspecialchars($tache['titre']); ?></strong> (<?php echo htmlspecialchars($tache['utilisateur_nom']); ?>)<br>
                <?php echo htmlspecialchars($tache['description']); ?><br>
                <em>Du <?php echo htmlspecialchars($tache['date_debut']); ?> au <?php echo htmlspecialchars($tache['date_fin']); ?></em>
            </li>
        <?php endforeach; ?>
    </ul>
</body>

</html>