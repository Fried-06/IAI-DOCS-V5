<?php
// backend/alter_db_beta.php - Database migration and Beta codes generator
require_once __DIR__ . '/db.php';

try {
    $pdo = getDB();
    echo "Connexion à la base de données établie avec succès.\n";

    // 1. Alter users table to add is_beta_approved column
    echo "Vérification et modification de la table 'users'...\n";
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS is_beta_approved BOOLEAN DEFAULT FALSE");
    
    // Set all existing users to approved by default
    $pdo->exec("UPDATE users SET is_beta_approved = TRUE");
    echo "Tous les utilisateurs existants ont été marqués comme approuvés pour la bêta.\n";

    // 2. Create beta_codes table
    echo "Création de la table 'beta_codes'...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS beta_codes (
            code VARCHAR(50) PRIMARY KEY,
            is_used BOOLEAN DEFAULT FALSE,
            used_by_email VARCHAR(255) DEFAULT NULL,
            used_at TIMESTAMP DEFAULT NULL
        )
    ");
    echo "Table 'beta_codes' créée ou déjà existante.\n";

    // 3. Create beta_waitlist table
    echo "Création de la table 'beta_waitlist'...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS beta_waitlist (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            level VARCHAR(100) NOT NULL,
            device VARCHAR(100) NOT NULL,
            motivation TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "Table 'beta_waitlist' créée ou déjà existante.\n";

    // 4. Generate 50 unique beta codes
    // First check if we already have codes in the table
    $stmt = $pdo->query("SELECT COUNT(*) FROM beta_codes");
    $existingCodesCount = (int) $stmt->fetchColumn();

    $codes = [];
    if ($existingCodesCount === 0) {
        echo "Génération de 50 codes d'accès bêta uniques...\n";
        
        $insertStmt = $pdo->prepare("INSERT INTO beta_codes (code) VALUES (?) ON CONFLICT DO NOTHING");
        
        while (count($codes) < 50) {
            // Generate a random uppercase alphanumeric string of 6 chars
            $randomString = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
            $code = "IAI-BETA-" . $randomString;
            
            if (!in_array($code, $codes)) {
                if ($insertStmt->execute([$code])) {
                    $codes[] = $code;
                }
            }
        }
        echo "50 codes d'accès ont été insérés en base de données.\n";
    } else {
        echo "Des codes d'accès existent déjà en base de données ($existingCodesCount codes trouvés). Récupération...\n";
        $stmt = $pdo->query("SELECT code FROM beta_codes ORDER BY code ASC");
        $codes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // 5. Write the codes to a text file in the root directory for easy admin usage
    $txtFile = __DIR__ . '/../beta_activation_codes.txt';
    $fileContent = "==================================================\n";
    $fileContent .= "IAI-DOCS — LISTE DES 50 CODES D'ACCÈS BÊTA PRIVÉE\n";
    $fileContent .= "Généré le : " . date('Y-m-d H:i:s') . "\n";
    $fileContent .= "==================================================\n\n";
    
    foreach ($codes as $index => $c) {
        $fileContent .= sprintf("%02d. %s\n", $index + 1, $c);
    }
    
    file_put_contents($txtFile, $fileContent);
    echo "La liste des codes a été enregistrée à la racine dans : beta_activation_codes.txt\n";

} catch (\PDOException $e) {
    echo "Erreur lors de la migration : " . $e->getMessage() . "\n";
    exit(1);
}
?>
