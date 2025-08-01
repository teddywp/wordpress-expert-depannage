# 🛡️ Guide Expert - Sécurité WordPress

> **Protégez votre site WordPress contre les menaces les plus courantes avec l'expertise de 12+ années et 120+ sites piratés restaurés**

## 🎯 Statistiques alarmantes WordPress

- **30,000+ sites WordPress piratés quotidiennement**
- **98% des attaques exploitent des vulnérabilités connues**
- **Temps moyen de détection d'un piratage : 197 jours**
- **Coût moyen d'un site piraté : 2,500€ à 15,000€**

*Source : Études WPScan, Sucuri, Wordfence 2024*

## 🚨 Signes d'un site WordPress compromis

### Indicateurs critiques immédiats
```bash
# Vérification rapide via logs
tail -n 100 /var/log/apache2/access.log | grep -E "(admin-ajax|wp-login|xmlrpc)"

# Recherche fichiers suspects
find . -name "*.php" -exec grep -l "base64_decode\|eval\|exec\|system" {} \;
```

### Symptômes visibles
- ✅ **Redirections malveillantes** vers sites tiers
- ✅ **Pop-ups publicitaires** non autorisées  
- ✅ **Pages inconnues** apparaissant dans Google
- ✅ **Avertissements navigateurs** "Site dangereux"
- ✅ **Lenteur anormale** du site
- ✅ **Utilisateurs administrateurs** non reconnus

## 🔍 Audit de sécurité Expert WordPress

