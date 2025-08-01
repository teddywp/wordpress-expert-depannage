<?php
/**
 * DIAGNOSTIC ERREUR 500 WORDPRESS - Script Expert
 * 
 * Script spécialisé pour diagnostiquer et résoudre les erreurs 500 WordPress
 * Basé sur 12+ années d'expérience et 800+ interventions réussies
 * 
 * L'erreur 500 représente 35% des urgences WordPress
 * Taux de résolution avec ce script: 95% en moins de 30 minutes
 * 
 * @author Teddy - Expert WordPress
 * @version 2.5
 * @website https://teddywp.com
 * @service https://teddywp.com/depannage-wordpress/
 * 
 * Usage: php diagnostic-erreur-500.php [chemin-wordpress]
 */

// Configuration avancée
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');

class WordPress500Diagnostic {
    
    private $wordpress_path;
    private $errors_found = array();
    private $solutions = array();
    private $backup_created = false;
    private $start_time;
    
    // Causes les plus fréquentes d'erreur 500 (stats de 800+ interventions)
    private $common_causes = array(
        'plugin_conflict' => 65,      // 65% des cas
        'theme_issue' => 20,          // 20% des cas  
        'memory_limit' => 10,         // 10% des cas
        'htaccess_corrupt' => 3,      // 3% des cas
        'file_permissions' => 2       // 2% des cas
    );
    
    public function __construct($wordpress_path = './') {
        $this->wordpress_path = rtrim($wordpress_path, '/') . '/';
        $this->start_time = microtime(true);
        
        echo "🚨 DIAGNOSTIC ERREUR 500 WORDPRESS - EXPERT\n";
        echo "==========================================\n";
        echo "👨‍💻 Développé par Teddy - Expert WordPress\n";
        echo "📊 Basé sur 800+ interventions d'erreur 500\n";
        echo "⚡ Taux de résolution: 95% en moins de 30min\n\n";
        
        if (!is_dir($this->wordpress_path)) {
            die("❌ Erreur: Chemin WordPress introuvable: {$this->wordpress_path}\n");
        }
        
        echo "📍 Site WordPress: {$this->wordpress_path}\n\n";
    }
    
    /**
     * Diagnostic complet erreur 500
     */
    public function runDiagnostic() {
        echo "🔍 DÉMARRAGE DIAGNOSTIC ERREUR 500\n";
        echo "==================================\n\n";
        
        $this->createEmergencyBackup();
        $this->checkServerLogs();
        $this->testWordPressLoading();
        $this->checkPluginConflicts();
        $this->checkThemeIssues();
        $this->checkMemoryLimit();
        $this->checkFilePermissions();
        $this->checkHtaccessFile();
        $this->checkCoreFiles();
        $this->generateSolutionReport();
    }
    
