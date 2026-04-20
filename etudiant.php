<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Database Configuration
$host = 'localhost';
$db   = 'bit'; // Replace with your actual DB name
$user = 'cs27'; // Use the 'admin' user we created
$pass = 'cs27'; // Use your actual password
$charset = 'utf8';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// 2. Fetch Data
$stmt = $pdo->query('SELECT MATRICULE, NOM, PRENOM FROM ETUDIANT');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Étudiants</title>
    <style>
        table { border-collapse: collapse; width: 50%; margin: 20px auto; font-family: Arial, sans-serif; }
        th, td { border: 1px solid #100b0b; padding: 12px; text-align: left; }
        th { background-color: #999ff493; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        h2 { text-align: center; color: #333; }
    </style>
</head>
<body>

    <h2>Répertoire des Étudiants</h2>

    <table>
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Nom</th>
                <th>Prénom</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $stmt->fetch()): ?>
            <tr>
                <td><?= htmlspecialchars($row['MATRICULE']) ?></td>
                <td><?= htmlspecialchars($row['NOM']) ?></td>
                <td><?= htmlspecialchars($row['PRENOM']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>