### Script de détection automatisée
```php
<?php
/**
 * AUDIT SÉCURITÉ WORDPRESS - Script Expert
 * Détection automatisée des vulnérabilités critiques
 */

class WordPressSecurityAudit {
    
    private $threats_found = array();
    private $vulnerabilities = array();
    
    public function runSecurityScan() {
        echo "🔍 AUDIT SÉCURITÉ WORDPRESS EXPERT\n";
        echo "==================================\n\n";
        
        $this->checkMaliciousFiles();
        $this->checkSuspiciousUsers();
        $this->checkFilePermissions();
        $this->checkConfigurationSecurity();
        $this->checkDatabaseIntegrity();
        $this->generateSecurityReport();
    }
    
    /**
     * Détection fichiers malveillants
     */
    private function checkMaliciousFiles() {
        echo "🦠 1. DÉTECTION MALWARES\n";
        echo "------------------------\n";
        
        $suspicious_patterns = array(
            'base64_decode',
            'eval\(',
            'exec\(',
            'system\(',
            'shell_exec',
            'file_get_contents.*http',
            'curl_exec',
            'preg_replace.*\/e',
            'assert\(',
            'create_function'
        );
        
        $scan_directories = array('./', 'wp-content/themes/', 'wp-content/plugins/');
        $infected_files = array();
        
        foreach ($scan_directories as $dir) {
            if (is_dir($dir)) {
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
                
                foreach ($iterator as $file) {
                    if ($file->isFile() && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                        $content = file_get_contents($file);
                        
                        foreach ($suspicious_patterns as $pattern) {
                            if (preg_match('/' . $pattern . '/i', $content)) {
                                $infected_files[] = array(
                                    'file' => $file->getPathname(),
                                    'pattern' => $pattern,
                                    'size' => $file->getSize()
                                );
                                break;
                            }
                        }
                    }
                }
            }
        }
        
        if (empty($infected_files)) {
            echo "✅ Aucun malware détecté\n";
        } else {
            echo "🚨 " . count($infected_files) . " fichier(s) suspect(s) détecté(s):\n";
            foreach ($infected_files as $file) {
                echo "   ❌ {$file['file']} - Pattern: {$file['pattern']}\n";
                $this->threats_found[] = "Fichier infecté: " . $file['file'];
            }
        }
        echo "\n";
    }
    
    /**
     * Vérification utilisateurs suspects
     */
    private function checkSuspiciousUsers() {
        echo "👥 2. ANALYSE UTILISATEURS\n";
        echo "-------------------------\n";
        
        if (!function_exists('get_users')) {
            echo "❌ WordPress non chargé\n\n";
            return;
        }
        
        $users = get_users();
        $suspicious_users = array();
        $admin_users = array();
        
        foreach ($users as $user) {
            // Utilisateurs administrateurs
            if (in_array('administrator', $user->roles)) {
                $admin_users[] = $user;
                
                // Noms suspects
                $suspicious_names = array('admin', 'administrator', 'root', 'test', 'demo', '123', 'user');
                if (in_array(strtolower($user->user_login), $suspicious_names)) {
                    $suspicious_users[] = $user->user_login . ' (nom générique)';
                }
                
                // Emails suspects
                if (strpos($user->user_email, 'temp') !== false || 
                    strpos($user->user_email, '123') !== false ||
                    strpos($user->user_email, 'test') !== false) {
                    $suspicious_users[] = $user->user_login . ' (email suspect: ' . $user->user_email . ')';
                }
            }
        }
        
        echo "Administrateurs total: " . count($admin_users) . "\n";
        
        if (count($admin_users) > 3) {
            $this->vulnerabilities[] = "Trop d'administrateurs: " . count($admin_users);
            echo "⚠️  Nombre élevé d'administrateurs (" . count($admin_users) . ")\n";
        }
        
        if (!empty($suspicious_users)) {
            echo "🚨 Utilisateurs suspects détectés:\n";
            foreach ($suspicious_users as $suspicious) {
                echo "   ❌ $suspicious\n";
                $this->threats_found[] = "Utilisateur suspect: $suspicious";
            }
        } else {
            echo "✅ Utilisateurs vérifiés\n";
        }
        echo "\n";
    }
    
    /**
     * Vérification permissions fichiers
     */
    private function checkFilePermissions() {
        echo "🔐 3. PERMISSIONS FICHIERS\n";
        echo "-------------------------\n";
        
        $critical_files = array(
            'wp-config.php' => array('recommended' => '0644', 'max' => '0644'),
            '.htaccess' => array('recommended' => '0644', 'max' => '0644'),
            'wp-content' => array('recommended' => '0755', 'max' => '0755'),
            'wp-admin' => array('recommended' => '0755', 'max' => '0755'),
            'wp-includes' => array('recommended' => '0755', 'max' => '0755')
        );
        
        foreach ($critical_files as $file => $perms) {
            if (file_exists($file)) {
                $current = substr(sprintf('%o', fileperms($file)), -4);
                echo "$file: $current ";
                
                if ($current === $perms['recommended']) {
                    echo "✅\n";
                } elseif (octdec($current) <= octdec($perms['max'])) {
                    echo "⚠️  (recommandé: {$perms['recommended']})\n";
                    $this->vulnerabilities[] = "Permissions $file: $current";
                } else {
                    echo "❌ DANGEREUX\n";
                    $this->threats_found[] = "Permissions dangereuses $file: $current";
                }
            } else {
                echo "$file: ❌ MANQUANT\n";
                $this->threats_found[] = "Fichier critique manquant: $file";
            }
        }
        echo "\n";
    }
    
    /**
     * Configuration sécurité WordPress
     */
    private function checkConfigurationSecurity() {
        echo "⚙️  4. CONFIGURATION SÉCURITÉ\n";
        echo "-----------------------------\n";
        
        if (!file_exists('wp-config.php')) {
            echo "❌ wp-config.php manquant\n\n";
            return;
        }
        
        $wp_config = file_get_contents('wp-config.php');
        $security_checks = array();
        
        // Clés de sécurité
        $security_keys = array(
            'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY',
            'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT'
        );
        
        $missing_keys = 0;
        foreach ($security_keys as $key) {
            if (strpos($wp_config, $key) === false || 
                strpos($wp_config, "define('$key', 'put your unique phrase here')") !== false) {
                $missing_keys++;
            }
        }
        
        if ($missing_keys === 0) {
            echo "✅ Clés de sécurité configurées (8/8)\n";
        } else {
            echo "❌ Clés de sécurité manquantes: $missing_keys/8\n";
            $this->vulnerabilities[] = "$missing_keys clés de sécurité non configurées";
        }
        
        // Debug mode
        if (strpos($wp_config, "define('WP_DEBUG', true)") !== false) {
            echo "❌ Mode debug activé (risque sécurité)\n";
            $this->vulnerabilities[] = "Mode debug activé en production";
        } else {
            echo "✅ Mode debug désactivé\n";
        }
        
        // File editing
        if (strpos($wp_config, "define('DISALLOW_FILE_EDIT', true)") !== false) {
            echo "✅ Édition fichiers désactivée\n";
        } else {
            echo "⚠️  Édition fichiers autorisée\n";
            $this->vulnerabilities[] = "Édition de fichiers via admin non désactivée";
        }
        
        // Force SSL
        if (strpos($wp_config, "define('FORCE_SSL_ADMIN', true)") !== false) {
            echo "✅ SSL forcé sur admin\n";
        } else {
            echo "⚠️  SSL admin non forcé\n";
            $this->vulnerabilities[] = "SSL non forcé sur administration";
        }
        
        echo "\n";
    }
    
    /**
     * Intégrité base de données
     */
    private function checkDatabaseIntegrity() {
        echo "🗄️  5. INTÉGRITÉ BASE DE DONNÉES\n";
        echo "--------------------------------\n";
        
        if (!function_exists('get_option')) {
            echo "❌ WordPress non chargé\n\n";
            return;
        }
        
        global $wpdb;
        
        // Vérification table options suspectes
        $suspicious_options = $wpdb->get_results(
            "SELECT option_name, option_value FROM {$wpdb->options} 
             WHERE option_name LIKE '%temp%' 
             OR option_name LIKE '%cache%' 
             OR option_value LIKE '%base64%'
             OR option_value LIKE '%eval(%'
             LIMIT 20"
        );
        
        if (!empty($suspicious_options)) {
            echo "🚨 Options suspectes détectées:\n";
            foreach ($suspicious_options as $option) {
                echo "   ❌ {$option->option_name}\n";
                $this->threats_found[] = "Option DB suspecte: {$option->option_name}";
            }
        } else {
            echo "✅ Table options vérifiée\n";
        }
        
        // Vérification posts suspects
        $suspicious_posts = $wpdb->get_results(
            "SELECT ID, post_title, post_content FROM {$wpdb->posts} 
             WHERE post_content LIKE '%<script%' 
             OR post_content LIKE '%javascript:%'
             OR post_content LIKE '%base64%'
             LIMIT 10"
        );
        
        if (!empty($suspicious_posts)) {
            echo "🚨 Contenus suspects détectés:\n";
            foreach ($suspicious_posts as $post) {
                echo "   ❌ Post ID {$post->ID}: {$post->post_title}\n";
                $this->threats_found[] = "Contenu suspect: Post ID {$post->ID}";
            }
        } else {
            echo "✅ Contenus vérifiés\n";
        }
        
        echo "\n";
    }
    
    /**
     * Rapport de sécurité final
     */
    private function generateSecurityReport() {
        echo "📋 RAPPORT DE SÉCURITÉ EXPERT\n";
        echo "=============================\n\n";
        
        $total_issues = count($this->threats_found) + count($this->vulnerabilities);
        
        echo "🚨 Menaces critiques: " . count($this->threats_found) . "\n";
        echo "⚠️  Vulnérabilités: " . count($this->vulnerabilities) . "\n";
        echo "📊 Total problèmes: $total_issues\n\n";
        
        // Menaces critiques
        if (!empty($this->threats_found)) {
            echo "🚨 MENACES CRITIQUES - ACTION IMMÉDIATE REQUISE:\n";
            foreach ($this->threats_found as $threat) {
                echo "   ❌ $threat\n";
            }
            echo "\n";
        }
        
        // Vulnérabilités
        if (!empty($this->vulnerabilities)) {
            echo "⚠️  VULNÉRABILITÉS À CORRIGER:\n";
            foreach ($this->vulnerabilities as $vulnerability) {
                echo "   ⚠️  $vulnerability\n";
            }
            echo "\n";
        }
        
        // Niveau de risque
        if (count($this->threats_found) > 0) {
            $risk_level = "🚨 CRITIQUE";
            $recommendation = "INTERVENTION D'URGENCE NÉCESSAIRE";
        } elseif (count($this->vulnerabilities) > 3) {
            $risk_level = "⚠️  ÉLEVÉ";
            $recommendation = "Correction rapide recommandée";
        } elseif (count($this->vulnerabilities) > 0) {
            $risk_level = "🟡 MODÉRÉ";
            $recommendation = "Améliorations de sécurité suggérées";
        } else {
            $risk_level = "✅ FAIBLE";
            $recommendation = "Sécurité satisfaisante";
        }
        
        echo "🎯 NIVEAU DE RISQUE: $risk_level\n";
        echo "💡 RECOMMANDATION: $recommendation\n\n";
        
        if (!empty($this->threats_found)) {
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "🆘 SITE COMPROMIS DÉTECTÉ !\n";
            echo "🔧 Nettoyage professionnel requis immédiatement\n";
            echo "📞 Service d'urgence: https://teddywp.com/depannage-wordpress/\n";
            echo "⚡ Intervention sous 6h - Expert WordPress certifié\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        }
    }
}

// Lancement de l'audit sécurité
$security_audit = new WordPressSecurityAudit();
$security_audit->runSecurityScan();
?>
```

