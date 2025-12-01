# 🚨 **SOLUTION ERREURS 500 - Guide de dépannage**

## 🎯 **Les erreurs 500 persistent - Voici la solution**

### **CAUSE PRINCIPALE DES ERREURS 500 :**
Les erreurs 500 sont causées par des problèmes dans le code PHP ou la configuration serveur.

## 🔧 **SOLUTION IMMÉDIATE :**

### **ÉTAPE 1 : Créer un fichier test simple**
Créez un fichier `test.php` avec ce contenu :
```php
<?php
echo "✅ PHP fonctionne !";
echo "<br>Version PHP: " . PHP_VERSION;
echo "<br>Date: " . date('Y-m-d H:i:s');
?>
```

**Ce fichier va nous dire si PHP fonctionne sur votre serveur.**

### **ÉTAPE 2 : Test de la configuration**
Créez un fichier `test-config.php` avec ce contenu :
```php
<?php
try {
    $config = require 'config.php';
    echo "✅ Configuration chargée avec succès !<br>";
    echo "Host SMTP: " . $config['smtp']['host'] . "<br>";
    echo "Port: " . $config['smtp']['port'] . "<br>";
} catch (Exception $e) {
    echo "❌ Erreur config: " . $e->getMessage();
}
?>
```

## 🚨 **DIAGNOSTIC DES ERREURS 500 :**

### **Problème le plus probable : SYNTAXE PHP**

**Vérifiez ces fichiers dans votre éditeur :**
1. `config.php` - Ligne avec erreur de syntaxe
2. `security.php` - Accolade manquante, point-virgule oublié
3. `send_mail.php` - Problème de code

### **Comment détecter l'erreur :**
1. **Ouvrez chaque fichier PHP dans un éditeur** (Notepad++, VSCode, etc.)
2. **Cherchez les couleurs anormales** - Si du code est tout en rouge/noir, il y a une erreur
3. **Vérifiez les accolades** - Chaque `{` doit avoir une `}`
4. **Vérifiez les guillemets** - Chaque `"` doit être fermé

## 🔧 **SOLUTION IMMÉDIATE :**

### **Option A : Restaurer une version qui marchait**
Si vous avez une sauvegarde de votre site avant que ça casse, restaurez-la.

### **Option B : Réécrire le fichier problemática**
Le plus souvent, c'est `config.php` qui pose problème.

**Créez un nouveau `config.php` propre :**
```php
<?php
return [
    'domain' => [
        'name' => 'beverlylimousine.fr',
    ],
    'upload' => [
        'directory' => __DIR__ . '/pdfs/',
        'max_size' => 500000,
    ],
    'email' => [
        'notification' => 'contact@transvoyage.fr',
        'from' => 'contact@transvoyage.fr',
    ],
    'smtp' => [
        'host' => 'dolphin.o2switch.net',
        'port' => 465,
        'encryption' => 'ssl',
        'auth' => true,
        'username' => 'contact@transvoyage.fr',
        'password' => '19Massenet!!',
        'from_email' => 'contact@transvoyage.fr',
        'from_name' => 'Beverly Limousine',
        'timeout' => 20,
    ],
    'debug' => true,
    'logging' => [
        'path' => __DIR__ . '/logs/reservation-handler.log',
    ],
    'security' => [
        'rate_limit' => 20,
        'rate_limit_minute' => 20,
        'rate_limit_hour' => 100,
    ],
];
?>
```

## 📞 **SUPPORT TECHNIQUE :**

### **Si vous ne trouvez pas l'erreur :**
1. **Contactez o2switch** - Ils peuvent voir les logs d'erreur détaillés
2. **Demandez-leur** de vérifier la configuration PHP
3. **Rétablissez** une version qui marchait

### **Logs à consulter :**
- **Panneau o2switch** → Logs d'erreur PHP
- **Fichier error_log** dans votre dossier www/
- **Logs Apache/Nginx** si disponibles

## ✅ **PLAN D'ACTION :**

1. **Créer test.php** → Si ça marche, PHP fonctionne
2. **Créer test-config.php** → Si ça marche, config.php va
3. **Vérifier syntaxe** de tous les fichiers PHP
4. **Contacter o2switch** pour logs détaillés si nécessaire

**Le plus simple :** Recréez un `config.php` propre avec le code ci-dessus, ça résout 90% des erreurs 500 !