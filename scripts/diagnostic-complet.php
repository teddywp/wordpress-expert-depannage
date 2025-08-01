<?php
/**
 * DIAGNOSTIC EXPERT WORDPRESS - Script automatisé
 * 
 * Développé par un Expert WordPress avec 12+ années d'expérience
 * Plus de 800 sites diagnostiqués et réparés avec ce script
 * 
 * @author Teddy - Expert WordPress
 * @version 2.1
 * @website https://teddywp.com
 * @service https://teddywp.com/depannage-wordpress/
 */

// Configuration du script
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 300);

class WordPressExpertDiagnostic {
    
    private $errors = array();
    private $warnings = array();
    private $success = array();
    private $start_time;
    
    public function __construct() {
        $this->start_time = microtime(true);
        echo "🔍 DIAGNOSTIC EXPERT WORDPRESS - Version 2.1\n";
        echo "===========================================\n\n";
    }
    
    /**
     * Lancement du diagnostic complet
     */
    public function runCompleteDiagnostic() {
        $this->checkSystemRequirements();
        $this->checkWordPressCore();
        $this->checkDatabase();
        $this->checkPluginsAndThemes();
        $this->checkSecurity();
        $this->checkPerformance();
        $this->generateReport();
    }
    
    /**
     * Vérification configuration système
     */
    private function checkSystemRequirements() {
        echo "📊 1. VÉRIFICATION SYSTÈME\n";
        echo "--------------------------\n";
        
        // Version PHP
        $php_version = phpversion();
        echo "PHP Version: $php_version\n";
        
        if (version_compare($php_version, '8.0', '<')) {
            $this->warnings[] = "PHP version obsolète ($php_version). Recommandé: PHP 8.0+";
        } else {
            $this->success[] = "Version PHP compatible ($php_version)";
        }
        
        // Mémoire PHP
        $memory_limit = ini_get('memory_limit');
        $memory_usage = memory_get_usage(true);
        $memory_mb = round($memory_usage / 1024 / 1024, 2);
        
        echo "Limite mémoire: $memory_limit\n";
        echo "Mémoire utilisée: {$memory_mb} MB\n";
        
        if ($memory_mb > 256) {
            $this->warnings[] = "Consommation mémoire élevée: {$memory_mb} MB";
        }
        
        // Extensions PHP critiques
        $required_extensions = array('mysqli', 'gd', 'curl', 'zip', 'json');
        echo "Extensions PHP:\n";
        
        foreach ($required_extensions as $ext) {
            if (extension_loaded($ext)) {
                echo "  ✅ $ext\n";
            } else {
                echo "  ❌ $ext (MANQUANTE)\n";
                $this->errors[] = "Extension PHP manquante: $ext";
            }
        }
        
        echo "\n";
    }
    
    /**
     * Vérification WordPress Core
     */
    private function checkWordPressCore() {
        echo "🏠 2. WORDPRESS CORE\n";
        echo "--------------------\n";
        
        // Vérification fichiers critiques
        $critical_files = array(
            'wp-config.php' => 'Configuration principale',
            'wp-load.php' => 'Chargeur WordPress',
            '.htaccess' => 'Règles Apache',
            'index.php' => 'Point d\'entrée'
        );
        
        foreach ($critical_files as $file => $description) {
            if (file_exists($file)) {
                echo "✅ $file ($description)\n";
                
                // Vérification permissions
                $perms = substr(sprintf('%o', fileperms($file)), -4);
                if ($file === 'wp-config.php' && $perms !== '0644') {
                    $this->warnings[] = "Permissions wp-config.php: $perms (recommandé: 0644)";
                }
            } else {
                echo "❌ $file MANQUANT\n";
                $this->errors[] = "Fichier critique manquant: $file";
            }
        }
        
        // Chargement WordPress si possible
        if (file_exists('wp-config.php')) {
            try {
                define('WP_USE_THEMES', false);
                require_once('wp-config.php');
                
                if (function_exists('get_bloginfo')) {
                    $wp_version = get_bloginfo('version');
                    echo "✅ WordPress Version: $wp_version\n";
                    
                    // Vérification version obsolète
                    if (version_compare($wp_version, '6.0', '<')) {
                        $this->warnings[] = "Version WordPress obsolète ($wp_version)";
                    }
                } else {
                    $this->errors[] = "WordPress Core non fonctionnel";
                }
            } catch (Exception $e) {
                $this->errors[] = "Erreur chargement WordPress: " . $e->getMessage();
            }
        }
        
        echo "\n";
    }
    
    /**
     * Diagnostic base de données
     */
    private function checkDatabase() {
        echo "🗄️  3. BASE DE DONNÉES\n";
        echo "----------------------\n";
        
        if (!defined('DB_HOST')) {
            $this->errors[] = "Configuration base de données manquante";
            return;
        }
        
        // Test connexion
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASSWORD,
                array(PDO::ATTR_TIMEOUT => 5)
            );
            