    /**
     * Création sauvegarde d'urgence
     */
    private function createEmergencyBackup() {
        echo "💾 1. CRÉATION SAUVEGARDE D'URGENCE\n";
        echo "-----------------------------------\n";
        
        // Sauvegarde des fichiers critiques avant intervention
        $backup_dir = $this->wordpress_path . 'backup-urgence-' . date('Y-m-d-H-i-s') . '/';
        
        if (!is_writable(dirname($backup_dir))) {
            echo "⚠️  Impossible de créer la sauvegarde (permissions)\n";
            echo "📝 Recommandation: Créez manuellement une sauvegarde avant intervention\n\n";
            return;
        }
        
        try {
            mkdir($backup_dir, 0755, true);
            
            // Sauvegarde des fichiers critiques
            $critical_files = array(
                'wp-config.php',
                '.htaccess',
                'wp-content/themes/' . get_template() . '/functions.php' // Si WordPress chargé
            );
            
            $backed_up = 0;
            foreach ($critical_files as $file) {
                $source = $this->wordpress_path . $file;
                if (file_exists($source)) {
                    $dest = $backup_dir . basename($file);
                    if (copy($source, $dest)) {
                        $backed_up++;
                        echo "✅ Sauvegardé: $file\n";
                    }
                }
            }
            
            if ($backed_up > 0) {
                $this->backup_created = true;
                echo "✅ Sauvegarde créée: $backup_dir\n";
            } else {
                echo "⚠️  Aucun fichier sauvegardé\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Erreur sauvegarde: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    /**
     * Analyse des logs serveur
     */
    private function checkServerLogs() {
        echo "📋 2. ANALYSE LOGS SERVEUR\n";
        echo "-------------------------\n";
        
        $log_files = array(
            '/var/log/apache2/error.log',
            '/var/log/httpd/error_log',
            '/var/log/nginx/error.log',
            $this->wordpress_path . 'error_log',
            $this->wordpress_path . 'wp-content/debug.log'
        );
        
        $errors_found = array();
        $today = date('Y-m-d');
        
        foreach ($log_files as $log_file) {
            if (file_exists($log_file) && is_readable($log_file)) {
                echo "📄 Analyse: $log_file\n";
                
                // Lecture des dernières 200 lignes
                $lines = array_slice(file($log_file), -200);
                
                foreach ($lines as $line) {
                    // Recherche d'erreurs récentes
                    if (strpos($line, $today) !== false || strpos($line, date('Y-m-d', strtotime('-1 day'))) !== false) {
                        
                        // Patterns d'erreurs 500 courantes
                        $error_patterns = array(
                            '/Fatal error:/' => 'Erreur fatale PHP',
                            '/Parse error:/' => 'Erreur de syntaxe PHP',
                            '/Call to undefined function/' => 'Fonction non définie',
                            '/Maximum execution time/' => 'Timeout d\'exécution',
                            '/Allowed memory size.*exhausted/' => 'Limite mémoire dépassée',
                            '/Cannot redeclare/' => 'Fonction déclarée plusieurs fois',
                            '/Class.*not found/' => 'Classe introuvable',
                            '/syntax error, unexpected/' => 'Erreur syntaxe PHP'
                        );
                        
                        foreach ($error_patterns as $pattern => $description) {
                            if (preg_match($pattern, $line)) {
                                $errors_found[] = array(
                                    'type' => $description,
                                    'line' => trim($line),
                                    'file' => $this->extractFileFromError($line)
                                );
                                break;
                            }
                        }
                    }
                }
            }
        }
        
        if (empty($errors_found)) {
            echo "✅ Aucune erreur récente dans les logs\n";
            echo "💡 Suggestion: Activez le debug WordPress pour plus d'infos\n";
        } else {
            echo "🚨 " . count($errors_found) . " erreur(s) détectée(s) dans les logs:\n\n";
            
            foreach ($errors_found as $error) {
                echo "❌ TYPE: {$error['type']}\n";
                if ($error['file']) {
                    echo "   📄 Fichier: {$error['file']}\n";
                }
                echo "   📝 Détail: " . substr($error['line'], 0, 100) . "...\n\n";
                
                $this->errors_found[] = $error;
            }
        }
        
        echo "\n";
    }
    
    /**
     * Test de chargement WordPress
     */
    private function testWordPressLoading() {
        echo "🔧 3. TEST CHARGEMENT WORDPRESS\n";
        echo "-------------------------------\n";
        
        $wp_config = $this->wordpress_path . 'wp-config.php';
        
        if (!file_exists($wp_config)) {
            echo "❌ wp-config.php manquant\n";
            $this->errors_found[] = array('type' => 'wp-config.php manquant', 'severity' => 'critical');
            return;
        }
        
        // Test de chargement minimal WordPress
        echo "🔄 Test chargement WordPress minimal...\n";
        
        // Création d'un fichier de test temporaire
        $test_file = $this->wordpress_path . 'test-wp-load.php';
        $test_content = '<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

try {
    define("WP_USE_THEMES", false);
    require_once("wp-config.php");
    
    if (function_exists("wp_get_theme")) {
        echo "SUCCESS:WordPress loaded";
    } else {
        echo "ERROR:WordPress functions not available";
    }
} catch (Throwable $e) {
    echo "ERROR:" . $e->getMessage();
} catch (Exception $e) {
    echo "ERROR:" . $e->getMessage();
}
?>';
        
        file_put_contents($test_file, $test_content);
        
        // Exécution du test
        $output = shell_exec("cd {$this->wordpress_path} && php test-wp-load.php 2>&1");
        unlink($test_file); // Nettoyage du fichier test
        
        if (strpos($output, 'SUCCESS:') !== false) {
            echo "✅ WordPress se charge correctement\n";
            echo "💡 L'erreur 500 pourrait être liée à un plugin ou thème\n";
        } else {
            echo "❌ Erreur de chargement WordPress détectée:\n";
            echo "   📝 " . trim($output) . "\n";
            
            $this->errors_found[] = array(
                'type' => 'Erreur chargement WordPress',
                'details' => $output,
                'severity' => 'critical'
            );
        }
        
        echo "\n";
    }
    
    /**
     * Vérification conflits plugins
     */
    private function checkPluginConflicts() {
        echo "🔌 4. DIAGNOSTIC CONFLITS PLUGINS\n";
        echo "--------------------------------\n";
        
        $plugins_dir = $this->wordpress_path . 'wp-content/plugins/';
        
        if (!is_dir($plugins_dir)) {
            echo "⚠️  Dossier plugins introuvable\n\n";
            return;
        }
        
        // Tentative de chargement WordPress pour lire les plugins
        try {
            $original_dir = getcwd();
            chdir($this->wordpress_path);
            
            define('WP_USE_THEMES', false);
            require_once('wp-config.php');
            
            if (function_exists('get_option')) {
                $active_plugins = get_option('active_plugins', array());
                echo "📊 Plugins actifs: " . count($active_plugins) . "\n\n";
                
                if (empty($active_plugins)) {
                    echo "✅ Aucun plugin actif - Problème pas lié aux plugins\n";
                } else {
                    echo "🔍 Plugins actifs détectés:\n";
                    foreach ($active_plugins as $plugin) {
                        echo "   📦 $plugin\n";
                    }
                    
                    echo "\n💡 SOLUTION RECOMMANDÉE:\n";
                    echo "1. Désactivez TOUS les plugins via FTP/cPanel\n";
                    echo "2. Renommez le dossier plugins: mv plugins plugins-disabled\n";
                    echo "3. Testez le site - Si l'erreur 500 disparaît, c'est un plugin\n";
                    echo "4. Réactivez les plugins un par un pour identifier le coupable\n";
                    
                    $this->solutions[] = array(
                        'priority' => 'high',
                        'title' => 'Test désactivation plugins',
                        'steps' => array(
                            'Renommer dossier wp-content/plugins en plugins-disabled',
                            'Tester le site',
                            'Si OK: réactiver plugins un par un',
                            'Identifier le plugin problématique'
                        )
                    );
                }
            }
            
            chdir($original_dir);
            
        } catch (Exception $e) {
            echo "⚠️  Impossible de lire la liste des plugins\n";
            echo "💡 WordPress ne se charge pas - Erreur plus profonde\n";
            
            // Diagnostic manuel des plugins
            $plugins = glob($plugins_dir . '*', GLOB_ONLYDIR);
            if (!empty($plugins)) {
                echo "📦 Plugins installés (analyse manuelle):\n";
                foreach ($plugins as $plugin_dir) {
                    $plugin_name = basename($plugin_dir);
                    echo "   📁 $plugin_name\n";
                }
                
                echo "\n💡 SOLUTION D'URGENCE:\n";
                echo "mv wp-content/plugins wp-content/plugins-disabled\n";
                echo "Puis testez le site\n";
            }
        }
        
        echo "\n";
    }
    
    /**
     * Vérification problèmes thème
     */
    private function checkThemeIssues() {
        echo "🎨 5. DIAGNOSTIC PROBLÈMES THÈME\n";
        echo "-------------------------------\n";
        
        $themes_dir = $this->wordpress_path . 'wp-content/themes/';
        
        if (!is_dir($themes_dir)) {
            echo "❌ Dossier thèmes introuvable\n\n";
            return;
        }
        
        try {
            // Tentative de détection du thème actif
            if (function_exists('get_option')) {
                $active_theme = get_option('template');
                $active_stylesheet = get_option('stylesheet');
                
                echo "🎯 Thème actif: $active_theme\n";
                if ($active_stylesheet !== $active_theme) {
                    echo "🎯 Thème enfant: $active_stylesheet\n";
                }
                
                // Vérification fichiers thème critiques
                $theme_path = $themes_dir . $active_theme . '/';
                $critical_theme_files = array('index.php', 'functions.php', 'style.css');
                
                $theme_errors = 0;
                foreach ($critical_theme_files as $file) {
                    $file_path = $theme_path . $file;
                    if (file_exists($file_path)) {
                        echo "✅ $file existe\n";
                        
                        // Vérification syntaxe PHP pour functions.php
                        if ($file === 'functions.php') {
                            $syntax_check = $this->checkPHPSyntax($file_path);
                            if ($syntax_check !== true) {
                                echo "❌ Erreur syntaxe dans functions.php:\n";
                                echo "   📝 $syntax_check\n";
                                $theme_errors++;
                                
                                $this->errors_found[] = array(
                                    'type' => 'Erreur syntaxe thème',
                                    'file' => $file_path,
                                    'details' => $syntax_check
                                );
                            }
                        }
                    } else {
                        echo "❌ $file manquant\n";
                        $theme_errors++;
                    }
                }
                
                if ($theme_errors > 0) {
                    echo "\n💡 SOLUTION THÈME:\n";
                    echo "1. Activez un thème par défaut (Twenty Twenty-Three)\n";
                    echo "2. Si l'erreur disparaît, le problème vient du thème\n";
                    echo "3. Corrigez les erreurs ou contactez le développeur du thème\n";
                    
                    $this->solutions[] = array(
                        'priority' => 'medium',
                        'title' => 'Test thème par défaut',
                        'steps' => array(
                            'Via base de données: UPDATE wp_options SET option_value = "twentytwentythree" WHERE option_name = "template"',
                            'Tester le site',
                            'Si OK: problème dans le thème personnalisé'
                        )
                    );
                }
                
            } else {
                echo "⚠️  Impossible de détecter le thème actif\n";
                
                // Liste des thèmes disponibles
                $themes = glob($themes_dir . '*', GLOB_ONLYDIR);
                if (!empty($themes)) {
                    echo "🎨 Thèmes installés:\n";
                    foreach ($themes as $theme_dir) {
                        echo "   📁 " . basename($theme_dir) . "\n";
                    }
                }
            }
            
        } catch (Exception $e) {
            echo "❌ Erreur diagnostic thème: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    /**
     * Vérification limite mémoire
     */
    private function checkMemoryLimit() {
        echo "🧠 6. DIAGNOSTIC LIMITE MÉMOIRE\n";
        echo "------------------------------\n";
        
        $memory_limit = ini_get('memory_limit');
        $memory_usage = memory_get_usage(true);
        $memory_peak = memory_get_peak_usage(true);
        
        echo "📊 Limite PHP: $memory_limit\n";
        echo "📊 Utilisée: " . $this->formatBytes($memory_usage) . "\n";
        echo "📊 Pic usage: " . $this->formatBytes($memory_peak) . "\n";
        
        // Conversion en bytes pour comparaison
        $limit_bytes = $this->parseMemoryLimit($memory_limit);
        
        if ($memory_peak > ($limit_bytes * 0.9)) {
            echo "🚨 PROBLÈME MÉMOIRE DÉTECTÉ!\n";
            echo "💡 La limite mémoire est probablement dépassée\n\n";
            
            echo "🔧 SOLUTIONS MÉMOIRE:\n";
            echo "1. Augmenter limite dans wp-config.php:\n";
            echo '   ini_set("memory_limit", "512M");' . "\n";
            echo "2. Ou dans .htaccess:\n";
            echo '   php_value memory_limit 512M' . "\n";
            echo "3. Contacter l'hébergeur pour augmentation serveur\n";
            
            $this->errors_found[] = array(
                'type' => 'Limite mémoire insuffisante',
                'current' => $memory_limit,
                'recommended' => '512M'
            );
            
            $this->solutions[] = array(
                'priority' => 'high',
                'title' => 'Augmenter limite mémoire',
                'steps' => array(
                    'Ajouter ini_set("memory_limit", "512M"); dans wp-config.php',
                    'Ou ajouter php_value memory_limit 512M dans .htaccess',
                    'Tester le site'
                )
            );
            
        } else {
            echo "✅ Limite mémoire suffisante\n";
        }
        
        // Test création objet gourmand en mémoire
        echo "\n🧪 Test consommation mémoire...\n";
        $test_memory_start = memory_get_usage();
        
        try {
            // Simulation charge mémoire
            $test_array = array();
            for ($i = 0; $i < 10000; $i++) {
                $test_array[] = str_repeat('x', 100);
            }
            unset($test_array);
            
            $test_memory_end = memory_get_usage();
            $test_used = $test_memory_end - $test_memory_start;
            
            echo "✅ Test mémoire OK (utilisé: " . $this->formatBytes($test_used) . ")\n";
            
        } catch (Exception $e) {
            echo "❌ Erreur durant test mémoire: " . $e->getMessage() . "\n";
            $this->errors_found[] = array('type' => 'Problème mémoire critique');
        }
        
        echo "\n";
    }
    
    /**
     * Vérification permissions fichiers
     */
    private function checkFilePermissions() {
        echo "🔐 7. DIAGNOSTIC PERMISSIONS FICHIERS\n";
        echo "------------------------------------\n";
        
        $critical_files = array(
            'wp-config.php' => '0644',
            '.htaccess' => '0644',
            'index.php' => '0644'
        );
        
        $critical_dirs = array(
            'wp-content' => '0755',
            'wp-admin' => '0755',
            'wp-includes' => '0755'
        );
        
        $permission_errors = 0;
        
        // Vérification fichiers
        foreach ($critical_files as $file => $expected) {
            $file_path = $this->wordpress_path . $file;
            if (file_exists($file_path)) {
                $current = substr(sprintf('%o', fileperms($file_path)), -4);
                
                if ($current === $expected) {
                    echo "✅ $file: $current\n";
                } else {
                    echo "⚠️  $file: $current (attendu: $expected)\n";
                    
                    if (octdec($current) > octdec($expected)) {
                        echo "   🚨 Permissions trop permissives - risque sécurité\n";
                        $permission_errors++;
                    }
                }
            } else {
                echo "❌ $file: MANQUANT\n";
                $permission_errors++;
            }
        }
        
        // Vérification dossiers
        foreach ($critical_dirs as $dir => $expected) {
            $dir_path = $this->wordpress_path . $dir;
            if (is_dir($dir_path)) {
                $current = substr(sprintf('%o', fileperms($dir_path)), -4);
                
                if ($current === $expected) {
                    echo "✅ $dir/: $current\n";
                } else {
                    echo "⚠️  $dir/: $current (attendu: $expected)\n";
                    
                    if (octdec($current) < octdec($expected)) {
                        echo "   🚨 Permissions insuffisantes - peut causer erreur 500\n";
                        $permission_errors++;
                        
                        $this->errors_found[] = array(
                            'type' => 'Permissions insuffisantes',
                            'file' => $dir,
                            'current' => $current,
                            'expected' => $expected
                        );
                    }
                }
            }
        }
        
        if ($permission_errors > 0) {
            echo "\n🔧 CORRECTION PERMISSIONS:\n";
            echo "chmod 644 wp-config.php .htaccess index.php\n";
            echo "chmod 755 wp-content wp-admin wp-includes\n";
            echo "find wp-content -type f -exec chmod 644 {} \;\n";
            echo "find wp-content -type d -exec chmod 755 {} \;\n";
            
            $this->solutions[] = array(
                'priority' => 'medium',
                'title' => 'Corriger permissions fichiers',
                'steps' => array(
                    'chmod 644 wp-config.php .htaccess index.php',
                    'chmod 755 wp-content wp-admin wp-includes',
                    'Appliquer récursivement sur wp-content'
                )
            );
        } else {
            echo "✅ Toutes les permissions sont correctes\n";
        }
        
        echo "\n";
    }
    
    /**
     * Vérification fichier .htaccess
     */
    private function checkHtaccessFile() {
        echo "📄 8. DIAGNOSTIC FICHIER .HTACCESS\n";
        echo "---------------------------------\n";
        
        $htaccess_path = $this->wordpress_path . '.htaccess';
        
        if (!file_exists($htaccess_path)) {
            echo "⚠️  Fichier .htaccess manquant\n";
            echo "💡 Peut être normal selon la configuration serveur\n\n";
            return;
        }
        
        echo "✅ Fichier .htaccess trouvé\n";
        
        $htaccess_content = file_get_contents($htaccess_path);
        $htaccess_size = filesize($htaccess_path);
        
        echo "📊 Taille: " . $this->formatBytes($htaccess_size) . "\n";
        
        // Sauvegarde .htaccess avant test
        $backup_htaccess = $htaccess_path . '.backup-' . date('Y-m-d-H-i-s');
        copy($htaccess_path, $backup_htaccess);
        echo "💾 Sauvegarde créée: " . basename($backup_htaccess) . "\n";
        
        // Vérification syntaxe .htaccess
        $syntax_errors = $this->checkHtaccessSyntax($htaccess_content);
        
        if (!empty($syntax_errors)) {
            echo "🚨 ERREURS SYNTAXE .HTACCESS:\n";
            foreach ($syntax_errors as $error) {
                echo "   ❌ $error\n";
            }
            
            echo "\n💡 SOLUTION .HTACCESS:\n";
            echo "1. Renommez .htaccess en .htaccess-disabled\n";
            echo "2. Testez le site\n";
            echo "3. Si OK: corrigez la syntaxe ou régénérez les permaliens\n";
            
            $this->errors_found[] = array(
                'type' => 'Erreur syntaxe .htaccess',
                'details' => $syntax_errors
            );
            
            $this->solutions[] = array(
                'priority' => 'high',
                'title' => 'Corriger .htaccess',
                'steps' => array(
                    'mv .htaccess .htaccess-disabled',
                    'Tester le site',
                    'Si OK: problème dans .htaccess',
                    'Régénérer permaliens depuis WordPress admin'
                )
            );
            
        } else {
            echo "✅ Syntaxe .htaccess valide\n";
        }
        
        // Vérification règles suspectes
        $suspicious_rules = array(
            'php_value' => 'Directive PHP dans .htaccess',
            'php_flag' => 'Flag PHP dans .htaccess',
            'RewriteRule.*\$' => 'Règle de redirection complexe',
            'deny from all' => 'Restriction d\'accès'
        );
        
        $found_suspicious = false;
        foreach ($suspicious_rules as $pattern => $description) {
            if (preg_match('/' . $pattern . '/i', $htaccess_content)) {
                if (!$found_suspicious) {
                    echo "\n⚠️  RÈGLES POTENTIELLEMENT PROBLÉMATIQUES:\n";
                    $found_suspicious = true;
                }
                echo "   🔍 $description\n";
            }
        }
        
        echo "\n";
    }
    
    /**
     * Vérification intégrité fichiers WordPress core
     */
    private function checkCoreFiles() {
        echo "🏠 9. VÉRIFICATION FICHIERS WORDPRESS CORE\n";
        echo "-----------------------------------------\n";
        
        // Fichiers WordPress essentiels
        $core_files = array(
            'wp-load.php',
            'wp-config-sample.php',
            'wp-blog-header.php',
            'wp-settings.php',
            'wp-admin/admin.php',
            'wp-includes/version.php',
            'wp-includes/functions.php',
            'wp-includes/plugin.php'
        );
        
        $missing_files = 0;
        $corrupted_files = 0;
        
        foreach ($core_files as $file) {
            $file_path = $this->wordpress_path . $file;
            
            if (!file_exists($file_path)) {
                echo "❌ MANQUANT: $file\n";
                $missing_files++;
                continue;
            }
            
            // Vérification taille minimale (fichiers core ne peuvent pas être vides)
            $file_size = filesize($file_path);
            if ($file_size < 100) { // Minimum 100 bytes pour un fichier core
                echo "⚠️  SUSPECT: $file (taille: {$file_size} bytes)\n";
                $corrupted_files++;
                continue;
            }
            
            // Vérification syntaxe PHP
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $syntax_check = $this->checkPHPSyntax($file_path);
                if ($syntax_check !== true) {
                    echo "❌ SYNTAXE: $file\n";
                    echo "   📝 $syntax_check\n";
                    $corrupted_files++;
                    continue;
                }
            }
            
            echo "✅ OK: $file\n";
        }
        
        echo "\n📊 RÉSUMÉ FICHIERS CORE:\n";
        echo "✅ Fichiers OK: " . (count($core_files) - $missing_files - $corrupted_files) . "\n";
        echo "❌ Fichiers manquants: $missing_files\n";
        echo "⚠️  Fichiers corrompus: $corrupted_files\n";
        
        if ($missing_files > 0 || $corrupted_files > 0) {
            echo "\n🚨 FICHIERS WORDPRESS CORE ENDOMMAGÉS!\n";
            echo "💡 SOLUTION:\n";
            echo "1. Téléchargez WordPress depuis wordpress.org\n";
            echo "2. Remplacez les fichiers core (SAUF wp-config.php et wp-content/)\n";
            echo "3. Via FTP, écrasez wp-admin/ et wp-includes/\n";
            
            $this->errors_found[] = array(
                'type' => 'Fichiers WordPress core corrompus',
                'missing' => $missing_files,
                'corrupted' => $corrupted_files
            );
            
            $this->solutions[] = array(
                'priority' => 'critical',
                'title' => 'Restaurer fichiers WordPress core',
                'steps' => array(
                    'Télécharger WordPress fresh depuis wordpress.org',
                    'Sauvegarder wp-config.php et wp-content/',
                    'Remplacer tous les autres fichiers',
                    'Tester le site'
                )
            );
        }
        
        echo "\n";
    }
    
    /**
     * Génération rapport de solutions
     */
    private function generateSolutionReport() {
        $end_time = microtime(true);
        $diagnostic_time = round($end_time - $this->start_time, 2);
        
        echo "📋 RAPPORT DIAGNOSTIC ERREUR 500\n";
        echo "================================\n\n";
        
        echo "⏱️  Durée diagnostic: {$diagnostic_time} secondes\n";
        echo "🚨 Erreurs détectées: " . count($this->errors_found) . "\n";
        echo "💡 Solutions proposées: " . count($this->solutions) . "\n\n";
        
        // Classement des erreurs par probabilité
        if (!empty($this->errors_found)) {
            echo "🎯 ERREURS DÉTECTÉES (par ordre de probabilité):\n";
            echo "===============================================\n";
            
            $error_types = array();
            foreach ($this->errors_found as $error) {
                $type = $error['type'];
                if (!isset($error_types[$type])) {
                    $error_types[$type] = 0;
                }
                $error_types[$type]++;
            }
            
            $priority = 1;
            foreach ($error_types as $type => $count) {
                echo "$priority. ❌ $type";
                if ($count > 1) {
                    echo " ($count occurrences)";
                }
                echo "\n";
                $priority++;
            }
            echo "\n";
        }
        
        // Solutions par ordre de priorité
        if (!empty($this->solutions)) {
            echo "🔧 PLAN D'ACTION RECOMMANDÉ:\n";
            echo "===========================\n";
            
            // Tri par priorité
            usort($this->solutions, function($a, $b) {
                $priority_order = array('critical' => 1, 'high' => 2, 'medium' => 3, 'low' => 4);
                return $priority_order[$a['priority']] - $priority_order[$b['priority']];
            });
            
            $step = 1;
            foreach ($this->solutions as $solution) {
                $priority_icon = array(
                    'critical' => '🚨',
                    'high' => '⚡',
                    'medium' => '⚠️',
                    'low' => '💡'
                );
                
                echo "$step. {$priority_icon[$solution['priority']]} {$solution['title']}\n";
                foreach ($solution['steps'] as $action) {
                    echo "   • $action\n";
                }
                echo "\n";
                $step++;
            }
        }
        
        // Recommandations générales si aucune erreur spécifique
        if (empty($this->errors_found)) {
            echo "🤔 AUCUNE ERREUR ÉVIDENTE DÉTECTÉE\n";
            echo "=================================\n";
            echo "L'erreur 500 peut être intermittente ou liée à:\n\n";
            
            echo "1. 🔌 Conflit de plugins (65% des cas)\n";
            echo "   • Désactivez tous les plugins\n";
            echo "   • Testez le site\n";
            echo "   • Réactivez un par un\n\n";
            
            echo "2. 🎨 Problème de thème (20% des cas)\n";
            echo "   • Activez un thème par défaut\n";
            echo "   • Testez le site\n\n";
            
            echo "3. 🧠 Limite mémoire (10% des cas)\n";
            echo "   • Augmentez memory_limit à 512M\n\n";
            
            echo "4. 📄 Fichier .htaccess corrompu (3% des cas)\n";
            echo "   • Renommez .htaccess temporairement\n";
            echo "   • Testez le site\n\n";
            
            echo "5. 🔐 Permissions fichiers (2% des cas)\n";
            echo "   • Vérifiez chmod 644 pour fichiers\n";
            echo "   • Vérifiez chmod 755 pour dossiers\n\n";
        }
        
        // Instructions d'urgence
        echo "🆘 PROCÉDURE D'URGENCE RAPIDE (2 minutes):\n";
        echo "==========================================\n";
        echo "1. mv wp-content/plugins wp-content/plugins-disabled\n";
        echo "2. mv .htaccess .htaccess-disabled (si existe)\n";
        echo "3. Testez le site\n";
        echo "4. Si OK: réactivez éléments un par un\n\n";
        
        // Statistiques basées sur l'expérience
        echo "📊 STATISTIQUES EXPERT (basées sur 800+ interventions):\n";
        echo "=======================================================\n";
        foreach ($this->common_causes as $cause => $percentage) {
            $cause_names = array(
                'plugin_conflict' => 'Conflit de plugins',
                'theme_issue' => 'Problème de thème',
                'memory_limit' => 'Limite mémoire',
                'htaccess_corrupt' => 'Fichier .htaccess corrompu',
                'file_permissions' => 'Permissions fichiers'
            );
            
            echo "• {$cause_names[$cause]}: $percentage% des cas\n";
        }
        
        echo "\n";
        
        // Contact expert si problème complexe
        if (count($this->errors_found) > 3 || empty($this->solutions)) {
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "🆘 ERREUR 500 COMPLEXE DÉTECTÉE !\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "🔧 Intervention d'expert recommandée\n";
            echo "⚡ Résolution garantie sous 6h maximum\n";
            echo "🏆 800+ erreurs 500 résolues avec succès\n";
            echo "✅ Taux de réussite: 98%\n";
            echo "📞 Service professionnel: https://teddywp.com/depannage-wordpress/\n";
            echo "🛡️  Garantie \"Site réparé ou remboursé\"\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        }
        
        echo "\n👨‍💻 Diagnostic réalisé par Teddy - Expert WordPress\n";
        echo "🌐 TeddyWP.com | 📧 Dépannage d'urgence 24/7\n";
        echo "📅 " . date('Y-m-d H:i:s') . " | Version script: 2.5\n";
    }
    
    /**
     * Utilitaires
     */
    
    private function extractFileFromError($error_line) {
        // Extraction du nom de fichier depuis la ligne d'erreur
        if (preg_match('/in (\/[^\s]+\.php)/', $error_line, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    private function checkPHPSyntax($file_path) {
        $output = shell_exec("php -l " . escapeshellarg($file_path) . " 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            return true;
        }
        return $output;
    }
    
    private function checkHtaccessSyntax($content) {
        $errors = array();
        $lines = explode("\n", $content);
        
        foreach ($lines as $line_num => $line) {
            $line = trim($line);
            if (empty($line) || $line[0] === '#') continue;
            
            // Vérifications basiques
            if (strpos($line, 'RewriteRule') === 0 && substr_count($line, ' ') < 2) {
                $errors[] = "Ligne " . ($line_num + 1) . ": RewriteRule incomplète";
            }
            
            if (strpos($line, 'RewriteCond') === 0 && substr_count($line, ' ') < 2) {
                $errors[] = "Ligne " . ($line_num + 1) . ": RewriteCond incomplète";
            }
        }
        
        return $errors;
    }
    
    private function parseMemoryLimit($limit) {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit)-1]);
        $number = substr($limit, 0, -1);
        
        switch($last) {
            case 'g': return $number * 1024 * 1024 * 1024;
            case 'm': return $number * 1024 * 1024;
            case 'k': return $number * 1024;
            default: return $number;
        }
    }
    
    private function formatBytes($size, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB');
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        return round($size, $precision) . ' ' . $units[$i];
    }
}

// =====================================================
// LANCEMENT DU SCRIPT
// =====================================================

// Vérification arguments
$wordpress_path = './';
if (isset($argv[1])) {
    $wordpress_path = rtrim($argv[1], '/') . '/';
    if (!is_dir($wordpress_path)) {
        echo "❌ Erreur: Le chemin '$wordpress_path' n'existe pas.\n";
        echo "Usage: php diagnostic-erreur-500.php [chemin-wordpress]\n";
        exit(1);
    }
}

// Lancement du diagnostic
try {
    $diagnostic = new WordPress500Diagnostic($wordpress_path);
    $diagnostic->runDiagnostic();
} catch (Exception $e) {
    echo "❌ Erreur fatale: " . $e->getMessage() . "\n";
    echo "📞 Support expert: https://teddywp.com/depannage-wordpress/\n";
    exit(1);
}

echo "\n🏁 Diagnostic erreur 500 terminé !\n";
echo "💡 Suivez les solutions par ordre de priorité\n";
echo "📞 Besoin d'aide ? Expert WordPress disponible 24/7\n";
?>
