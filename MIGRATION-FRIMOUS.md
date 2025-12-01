# 📧 Migration de o2switch vers frlimous.odns.fr

## 🎯 Problème identifié
Votre configuration utilise actuellement o2switch (`dolphin.o2switch.net`) mais votre vraie boîte mail est sur **frlimous.odns.fr**.

## 🚀 Plan de migration

### Étape 1 : Tester la configuration frlimous
1. **Ouvrez** `smtp-detector-frlimous.php` dans votre navigateur
2. **Saisissez** vos identifiants frlimous réels :
   - Email : `VOTRE_EMAIL@frlimous.odns.fr`
   - Mot de passe : `VOTRE_MOT_DE_PASSE`
3. **Lancez** les tests pour identifier la bonne configuration SMTP
4. **Notez** les paramètres qui fonctionnent :
   - Host (probablement `mail.frlimous.odns.fr`)
   - Port (probablement `587` ou `465`)
   - Encryption (probablement `tls` ou `ssl`)

### Étape 2 : Mettre à jour config.php
```php
// Remplacez dans config.php
'smtp' => [
    'host' => 'mail.frlimous.odns.fr', // Valeur trouvée par le test
    'port' => 587, // Valeur trouvée par le test
    'encryption' => 'tls', // Valeur trouvée par le test
    'username' => 'VOTRE_EMAIL@frlimous.odns.fr', // Votre vrai email
    'password' => 'VOTRE_MOT_DE_PASSE', // Votre vrai mot de passe
    'from_email' => 'VOTRE_EMAIL@frlimous.odns.fr',
    'from_name' => 'Beverly Limousine',
    'timeout' => 20,
],

'email' => [
    'notification' => 'VOTRE_EMAIL@frlimous.odns.fr', // Votre vrai email
    'from' => 'VOTRE_EMAIL@frlimous.odns.fr',
],
```

### Étape 3 : Tester la nouvelle configuration
1. **Utilisez** `smtp-test-enhanced.php` pour tester la configuration
2. **Vérifiez** que les emails s'envoient correctement
3. **Consultez** les logs en cas de problème

## 🔧 Fichiers de diagnostic créés

| Fichier | Usage |
|---------|-------|
| `smtp-detector-frlimous.php` | 🔍 **Détecter la bonne configuration SMTP** |
| `config-frlimous-example.php` | 📋 **Exemple de configuration** |
| `email-status-check.php` | 📊 **Vérifier l'état des fichiers** |
| `smtp-test-enhanced.php` | 🧪 **Test SMTP complet** |

## ⚠️ Points d'attention

### Sécurité
- Ne laissez jamais les mots de passe en dur dans les fichiers
- Utilisez des variables d'environnement en production
- Supprimez les fichiers de diagnostic après utilisation

### Configuration probable
D'après votre webmail `webmail.frlimous.odns.fr`, voici les paramètres les plus probables :

```php
'smtp' => [
    'host' => 'mail.frlimous.odns.fr', // Le plus probable
    'port' => 587, // Port STARTTLS (le plus courant)
    'encryption' => 'tls', // Chiffrement moderne
    'username' => 'VOTRE_EMAIL@frlimous.odns.fr',
    'password' => 'VOTRE_MOT_DE_PASSE',
]
```

### Alternatives si le premier test échoue
Si `mail.frlimous.odns.fr:587` ne fonctionne pas, essayez :
- `mail.frlimous.odns.fr:465` (SSL)
- `localhost:587` (serveur local)
- `smtp.frlimous.odns.fr:587` (nom alternatif)

## ✅ Checklist de validation

- [ ] Test SMTP réussi avec la nouvelle configuration
- [ ] Email de test reçu dans votre boîte frlimous
- [ ] Formulaires du site envoient correctement
- [ ] Notifications de réservation fonctionnent
- [ ] Fichiers de diagnostic supprimés

## 🚨 En cas de problème

1. **Vérifiez** que votre compte email frlimous est actif
2. **Testez** la connexion sur votre webmail manuel
3. **Consultez** les logs d'erreur PHP
4. **Contactez** votre hébergeur frlimous pour les paramètres SMTP exacts

---

**💡 Prochaine étape** : Lancez `smtp-detector-frlimous.php` pour trouver la configuration exacte qui fonctionne !