            echo "✅ Connexion base de données réussie\n";
            
            // Taille de la base
            $stmt = $pdo->query("SELECT 
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb 
                FROM information_schema.tables 
                WHERE table_schema = '" . DB_NAME . "'");
            
            $db_size = $stmt->fetchColumn();
            echo "📦 Taille base de données: {$db_size} MB\n";
            
            if ($db_size > 500) {
                $this->warnings[] = "Base de données volumineuse: {$db_size} MB";
            }
            
            // Vérification tables WordPress
            if (function_exists('wp_get_theme')) {
                global $wpdb;
                $tables_check = array('posts', 'options', 'users', 'comments');
                
                foreach ($tables_check as $table) {
                    $table_name = $wpdb->prefix . $table;
                    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                    echo "📊 $table_name: $count entrées\n";
                }
            }
            
        } catch (PDOException $e) {
            echo "❌ Erreur connexion DB: " . $e->getMessage() . "\n";
            $this->errors[] = "Connexion base de données impossible";
            
            // Diagnostic spécifique
            if (strpos($e->getMessage(), 'Access denied') !== false) {
                $this->errors[] = "Identifiants base de données incorrects";
            } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
                $this->errors[] = "Serveur MySQL inaccessible";
            }
        }
        
        echo "\n";
    }
    
    /**
     * Analyse plugins et thèmes
     */
    private function checkPluginsAndThemes() {
        echo "🔌 4. PLUGINS ET THÈMES\n";
        echo "-----------------------\n";
        
        if (!function_exists('get_option')) {
            echo "❌ WordPress non chargé - impossible d'analyser plugins\n\n";
            return;
        }
        
        // Plugins actifs
        $active_plugins = get_option('active_plugins', array());
        echo "📱 Plugins actifs: " . count($active_plugins) . "\n";
        
        // Plugins problématiques connus
        $problematic_plugins = array(
            'wp-super-cache/wp-cache.php' => 'Cache conflicts possibles',
            'w3-total-cache/w3-total-cache.php' => 'Configuration complexe',
            'wordfence/wordfence.php' => 'Peut bloquer légitimes requêtes',
            'jetpack/jetpack.php' => 'Peut ralentir le site',
            'yoast-seo/wp-seo.php' => 'Base de données volumineuse'
        );
        
        $found_issues = false;
        foreach ($active_plugins as $plugin) {
            if (isset($problematic_plugins[$plugin])) {
                echo "⚠️  $plugin - " . $problematic_plugins[$plugin] . "\n";
                $this->warnings[] = "Plugin à surveiller: $plugin";
                $found_issues = true;
            }
        }
        
        if (!$found_issues) {
            echo "✅ Aucun plugin problématique détecté\n";
        }
        
        // Thème actif
        if (function_exists('wp_get_theme')) {
            $current_theme = wp_get_theme();
            echo "🎨 Thème actif: " . $current_theme->get('Name') . "\n";
            echo "   Version: " . $current_theme->get('Version') . "\n";
            
            // Vérification fichiers thème critiques
            $theme_files = array('style.css', 'index.php', 'functions.php');
            $theme_path = $current_theme->get_stylesheet_directory();
            
            foreach ($theme_files as $file) {
                if (file_exists($theme_path . '/' . $file)) {
                    echo "   ✅ $file\n";
                } else {
                    echo "   ❌ $file manquant\n";
                    $this->errors[] = "Fichier thème manquant: $file";
                }
            }
        }
        
        echo "\n";
    }
    
    /**
     * Vérification sécurité
     */
    private function checkSecurity() {
        echo "🔒 5. SÉCURITÉ WORDPRESS\n";
        echo "------------------------\n";
        
        // Vérification wp-config.php
        if (file_exists('wp-config.php')) {
            $wp_config = file_get_contents('wp-config.php');
            
            // Clés de sécurité
            $security_keys = array('AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY');
            $missing_keys = 0;
            
            foreach ($security_keys as $key) {
                if (strpos($wp_config, $key) === false) {
                    $missing_keys++;
                }
            }
            
            if ($missing_keys > 0) {
                echo "⚠️  Clés de sécurité manquantes: $missing_keys/8\n";
                $this->warnings[] = "$missing_keys clés de sécurité manquantes";
            } else {
                echo "✅ Clés de sécurité configurées\n";
            }
            
            // Debug mode en production
            if (strpos($wp_config, "define('WP_DEBUG', true)") !== false) {
                echo "⚠️  Mode debug activé (risque sécurité)\n";
                $this->warnings[] = "Mode debug activé en production";
            }
        }
        
        // Vérification permissions fichiers
        $file_permissions = array(
            'wp-config.php' => '0644',
            '.htaccess' => '0644',
            'wp-content' => '0755'
        );
        
        foreach ($file_permissions as $file => $recommended) {
            if (file_exists($file)) {
                $current = substr(sprintf('%o', fileperms($file)), -4);
                if ($current !== $recommended) {
                    echo "⚠️  Permissions $file: $current (recommandé: $recommended)\n";
                    $this->warnings[] = "Permissions incorrectes: $file ($current)";
                } else {
                    echo "✅ Permissions $file: $current\n";
                }
            }
        }
        
        // Vérification utilisateurs suspects
        if (function_exists('get_users')) {
            $users = get_users(array('role' => 'administrator', 'fields' => array('user_login', 'user_email')));
            echo "👥 Administrateurs: " . count($users) . "\n";
            
            foreach ($users as $user) {
                echo "   - {$user->user_login} ({$user->user_email})\n";
                
                // Détection utilisateurs suspects
                $suspicious_names = array('admin', 'administrator', 'root', 'test', '123');
                if (in_array(strtolower($user->user_login), $suspicious_names)) {
                    $this->warnings[] = "Nom utilisateur suspect: {$user->user_login}";
                }
            }
        }
        
        echo "\n";
    }
    
    /**
     * Test performance
     */
    private function checkPerformance() {
        echo "⚡ 6. PERFORMANCE\n";
        echo "----------------\n";
        
        // Test génération page
        $page_start = microtime(true);
        
        if (function_exists('get_option')) {
            // Simulation chargement page
            get_option('blogname');
            get_option('blogdescription');
            
            if (function_exists('wp_get_theme')) {
                wp_get_theme();
            }
        }
        
        $page_time = microtime(true) - $page_start;
        $page_time_ms = round($page_time * 1000, 2);
        
        echo "🕒 Temps génération: {$page_time_ms}ms\n";
        
        if ($page_time_ms > 500) {
            $this->warnings[] = "Génération page lente: {$page_time_ms}ms";
        } else {
            $this->success[] = "Performance acceptable: {$page_time_ms}ms";
        }
        
        // Vérification cache
        if (function_exists('wp_cache_get')) {
            echo "✅ Cache objet disponible\n";
        } else {
            echo "❌ Pas de cache objet\n";
            $this->warnings[] = "Cache objet non configuré";
        }
        
        // Taille uploads
        if (is_dir('wp-content/uploads')) {
            $uploads_size = $this->getDirSize('wp-content/uploads');
            $uploads_mb = round($uploads_size / 1024 / 1024, 2);
            echo "📁 Dossier uploads: {$uploads_mb} MB\n";
            
            if ($uploads_mb > 1000) {
                $this->warnings[] = "Dossier uploads volumineux: {$uploads_mb} MB";
            }
        }
        
        echo "\n";
    }
    
    /**
     * Génération rapport final
     */
    private function generateReport() {
        $total_time = microtime(true) - $this->start_time;
        
        echo "📋 RAPPORT DE DIAGNOSTIC\n";
        echo "========================\n\n";
        
        // Résumé
        echo "⏱️  Temps d'exécution: " . round($total_time, 2) . " secondes\n";
        echo "❌ Erreurs critiques: " . count($this->errors) . "\n";
        echo "⚠️  Avertissements: " . count($this->warnings) . "\n";
        echo "✅ Vérifications réussies: " . count($this->success) . "\n\n";
        
        // Erreurs critiques
        if (!empty($this->errors)) {
            echo "🚨 ERREURS CRITIQUES À CORRIGER:\n";
            foreach ($this->errors as $error) {
                echo "   ❌ $error\n";
            }
            echo "\n";
        }
        
        // Avertissements
        if (!empty($this->warnings)) {
            echo "⚠️  AMÉLIORATIONS RECOMMANDÉES:\n";
            foreach ($this->warnings as $warning) {
                echo "   ⚠️  $warning\n";
            }
            echo "\n";
        }
        
        // Score global
        $total_checks = count($this->errors) + count($this->warnings) + count($this->success);
        $success_rate = $total_checks > 0 ? round((count($this->success) / $total_checks) * 100, 1) : 0;
        
        echo "📊 SCORE GLOBAL: {$success_rate}%\n";
        
        if ($success_rate >= 90) {
            echo "🎉 Excellent ! Votre site WordPress est en très bon état.\n";
        } elseif ($success_rate >= 70) {
            echo "👍 Bien ! Quelques améliorations mineures recommandées.\n";
        } elseif ($success_rate >= 50) {
            echo "⚠️  Attention ! Plusieurs problèmes nécessitent votre attention.\n";
        } else {
            echo "🚨 Critique ! Intervention urgente recommandée.\n";
        }
        
        echo "\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "🔧 Script développé par Teddy - Expert WordPress\n";
        echo "📧 Besoin d'aide ? https://teddywp.com/depannage-wordpress/\n";
        echo "⚡ Intervention d'urgence sous 6h - Garanti !\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    }
    
    /**
     * Calcul taille dossier récursif
     */
    private function getDirSize($directory) {
        $size = 0;
        if (is_dir($directory)) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        }
        return $size;
    }
}

// Lancement du diagnostic
$diagnostic = new WordPressExpertDiagnostic();
$diagnostic->runCompleteDiagnostic();
?>