## 🛡️ Hardening WordPress - Configuration Expert

### wp-config.php - Sécurisation avancée
```php
<?php
/**
 * CONFIGURATION SÉCURISÉE WORDPRESS
 * Développée par Expert WordPress - 12+ années d'expérience
 */

// === CLÉS DE SÉCURITÉ ===
// Générées via https://api.wordpress.org/secret-key/1.1/salt/
define('AUTH_KEY',         'votre-clé-unique-64-caractères');
define('SECURE_AUTH_KEY',  'votre-clé-unique-64-caractères');
define('LOGGED_IN_KEY',    'votre-clé-unique-64-caractères');
define('NONCE_KEY',        'votre-clé-unique-64-caractères');
define('AUTH_SALT',        'votre-clé-unique-64-caractères');
define('SECURE_AUTH_SALT', 'votre-clé-unique-64-caractères');
define('LOGGED_IN_SALT',   'votre-clé-unique-64-caractères');
define('NONCE_SALT',       'votre-clé-unique-64-caractères');

// === SÉCURITÉ RENFORCÉE ===
define('DISALLOW_FILE_EDIT', true);        // Désactive éditeur fichiers
define('DISALLOW_FILE_MODS', true);        // Désactive installation plugins/thèmes
define('FORCE_SSL_ADMIN', true);           // Force SSL sur admin
define('WP_POST_REVISIONS', 3);            // Limite révisions
define('EMPTY_TRASH_DAYS', 7);             // Vide corbeille auto
define('WP_CRON_LOCK_TIMEOUT', 120);       // Timeout cron

// === SÉCURITÉ DATABASE ===
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');

// === DEBUG - DÉSACTIVÉ EN PRODUCTION ===
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);

// === OPTIMISATIONS SÉCURITÉ ===
define('CONCATENATE_SCRIPTS', true);       // Optimise JS
define('COMPRESS_SCRIPTS', true);          // Compresse JS
define('COMPRESS_CSS', true);              // Compresse CSS

// === PROTECTION SUPPLÉMENTAIRE ===
if (!defined('ABSPATH')) {
    exit;
}

// Limitation tentatives connexion
if (!defined('WP_FAIL2BAN_BLOCKED_USERS')) {
    define('WP_FAIL2BAN_BLOCKED_USERS', array('admin', 'administrator', 'root'));
}
?>
```

