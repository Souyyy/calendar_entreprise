<?php
require 'src/config/db.php'; // Inclure la connexion à la base de données

// Récupérer l'ID de l'utilisateur connecté (vous devriez le définir par session ou autre méthode)
$utilisateur_id = 1; // Remplacer par l'ID de l'utilisateur connecté

// Récupérer les tâches pour l'utilisateur
$date = date('Y-m-d');
$taches = $pdo->prepare("
    SELECT * 
    FROM tache 
    WHERE utilisateur_id = ? AND DATE(date_debut) = ?
    ORDER BY date_debut
");
$taches->execute([$utilisateur_id, $date]);
$taches = $taches->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Agenda</title>
    <style>
        .timeline {
            display: flex;
            flex-direction: column;
            border-left: 2px solid #333;
        }

        .tache {
            margin: 10px 0;
            padding-left: 10px;
            border-left: 2px solid #28A745;
        }
    </style>
</head>

<body>
    <h1>Mon Agenda</h1>

    <h2>Tâches pour aujourd'hui (<?php echo date('Y-m-d'); ?>)</h2>
    <div class="timeline">
        <?php foreach ($taches as $tache): ?>
            <div class="tache">
                <strong><?php echo htmlspecialchars($tache['titre']); ?></strong><br>
                <em>De <?php echo htmlspecialchars($tache['date_debut']); ?> à <?php echo htmlspecialchars($tache['date_fin']); ?></em><br>
                <?php echo htmlspecialchars($tache['description']); ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Formulaire pour ajouter une nouvelle tâche -->
    <h2>Ajouter une Tâche</h2>
    <form method="POST" action="">
        <label for="titre">Titre:</label>
        <input type="text" name="titre" required>

        <label for="description">Description:</label>
        <textarea name="description"></textarea>

        <label for="date_debut">Date de Début:</label>
        <input type="datetime-local" name="date_debut" required>

        <label for="date_fin">Date de Fin:</label>
        <input type="datetime-local" name="date_fin">

        <input type="hidden" name="utilisateur_id" value="<?php echo $utilisateur_id; ?>">
        <input type="hidden" name="calendrier_id" value="1"> <!-- Remplacez par un ID calendrier valide -->

        <button type="submit" name="create_task">Créer Tâche</button>
    </form>
</body>

</html>