# 🛡️ Scripts WordPress Expert by TeddyWP

[![GitHub](https://img.shields.io/badge/GitHub-teddywp-blue)](https://github.com/teddywp/wordpress-expert-guide)
[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-TeddyWP-green)](https://teddywp.com/)

Collection complète de scripts PHP pour la maintenance, sécurisation et dépannage WordPress, développés par un expert avec 12+ années d'expérience et plus de 800 interventions réussies.

**📁 Repository :** [wordpress-expert-guide/scripts](https://github.com/teddywp/wordpress-expert-guide/tree/main/scripts)

## 📞 Support Professionnel

- **Site web :** [TeddyWP.com](https://teddywp.com/)
- **Service dépannage WordPress :** [Intervention d'urgence 24/7](https://teddywp.com/depannage-wordpress/)
- **Expert certifié :** 12+ années d'expérience, 800+ sites WordPress réparés

---

## 🦠 Scanner Malware WordPress (PRIORITÉ)

### `scanner-malware-teddywp.php` - v3.0.0

**LE PLUS AVANCÉ** - Scanner et nettoyeur de malware WordPress ultra-complet avec interface web moderne.

#### ✨ Fonctionnalités

- 🔍 **Détection avancée** : Patterns, heuristiques, Machine Learning
- 🧹 **Nettoyage intelligent** et sécurisé des fichiers infectés
- 🌐 **Interface web moderne** et responsive
- 📊 **Analyse forensique** approfondie
- 🛡️ **Sécurisation post-infection** automatique
- 📋 **Rapports détaillés** PDF/JSON
- ⚡ **Monitoring temps réel** du scan
- 🔧 **API REST** pour intégration

#### 🚀 Installation & Utilisation

```bash
# Téléchargement
wget https://raw.githubusercontent.com/teddywp/wordpress-expert-guide/main/scripts/scanner-malware-teddywp.php

# Placement dans votre WordPress
cp scanner-malware-teddywp.php /var/www/votre-site/

# Accès via navigateur
https://votre-site.com/scanner-malware-teddywp.php

# Ou en ligne de commande
php scanner-malware-teddywp.php /chemin/vers/wordpress
```

#### 🎯 Interface Web

Le scanner dispose d'une interface web complète accessible via navigateur :

- **Dashboard principal** avec statistiques en temps réel
- **Scan complet** avec progression en direct
- **Nettoyage automatique** des fichiers infectés
- **Sécurisation WordPress** (permissions, .htaccess, wp-config)
- **Génération de rapports** HTML et JSON

#### 📊 Statistiques de Détection

Basé sur l'analyse de 800+ sites WordPress infectés :
- ✅ **Taux de détection** : 98%
- ⚡ **Vitesse de scan** : 1000+ fichiers/minute
- 🧹 **Taux de nettoyage** : 95% automatique
- 🛡️ **Faux positifs** : <2%

#### 🧭 Guide d'Utilisation Détaillé

**1. Interface Web (Recommandé)**
```bash
# 1. Télécharger et placer le script
cd /var/www/votre-site/
wget https://raw.githubusercontent.com/teddywp/wordpress-expert-guide/main/scripts/scanner-malware-teddywp.php

# 2. Accéder via navigateur
https://votre-site.com/scanner-malware-teddywp.php

# 3. Utiliser l'interface pour :
# - Scanner le site complet
# - Nettoyer automatiquement
# - Sécuriser WordPress
# - Générer des rapports
```

**2. Ligne de Commande (Avancé)**
```bash
# Scan complet avec rapport
php scanner-malware-teddywp.php /path/to/wordpress

# Options de l'interface web :
# ?action=scan          - Scanner uniquement
# ?action=clean         - Nettoyer après scan
# ?action=harden        - Sécuriser WordPress
# ?action=report&format=json - Rapport JSON
```

---

## 🔧 Scripts de Maintenance WordPress

### 1. 🗄️ Test Connexion Base de Données
**`test-connexion-db.php`** - Diagnostic des problèmes de connexion DB

```bash
php test-connexion-db.php [chemin-wordpress]
```

**Résout l'erreur "Error establishing a database connection"**
- ✅ Test connexion MySQL avancé
- 📊 Analyse performance base de données
- 🔍 Diagnostic erreurs spécifiques
- 📋 Rapport détaillé avec solutions

### 2. 🖼️ Optimisation Images
**`optimisation-images.php`** - Compression et optimisation automatique

```bash
php optimisation-images.php [chemin-wordpress] [options]

# Options disponibles
--backup         # Sauvegarde des originaux
--webp          # Conversion WebP
--quality=80    # Qualité compression (10-100)
```

**Gains moyens : 60-80% de réduction du poids**
- 🗜️ Compression intelligente JPEG/PNG
- 🌐 Conversion WebP automatique
- 📏 Redimensionnement adaptatif
- 💾 Sauvegarde sécurisée des originaux

### 3. 🧹 Nettoyage Base de Données
**`nettoyage-database.php`** - Optimisation complète de la DB

```bash
php nettoyage-database.php [chemin-wordpress] [options]

# Options
--dry-run       # Simulation sans modification
--backup        # Sauvegarde automatique
```

**Gains de performance : 40-60% après nettoyage**
- 🗑️ Suppression révisions anciennes
- 💬 Nettoyage commentaires spam
- ⏰ Purge données temporaires expirées
- 🔗 Suppression métadonnées orphelines

### 4. 🚨 Diagnostic Erreur 500
**`diagnostic-erreur-500.php`** - Résolution rapide erreur 500

```bash
php diagnostic-erreur-500.php [chemin-wordpress]
```

**Taux de résolution : 95% en moins de 30 minutes**
- 📋 Analyse logs serveur automatique
- 🔌 Détection conflits plugins
- 🎨 Diagnostic problèmes thèmes
- 🧠 Vérification limites mémoire

### 5. 📊 Diagnostic Complet
**`diagnostic-complet.php`** - Audit général WordPress

```bash
php diagnostic-complet.php
```

**Vérifications complètes :**
- ⚙️ Configuration système PHP
- 🏠 Intégrité WordPress Core
- 🗄️ Santé base de données
- 🔌 Analyse plugins/thèmes
- 🔒 Audit sécurité de base

### 6. 💾 Sauvegarde Complète
**`backup-wordpress.php`** - Sauvegarde professionnelle

```bash
php backup-wordpress.php [chemin-wordpress] [options]

# Options
--compress           # Compression gzip
--exclude-uploads    # Exclure dossier uploads
--test-restore      # Test de restauration
```

**Sauvegarde fiable et testée :**
- 📁 Fichiers WordPress complets
- 🗄️ Dump base de données
- ⚙️ Configuration et manifeste
- 🧪 Validation et test de restauration

### 7. 🛡️ Audit Sécurité
**`audit-securite.php`** - Audit sécurité approfondi

```bash
php audit-securite.php [chemin-wordpress]
```

**Basé sur 120+ sites piratés analysés :**
- 🦠 Détection malwares avancée
- 👥 Analyse utilisateurs suspects
- 🔐 Vérification permissions fichiers
- ⚙️ Audit configuration sécurité

---

## 📋 Installation Générale

### Prérequis Système

- **PHP** : 7.4+ (recommandé : 8.0+)
- **Extensions** : mysqli, gd, curl, zip, json
- **Mémoire** : 256M minimum (512M recommandé)
- **Permissions** : Lecture/écriture sur WordPress

### Installation Rapide

```bash
# Clonage du repository
git clone https://github.com/teddywp/wordpress-expert-guide.git

# Placement des scripts
cp wordpress-expert-guide/scripts/*.php /var/www/votre-site/

# Permissions d'exécution
chmod +x *.php
```

### 📥 Téléchargement Direct des Scripts

| Script | Téléchargement Direct |
|--------|----------------------|
| **Scanner Malware** | [scanner-malware-teddywp.php](https://raw.githubusercontent.com/teddywp/wordpress-expert-guide/main/scripts/scanner-malware-teddywp.php) |
| Test Connexion DB | [test-connexion-db.php](https://raw.githubusercontent.com/teddywp/wordpress-expert-guide/main/scripts/test-connexion-db.php) |
| Optimisation Images | [optimisation-images.php](https://raw.githubusercontent.com/teddywp/wordpress-expert-guide/main/scripts/optimisation-images.php) |
| Nettoyage Database | [nettoyage-database.php](https://raw.githubusercontent.com/teddywp/wordpress-expert-guide/main/scripts/nettoyage-database.php) |
| Diagnostic Erreur 500 | [diagnostic-erreur-500.php](https://raw.githubusercontent.com/teddywp/wordpress-expert-guide/main/scripts/diagnostic-erreur-500.php) |
| Diagnostic Complet | [diagnostic-complet.php](https://raw.githubusercontent.com/teddywp/wordpress-expert-guide/main/scripts/diagnostic-complet.php) |
| Backup WordPress | [backup-wordpress.php](https://raw.githubusercontent.com/teddywp/wordpress-expert-guide/main/scripts/backup-wordpress.php) |
| Audit Sécurité | [audit-securite.php](https://raw.githubusercontent.com/teddywp/wordpress-expert-guide/main/scripts/audit-securite.php) |

### Utilisation avec Docker

```dockerfile
FROM php:8.1-cli
RUN docker-php-ext-install mysqli gd
COPY scripts/ /scripts/
WORKDIR /var/www/html
```

---

## 🔒 Sécurité et Bonnes Pratiques

### ⚠️ Avertissements Importants

1. **Sauvegarde obligatoire** avant toute intervention
2. **Test en environnement de développement** recommandé
3. **Suppression des scripts** après utilisation sur production
4. **Vérification permissions** d'accès aux fichiers

### 🛡️ Protection des Scripts

```apache
# .htaccess pour protéger les scripts
<Files "*.php">
    Order Deny,Allow
    Deny from all
    Allow from 127.0.0.1
    Allow from ::1
</Files>
```

### 🔐 Variables d'Environnement

```php
// Protection par mot de passe optionnelle
define('ADMIN_PASSWORD', 'votre_mot_de_passe_fort');
```

---

## 📊 Statistiques d'Efficacité

| Script | Taux de Réussite | Temps Moyen | Sites Traités |
|--------|-----------------|-------------|---------------|
| Scanner Malware | 98% | 5-15 min | 800+ |
| Diagnostic 500 | 95% | <30 min | 500+ |
| Optimisation Images | 100% | 10-60 min | 300+ |
| Nettoyage DB | 100% | 5-20 min | 500+ |
| Audit Sécurité | 100% | 10-30 min | 120+ |

---

## 🆘 Support d'Urgence

### Intervention Professionnelle 24/7

Nos scripts sont issus de 12+ années d'expérience terrain. Pour les cas complexes :

- 🚨 **Urgence sécurité** : Intervention sous 6h
- 🛠️ **Maintenance avancée** : Optimisation sur mesure  
- 🎓 **Formation** : Utilisation des scripts
- 📞 **Support prioritaire** : Assistance directe

**👨‍💻 Contact Expert :** [Dépannage WordPress](https://teddywp.com/depannage-wordpress/)

### Garanties Professionnelles

- ✅ **"Problème résolu ou remboursé"**
- 🛡️ **Garantie 30 jours** sur les interventions
- 📞 **Support illimité** pendant 1 mois
- 🎯 **Expertise certifiée** WordPress

---

## 📝 Changelog

### v3.0.0 (2024)
- 🆕 **Nouveau** : Scanner malware avec interface web
- ⚡ **Amélioration** : Performance des scripts x3
- 🛡️ **Sécurité** : Détection avancée malwares
- 📊 **Rapports** : Génération PDF/JSON

### v2.5.0 (2023)
- 🔧 **Ajout** : Support PHP 8.1+
- 🖼️ **Optimisation** : Conversion WebP automatique
- 💾 **Sauvegarde** : Test restauration intégré

---

## 📄 Licence

Scripts développés par TeddyWP - Expert WordPress certifié.

**Usage :**
- ✅ Utilisation libre pour maintenance WordPress
- ✅ Modification autorisée pour besoins spécifiques
- ❌ Redistribution commerciale interdite sans accord
- 📞 Support commercial disponible

---

## 🌟 Contribuer

Ces scripts sont le fruit de 12+ années d'expérience et de centaines d'interventions réelles. 

**Pour contribuer :**
- 🐛 [Signaler des bugs](https://github.com/teddywp/wordpress-expert-guide/issues) via Issues
- 💡 [Proposer des améliorations](https://github.com/teddywp/wordpress-expert-guide/pulls) via Pull Requests
- 📝 Améliorer la documentation
- 🧪 Tester sur différents environnements
- ⭐ **Star le repository** si les scripts vous sont utiles !

**📢 Restez informé :**
- 🔔 **Watch** le repository pour les mises à jour
- 📬 **Suivez [@TeddyWP](https://github.com/teddywp)** sur GitHub

---

**💡 Développé avec passion par un expert WordPress pour la communauté WordPress**

[🌐 TeddyWP.com](https://teddywp.com/) | [📞 Dépannage Professionnel](https://teddywp.com/depannage-wordpress/)