### .htaccess - Protection serveur
```apache
# PROTECTION WORDPRESS EXPERT - .htaccess sécurisé
# Développé par Expert WordPress avec 12+ années d'expérience

# === PROTECTION GÉNÉRALE ===
Options -Indexes
ServerSignature Off

# === PROTECTION wp-config.php ===
<Files wp-config.php>
    Order allow,deny
    Deny from all
</Files>

# === PROTECTION .htaccess ===
<Files .htaccess>
    Order allow,deny
    Deny from all
</Files>

# === PROTECTION FICHIERS SENSIBLES ===
<FilesMatch "\.(htaccess|htpasswd|ini|log|sh|sql|tar|gz)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# === PROTECTION wp-admin ===
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_METHOD} POST
    RewriteCond %{REQUEST_URI} ^/wp-admin/.*$
    RewriteCond %{HTTP_REFERER} !^https?://votre-domaine\.com/.*$ [NC]
    RewriteRule ^(.*)$ - [F,L]
</IfModule>

# === PROTECTION CONTRE INJECTIONS ===
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{QUERY_STRING} (\<|%3C).*script.*(\>|%3E) [NC,OR]
    RewriteCond %{QUERY_STRING} GLOBALS(=|\[|\%[0-9A-Z]{0,2}) [OR]
    RewriteCond %{QUERY_STRING} (\<|%3C).*iframe.*(\>|%3E) [NC,OR]
    RewriteCond %{QUERY_STRING} (\<|%3C).*object.*(\>|%3E) [NC,OR]
    RewriteCond %{QUERY_STRING} base64_decode.*\(.*\) [NC,OR]
    RewriteCond %{QUERY_STRING} (\<|%3C).*embed.*(\>|%3E) [NC]
    RewriteRule ^(.*)$ - [F,L]
</IfModule>

# === PROTECTION XMLRPC ===
<Files xmlrpc.php>
    Order allow,deny
    Deny from all
</Files>

# === LIMITATION TAILLE UPLOADS ===
<IfModule mod_php.c>
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value max_execution_time 120
    php_value max_input_vars 3000
</IfModule>

# === HEADERS SÉCURITÉ ===
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options SAMEORIGIN
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
</IfModule>

# === CACHE NAVIGATEUR ===
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/pdf "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType application/x-javascript "access plus 1 month"
    ExpiresByType application/x-shockwave-flash "access plus 1 month"
    ExpiresByType image/x-icon "access plus 1 year"
</IfModule>

# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
```

