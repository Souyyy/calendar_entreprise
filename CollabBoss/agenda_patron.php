<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root"; // Remplacez par votre nom d'utilisateur
$password = "root"; // Remplacez par votre mot de passe
$dbname = "collaboss"; // Remplacez par le nom de votre base de données
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtenir la date sélectionnée ou utiliser la date actuelle
    $dateSelectionnee = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

    // Récupération des utilisateurs avec leurs tâches pour la date sélectionnée
    $query = "
        SELECT u.nom AS collaborateur_nom, t.titre, t.date_debut, t.date_fin
        FROM utilisateur u
        LEFT JOIN tache t ON u.id = t.utilisateur_id
        WHERE DATE(t.date_debut) = :date
        ORDER BY u.nom, t.date_debut
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':date', $dateSelectionnee);
    $stmt->execute();

    // Stocker les résultats par collaborateur
    $collaborateurs = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $collaborateurs[$row['collaborateur_nom']][] = $row;
    }

    // Heure actuelle
    $heureActuelle = new DateTime();
    $heureActuelle->setTimezone(new DateTimeZone('Europe/Paris')); // Ajustez selon votre fuseau horaire
    $positionHeureActuelle = (($heureActuelle->format('H') - 7) * (100 / 13)) + (($heureActuelle->format('i') / 60) * (100 / 13)); // Ajustement basé sur la plage 7h-20h

} catch (PDOException $e) {
    echo "Erreur de connexion: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline des Collaborateurs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .collaborateur {
            margin-bottom: 40px;
        }

        .timeline-container {
            position: relative;
            margin-top: 20px;
            border: 1px solid #3498db;
            border-radius: 8px;
            padding: 10px;
            background-color: #f9f9f9;
            /* Couleur de fond de la boîte */
        }

        .timeline {
            position: relative;
            height: 200px;
            /* Hauteur de la boîte contenant les tâches */
            overflow: hidden;
            /* Évite que les tâches débordent */
        }

        .tache {
            background: #ecf0f1;
            border-radius: 8px;
            padding: 5px;
            position: absolute;
            white-space: nowrap;
            /* Empêche le passage à la ligne */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            /* Ombre pour les tâches */
            z-index: 5;
            /* S'assurer que les tâches sont au-dessus des traits d'heure */
        }

        .trait-heure {
            position: absolute;
            height: 200px;
            /* Hauteur de la boîte contenant les tâches */
            border-left: 1px dashed gray;
            /* Style du trait en pointillé */
            left: 0;
            /* Alignement à gauche */
            z-index: 1;
            /* Assurez-vous que le trait est en dessous des tâches */
        }

        .heure {
            position: absolute;
            width: 100%;
            /* Chaque heure occupe 100% de la largeur */
            text-align: center;
            /* Centrer le texte */
            bottom: 5px;
            /* Positionnement des heures juste au-dessus du bas */
            font-size: 12px;
            /* Taille de police pour les heures */
            color: #34495e;
            /* Couleur du texte des heures */
        }
    </style>
</head>

<body>
    <?php echo '<style>.ligne-actuelle {
            position: absolute;
            left:  ' . $positionHeureActuelle . '%;
            height: 200px;
            border-left: 2px dashed red;
            top: 0;
            z-index: 10;
        } </style>'; ?>
    <h1>Timeline des Collaborateurs pour le <?php echo htmlspecialchars($dateSelectionnee); ?></h1>

    <!-- Formulaire de sélection de date -->
    <form method="GET" action="">
        <label for="date">Sélectionnez une date :</label>
        <input type="date" id="date" name="date" value="<?php echo $dateSelectionnee; ?>">
        <input type="submit" value="Afficher">
    </form>

    <?php foreach ($collaborateurs as $nom => $taches): ?>
        <div class="collaborateur">
            <h2><?php echo htmlspecialchars($nom); ?></h2>
            <div class="timeline-container">
                <div class="timeline">
                    <!-- Ligne verticale pour l'heure actuelle -->
                    <div class="ligne-actuelle"></div>

                    <!-- Trait en pointillé pour chaque heure -->
                    <?php for ($h = 7; $h <= 20; $h++): ?>
                        <div class="trait-heure" style="left: <?php echo ($h - 7) * (100 / 13); ?>%;"></div>
                        <div class="heure" style="left: <?php echo ($h - 7) * (100 / 13); ?>%;">
                            <?php echo sprintf('%02d:00', $h); ?>
                        </div>
                    <?php endfor; ?>

                    <?php foreach ($taches as $tache): ?>
                        <?php
                        // Calculer la position de la tâche dans la timeline
                        $debut = new DateTime($tache['date_debut']);
                        $fin = new DateTime($tache['date_fin']);
                        $debutHeure = (int)$debut->format('H'); // Heure de début
                        $finHeure = (int)$fin->format('H'); // Heure de fin
                        $debutMinute = (int)$debut->format('i'); // Minute de début
                        $finMinute = (int)$fin->format('i'); // Minute de fin

                        // Calculer la largeur en pourcentage pour la tâche
                        $largeur = (($finHeure + $finMinute / 60) - ($debutHeure + $debutMinute / 60)) * (100 / 13); // 100% / 13h
                        $position = ($debutHeure - 7) * (100 / 13) + ($debutMinute / 60) * (100 / 13); // Position en pourcentage
                        ?>
                        <div class="tache" style="left: <?php echo $position; ?>%; width: <?php echo $largeur; ?>%;">
                            <strong><?php echo htmlspecialchars($tache['titre']); ?></strong><br>
                            Début: <?php echo htmlspecialchars(date('d-m-Y H:i', strtotime($tache['date_debut']))); ?><br>
                            Fin: <?php echo htmlspecialchars(date('d-m-Y H:i', strtotime($tache['date_fin']))); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</body>

</html>