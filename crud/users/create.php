<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/MoneyKids/config/db.php';

function createUser($pdo, $nom, $prenom, $email, $password, $role, $parent_id = null, $age = null) {
    // Check email exists
    $stmt = $pdo->prepare("SELECT id FROM utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Cet email existe déjà'];
    }

    // Hash password
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $pdo->prepare("
        INSERT INTO utilisateur (nom, prenom, email, password, role, parent_id, age)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$nom, $prenom, $email, $hashed, $role, $parent_id, $age]);
    $userId = $pdo->lastInsertId();

    // Create compte
    $stmt = $pdo->prepare("
        INSERT INTO compte (user_id, solde, montant_argent_poche, frequence)
        VALUES (?, 0.00, 0.00, 'mensuel')
    ");
    $stmt->execute([$userId]);

    return ['success' => true, 'user_id' => $userId];
}
?>
<script>
document.addEventListener('wheel', function(e) {
    if (document.activeElement.type === 'number') {
        document.activeElement.blur();
    }
});
</script>