## 🚨 Procédure de nettoyage malware

### Étapes d'intervention Expert
1. **Isolation immédiate** - Mise hors ligne temporaire
2. **Sauvegarde de sécurité** - Avant nettoyage  
3. **Scan complet** - Identification tous fichiers infectés
4. **Nettoyage chirurgical** - Suppression codes malveillants
5. **Restauration intégrité** - Fichiers WordPress core
6. **Hardening complet** - Prévention récidives
7. **Tests exhaustifs** - Vérification fonctionnalités
8. **Monitoring actif** - Surveillance post-nettoyage

### Coût d'un site piraté
- **Perte trafic Google** : -95% en 24h
- **Temps de récupération** : 3-8 semaines
- **Coût nettoyage professionnel** : 500€ - 2,500€
- **Perte revenus** : Variable selon activité
- **Atteinte réputation** : Impact long terme

## 📞 Service Expert de Sécurité WordPress

**🆘 Site piraté ? Intervention d'urgence sous 6h**

✅ **Plus de 120 sites piratés nettoyés avec succès**  
✅ **Taux de récupération : 98%**  
✅ **Garantie "Site propre ou remboursé"**  
✅ **Monitoring post-nettoyage inclus**  

📧 **Contact direct : [https://teddywp.com/depannage-wordpress/](https://teddywp.com/depannage-wordpress/)**

---

*Guide rédigé par Teddy - Expert WordPress certifié | 12+ années d'expérience | 800+ sites sécurisés*
