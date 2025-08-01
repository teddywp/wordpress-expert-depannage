<?php
/**
 * AUDIT SÉCURITÉ WORDPRESS - Script Expert
 * 
 * Script d'audit de sécurité complet pour WordPress
 * Détecte malwares, vulnérabilités et failles de sécurité
 * 
 * Développé par Teddy - Expert WordPress
 * 12+ années d'expérience | 120+ sites piratés nettoyés
 * 
 * @author Teddy - Expert WordPress
 * @version 3.0
 * @website https://teddywp.com
 * @service https://teddywp.com/depannage-wordpress/
 * 
 * Usage: php audit-securite.php [chemin-wordpress]
 */

// Configuration du script
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 600); // 10 minutes max
ini_set('memory_limit', '512M');

class WordPressSecurityAuditor {
    
    private $wordpress_path;
    private $threats_found = array();
    private $vulnerabilities = array();
    private $warnings = array();
    private $scan_stats = array();
    private $start_time;
    
    // Patterns malware les plus courants (base de 12+ années d'expérience)
    private $malware_patterns = array(
        'base64_decode\s*\(',
        'eval\s*\(',
        'exec\s*\(',
        'system\s*\(',
        'shell_exec\s*\(',
        'passthru\s*\(',
        'file_get_contents\s*\(\s*["\']https?://',
        'curl_exec\s*\(',
        'preg_replace\s*\(\s*["\'].*\/e',
        'assert\s*\(',
        'create_function\s*\(',
        'ReflectionFunction',
        '\$_(?:GET|POST|REQUEST|COOKIE)\[.*\]\s*\(',
        'wp_remote_get\s*\(\s*\$',
        'add_action\s*\(\s*["\']wp_footer["\'].*base64',
        'wp_enqueue_script.*base64',
        '(?:include|require)(?:_once)?\s*\(\s*["\']https?://',
        '\\\x[0-9a-f]{2}', // Caractères hexadécimaux encodés
        'chr\s*\(\s*\d+\s*\)\s*\.', // Concaténation chr()
        'gzinflate\s*\(\s*base64_decode',
        'str_rot13\s*\(',
        '\$GLOBALS\[.*\]\s*\.\s*\$GLOBALS'
    );
    
    // Fichiers suspects communs
    private $suspicious_files = array(
        'wp-config-backup.php',
        'wp-config-sample.php.bak',
        'configuration.php',
        'config.php.bak',
        'backup.php',
        'dump.php',
        'shell.php',
        'c99.php',
        'r57.php',
        'webshell.php',
        'bypass.php',
        'index.php.bak',
        '.htaccess.bak'
    );
    
    public function __construct($wordpress_path = './') {
        $this->wordpress_path = rtrim($wordpress_path, '/') . '/';
        $this->start_time = microtime(true);
        
        echo "🛡️  AUDIT SÉCURITÉ WORDPRESS EXPERT - v3.0\n";
        echo "==========================================\n";
        echo "👨‍💻 Développé par Teddy - Expert WordPress\n";
        echo "📊 Base de données: 120+ sites piratés analysés\n";
        echo "⚡ Patterns malware: " . count($this->malware_patterns) . " signatures\n\n";
        
        $this->scan_stats = array(
            'files_scanned' => 0,
            'directories_scanned' => 0,
            'malware_detected' => 0,
            'suspicious_files' => 0,
            'vulnerabilities' => 0
        );
    }
    
    /**
     * Lancement de l'audit complet
     */
    public function runCompleteAudit() {
        $this->checkWordPressInstallation();
        $this->scanMaliciousFiles();
        $this->checkSuspiciousUsers();
        $this->analyzeFilePermissions();
        $this->checkConfigurationSecurity();
        $this->analyzeDatabaseSecurity();
        $this->checkSuspiciousFiles();
        $this->checkPluginVulnerabilities();
        $this->generateSecurityReport();
    }
    
    /**
     * Vérification installation WordPress
     */
    private function checkWordPressInstallation() {
        echo "🔍 1. VÉRIFICATION INSTALLATION WORDPRESS\n";
        echo "----------------------------------------\n";
        
        $wp_config = $this->wordpress_path . 'wp-config.php';
        
        if (!file_exists($wp_config)) {
            $this->threats_found[] = "wp-config.php manquant - Installation WordPress compromise";
            echo "❌ wp-config.php MANQUANT\n";
            return;
        }
        
        echo "✅ wp-config.php trouvé\n";
        
        // Chargement WordPress pour analyses avancées
        if (file_exists($wp_config)) {
            try {
                $original_path = getcwd();
                chdir($this->wordpress_path);
                
                define('WP_USE_THEMES', false);
                require_once('wp-config.php');
                
                if (function_exists('get_bloginfo')) {
                    $wp_version = get_bloginfo('version');
                    echo "✅ WordPress Version: $wp_version\n";
                    
                    // Vérification version obsolète (risque sécurité)
                    $latest_major = '6.4'; // À mettre à jour régulièrement
                    if (version_compare($wp_version, $latest_major, '<')) {
                        $this->vulnerabilities[] = "Version WordPress obsolète ($wp_version) - Vulnérabilités connues";
                        echo "⚠️  Version obsolète détectée\n";
                    }
                } else {
                    $this->warnings[] = "WordPress Core endommagé";
                }
                
                chdir($original_path);
            } catch (Exception $e) {
                $this->warnings[] = "Erreur chargement WordPress: " . $e->getMessage();
                echo "⚠️  Erreur chargement WordPress\n";
            }
        }
        
        echo "\n";
    }
    
    /**
     * Scan des fichiers malveillants
     */
    private function scanMaliciousFiles() {
        echo "🦠 2. DÉTECTION MALWARES ET CODES MALVEILLANTS\n";
        echo "----------------------------------------------\n";
        
        $scan_directories = array(
            $this->wordpress_path,
            $this->wordpress_path . 'wp-content/themes/',
            $this->wordpress_path . 'wp-content/plugins/',
            $this->wordpress_path . 'wp-content/uploads/'
        );
        
        $infected_files = array();
        $total_files = 0;
        
        foreach ($scan_directories as $directory) {
            if (!is_dir($directory)) continue;
            
            echo "📁 Scan: " . str_replace($this->wordpress_path, '', $directory) . "\n";
            
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $total_files++;
                    $this->scan_stats['files_scanned']++;
                    
                    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    
                    // Scan des fichiers PHP, JS et HTML
                    if (in_array($extension, array('php', 'js', 'html', 'htm', 'txt'))) {
                        $malware_found = $this->scanFileForMalware($file->getPathname());
                        if (!empty($malware_found)) {
                            $infected_files[] = array(
                                'file' => $file->getPathname(),
                                'threats' => $malware_found,
                                'size' => $file->getSize(),
                                'modified' => date('Y-m-d H:i:s', $file->getMTime())
                            );
                            $this->scan_stats['malware_detected']++;
                        }
                    }
                    
                    // Affichage progression
                    if ($total_files % 100 == 0) {
                        echo "   📊 Fichiers scannés: $total_files\r";
                    }
                }
            }
        }
        
        echo "   📊 Total fichiers scannés: $total_files\n";
        
        if (empty($infected_files)) {
            echo "✅ Aucun malware détecté\n";
        } else {
            echo "🚨 " . count($infected_files) . " fichier(s) infecté(s) détecté(s):\n\n";
            
            foreach ($infected_files as $file) {
                echo "❌ FICHIER INFECTÉ:\n";
                echo "   📄 Fichier: " . str_replace($this->wordpress_path, '', $file['file']) . "\n";
                echo "   📊 Taille: " . $this->formatBytes($file['size']) . "\n";
                echo "   📅 Modifié: " . $file['modified'] . "\n";
                echo "   🦠 Menaces: " . implode(', ', $file['threats']) . "\n\n";
                
                $this->threats_found[] = "Fichier infecté: " . $file['file'];
            }
        }
        
        echo "\n";
    }
    
    /**
     * Scan d'un fichier pour détecter les malwares
     */
    private function scanFileForMalware($filepath) {
        $threats = array();
        
        try {
            $content = file_get_contents($filepath);
            if ($content === false) return $threats;
            
            // Scan avec tous les patterns malware
            foreach ($this->malware_patterns as $pattern) {
                if (preg_match('/' . $pattern . '/i', $content)) {
                    $threats[] = $this->getMalwareTypeName($pattern);
                }
            }
            
            // Détection de code obfusqué
            if ($this->detectObfuscatedCode($content)) {
                $threats[] = "Code obfusqué suspect";
            }
            
            // Détection d'injections SQL
            if ($this->detectSQLInjection($content)) {
                $threats[] = "Injection SQL potentielle";
            }
            
        } catch (Exception $e) {
            // Fichier inaccessible ou corrompu
        }
        
        return array_unique($threats);
    }
    
    /**
     * Détection de code obfusqué
     */
    private function detectObfuscatedCode($content) {
        // Détection de patterns d'obfuscation courants
        $obfuscation_patterns = array(
            '/\$[a-zA-Z_][a-zA-Z0-9_]*\s*=\s*["\'][a-zA-Z0-9+\/]{50,}["\']/', // Base64 long
            '/eval\s*\(\s*gzinflate\s*\(\s*base64_decode/', // Décompression + évaluation
            '/\$[a-zA-Z_][a-zA-Z0-9_]*\[["\'][0-9]+["\']\]\s*\./', // Concaténation par index
            '/chr\s*\(\s*\d+\s*\)\s*\.\s*chr\s*\(\s*\d+\s*\)/', // Multiples chr()
            '/[a-zA-Z0-9+\/]{100,}/', // Chaînes base64 très longues
        );
        
        foreach ($obfuscation_patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        
        // Détection heuristique : trop de caractères spéciaux
        $special_chars = preg_match_all('/[\\\\$(){}\[\]|&^%#@!~`]/', $content);
        $total_chars = strlen($content);
        
        if ($total_chars > 0 && ($special_chars / $total_chars) > 0.15) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Détection d'injection SQL
     */
    private function detectSQLInjection($content) {
        $sql_patterns = array(
            '/UNION\s+SELECT/i',
            '/DROP\s+TABLE/i',
            '/INSERT\s+INTO.*VALUES/i',
            '/UPDATE.*SET.*WHERE/i',
            '/DELETE\s+FROM/i',
            '/\'\s*OR\s*\'1\'\s*=\s*\'1/i',
            '/\'\s*OR\s*1\s*=\s*1/i'
        );
        
        foreach ($sql_patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Obtenir le nom de la menace depuis le pattern
     */
    private function getMalwareTypeName($pattern) {
        $threat_names = array(
            'base64_decode' => 'Code encodé Base64',
            'eval\s*\(' => 'Évaluation de code dynamique',
            'exec\s*\(' => 'Exécution système',
            'system\s*\(' => 'Commande système',
            'shell_exec' => 'Shell malveillant',
            'file_get_contents.*http' => 'Téléchargement distant',
            'curl_exec' => 'Requête CURL suspecte',
            'preg_replace.*\/e' => 'Injection regex',
            'assert\s*\(' => 'Assertion malveillante',
            'create_function' => 'Fonction dynamique'
        );
        
        foreach ($threat_names as $key => $name) {
            if (strpos($pattern, $key) !== false) {
                return $name;
            }
        }
        
        return 'Malware générique';
    }
    
    /**
     * Vérification utilisateurs suspects
     */
    private function checkSuspiciousUsers() {
        echo "👥 3. ANALYSE UTILISATEURS WORDPRESS\n";
        echo "-----------------------------------\n";
        
        if (!function_exists('get_users')) {
            echo "⚠️  WordPress non chargé - analyse limitée\n\n";
            return;
        }
        
        try {
            $users = get_users();
            $admin_users = array();
            $suspicious_count = 0;
            
            foreach ($users as $user) {
                if (in_array('administrator', $user->roles)) {
                    $admin_users[] = $user;
                    
                    // Vérification noms suspects
                    $suspicious_names = array('admin', 'administrator', 'root', 'test', 'demo', '123', 'user', 'guest', 'temp');
                    if (in_array(strtolower($user->user_login), $suspicious_names)) {
                        echo "🚨 Utilisateur suspect: {$user->user_login} (nom générique)\n";
                        $this->threats_found[] = "Utilisateur administrateur suspect: {$user->user_login}";
                        $suspicious_count++;
                    }
                    
                    // Vérification emails suspects
                    $suspicious_email_patterns = array('temp', '123', 'test', 'admin@', 'root@', 'demo@');
                    foreach ($suspicious_email_patterns as $pattern) {
                        if (strpos(strtolower($user->user_email), $pattern) !== false) {
                            echo "🚨 Email suspect: {$user->user_login} ({$user->user_email})\n";
                            $this->threats_found[] = "Email administrateur suspect: {$user->user_email}";
                            $suspicious_count++;
                            break;
                        }
                    }
                    
                    // Vérification dates de création récentes (possibles pirates)
                    $user_registered = strtotime($user->user_registered);
                    $days_ago = (time() - $user_registered) / (24 * 3600);
                    
                    if ($days_ago < 7 && count($admin_users) > 1) {
                        echo "⚠️  Administrateur récent: {$user->user_login} (créé il y a " . round($days_ago, 1) . " jours)\n";
                        $this->warnings[] = "Administrateur créé récemment: {$user->user_login}";
                    }
                }
            }
            
            echo "📊 Total administrateurs: " . count($admin_users) . "\n";
            echo "🚨 Comptes suspects: $suspicious_count\n";
            
            // Alerte si trop d'administrateurs
            if (count($admin_users) > 3) {
                $this->vulnerabilities[] = "Nombre élevé d'administrateurs (" . count($admin_users) . ")";
                echo "⚠️  Nombre élevé d'administrateurs détecté\n";
            }
            
            if ($suspicious_count == 0) {
                echo "✅ Tous les comptes administrateurs semblent légitimes\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Erreur analyse utilisateurs: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    /**
     * Analyse des permissions fichiers
     */
    private function analyzeFilePermissions() {
        echo "🔐 4. ANALYSE PERMISSIONS FICHIERS\n";
        echo "---------------------------------\n";
        
        $critical_files = array(
            'wp-config.php' => array('max' => '0644', 'recommended' => '0600'),
            '.htaccess' => array('max' => '0644', 'recommended' => '0644'),
            'wp-admin/index.php' => array('max' => '0644', 'recommended' => '0644'),
            'wp-includes/version.php' => array('max' => '0644', 'recommended' => '0644')
        );
        
        $critical_dirs = array(
            'wp-content' => array('max' => '0755', 'recommended' => '0755'),
            'wp-admin' => array('max' => '0755', 'recommended' => '0755'),
            'wp-includes' => array('max' => '0755', 'recommended' => '0755'),
            'wp-content/uploads' => array('max' => '0755', 'recommended' => '0755')
        );
        
        $permissions_issues = 0;
        
        // Vérification fichiers
        foreach ($critical_files as $file => $perms) {
            $filepath = $this->wordpress_path . $file;
            if (file_exists($filepath)) {
                $current = substr(sprintf('%o', fileperms($filepath)), -4);
                
                echo "📄 $file: $current ";
                
                if ($current === $perms['recommended']) {
                    echo "✅\n";
                } elseif (octdec($current) <= octdec($perms['max'])) {
                    echo "⚠️  (recommandé: {$perms['recommended']})\n";
                    $this->vulnerabilities[] = "Permissions suboptimales $file: $current";
                    $permissions_issues++;
                } else {
                    echo "❌ DANGEREUX\n";
                    $this->threats_found[] = "Permissions dangereuses $file: $current";
                    $permissions_issues++;
                }
            } else {
                echo "📄 $file: ❌ MANQUANT\n";
                $this->threats_found[] = "Fichier critique manquant: $file";
            }
        }
        
        // Vérification dossiers
        foreach ($critical_dirs as $dir => $perms) {
            $dirpath = $this->wordpress_path . $dir;
            if (is_dir($dirpath)) {
                $current = substr(sprintf('%o', fileperms($dirpath)), -4);
                
                echo "📁 $dir/: $current ";
                
                if ($current === $perms['recommended']) {
                    echo "✅\n";
                } elseif (octdec($current) <= octdec($perms['max'])) {
                    echo "⚠️  (recommandé: {$perms['recommended']})\n";
                    $this->vulnerabilities[] = "Permissions dossier $dir: $current";
                    $permissions_issues++;
                } else {
                    echo "❌ DANGEREUX\n";
                    $this->threats_found[] = "Permissions dangereuses dossier $dir: $current";
                    $permissions_issues++;
                }
            }
        }
        
        if ($permissions_issues == 0) {
            echo "✅ Toutes les permissions sont correctes\n";
        } else {
            echo "⚠️  $permissions_issues problème(s) de permissions détecté(s)\n";
        }
        
        echo "\n";
    }
    
    /**
     * Vérification configuration sécurité
     */
    private function checkConfigurationSecurity() {
        echo "⚙️  5. CONFIGURATION SÉCURITÉ WORDPRESS\n";
        echo "--------------------------------------\n";
        
        $wp_config_path = $this->wordpress_path . 'wp-config.php';
        
        if (!file_exists($wp_config_path)) {
            echo "❌ wp-config.php introuvable\n\n";
            return;
        }
        
        $wp_config = file_get_contents($wp_config_path);
        $security_score = 0;
        $max_score = 10;
        
        // 1. Clés de sécurité
        $security_keys = array('AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY',
                              'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT');
        
        $configured_keys = 0;
        foreach ($security_keys as $key) {
            if (strpos($wp_config, $key) !== false && 
                strpos($wp_config, "put your unique phrase here") === false) {
                $configured_keys++;
            }
        }
        
        if ($configured_keys === 8) {
            echo "✅ Clés de sécurité: 8/8 configurées\n";
            $security_score += 2;
        } else {
            echo "❌ Clés de sécurité: $configured_keys/8 configurées\n";
            $this->vulnerabilities[] = "Clés de sécurité manquantes: " . (8 - $configured_keys);
        }
        
        // 2. Mode debug
        if (strpos($wp_config, "define('WP_DEBUG', false)") !== false || 
            strpos($wp_config, "WP_DEBUG") === false) {
            echo "✅ Mode debug désactivé\n";
            $security_score++;
        } else {
            echo "❌ Mode debug activé (risque sécurité)\n";
            $this->vulnerabilities[] = "Mode debug activé en production";
        }
        
        // 3. Édition fichiers
        if (strpos($wp_config, "define('DISALLOW_FILE_EDIT', true)") !== false) {
            echo "✅ Édition fichiers désactivée\n";
            $security_score++;
        } else {
            echo "⚠️  Édition fichiers autorisée\n";
            $this->vulnerabilities[] = "Édition de fichiers via admin non désactivée";
        }
        
        // 4. Installation plugins/thèmes
        if (strpos($wp_config, "define('DISALLOW_FILE_MODS', true)") !== false) {
            echo "✅ Installation plugins/thèmes bloquée\n";
            $security_score++;
        } else {
            echo "⚠️  Installation plugins/thèmes autorisée\n";
            $this->warnings[] = "Installation de plugins/thèmes non restreinte";
        }
        
        // 5. SSL forcé
        if (strpos($wp_config, "define('FORCE_SSL_ADMIN', true)") !== false) {
            echo "✅ SSL forcé sur administration\n";
            $security_score++;
        } else {
            echo "⚠️  SSL non forcé sur administration\n";
            $this->vulnerabilities[] = "SSL non forcé sur l'administration";
        }
        
        // 6. Révisions limitées
        if (strpos($wp_config, "WP_POST_REVISIONS") !== false) {
            echo "✅ Révisions limitées\n";
            $security_score++;
        } else {
            echo "⚠️  Révisions illimitées\n";
            $this->warnings[] = "Révisions de posts non limitées";
        }
        
        // 7. Corbeille automatique
        if (strpos($wp_config, "EMPTY_TRASH_DAYS") !== false) {
            echo "✅ Vidage corbeille automatique\n";
            $security_score++;
        } else {
            echo "⚠️  Corbeille non vidée automatiquement\n";
            $this->warnings[] = "Vidage automatique de la corbeille non configuré";
        }
        
        // 8. Préfixe base de données personnalisé
        if (defined('table_prefix')) {
            global $table_prefix;
            if ($table_prefix !== 'wp_') {
                echo "✅ Préfixe DB personnalisé ($table_prefix)\n";
                $security_score++;
            } else {
                echo "⚠️  Préfixe DB par défaut (wp_)\n";
                $this->vulnerabilities[] = "Préfixe de base de données par défaut";
            }
        }
        
        // Score final
        $percentage = round(($security_score / $max_score) * 100);
        echo "\n📊 Score sécurité configuration: $security_score/$max_score ($percentage%)\n";
        
        if ($percentage >= 80) {
            echo "✅ Configuration sécurisée\n";
        } elseif ($percentage >= 60) {
            echo "⚠️  Configuration acceptable - améliorations possibles\n";
        } else {
            echo "❌ Configuration à risque - corrections requises\n";
        }
        
        echo "\n";
    }
    
    /**
     * Analyse sécurité base de données
     */
    private function analyzeDatabaseSecurity() {
        echo "🗄️  6. ANALYSE SÉCURITÉ BASE DE DONNÉES\n";
        echo "--------------------------------------\n";
        
        if (!function_exists('get_option')) {
            echo "⚠️  WordPress non chargé - analyse limitée\n\n";
            return;
        }
        
        global $wpdb;
        
        try {
            // Recherche d'options suspectes
            $suspicious_options = $wpdb->get_results(
                "SELECT option_name, option_value FROM {$wpdb->options} 
                 WHERE option_name LIKE '%temp%' 
                 OR option_name LIKE '%backup%'
                 OR option_name LIKE '%cache%'
                 OR option_value LIKE '%base64%'
                 OR option_value LIKE '%eval(%'
                 OR option_value LIKE '%<script%'
                 OR LENGTH(option_value) > 50000
                 LIMIT 50"
            );
            
            $suspicious_count = 0;
            if (!empty($suspicious_options)) {
                echo "🚨 Options suspectes détectées:\n";
                foreach ($suspicious_options as $option) {
                    if (strlen($option->option_value) > 100) {
                        $preview = substr($option->option_value, 0, 100) . '...';
                    } else {
                        $preview = $option->option_value;
                    }
                    
                    echo "   ❌ {$option->option_name}: $preview\n";
                    $this->threats_found[] = "Option DB suspecte: {$option->option_name}";
                    $suspicious_count++;
                }
            }
            
            // Recherche de contenus suspects dans les posts
            $suspicious_posts = $wpdb->get_results(
                "SELECT ID, post_title, post_content FROM {$wpdb->posts} 
                 WHERE post_content LIKE '%<script%' 
                 OR post_content LIKE '%javascript:%'
                 OR post_content LIKE '%base64%'
                 OR post_content LIKE '%eval(%'
                 OR post_content LIKE '%iframe%'
                 LIMIT 20"
            );
            
            if (!empty($suspicious_posts)) {
                echo "🚨 Contenus suspects dans les posts:\n";
                foreach ($suspicious_posts as $post) {
                    echo "   ❌ Post ID {$post->ID}: " . substr($post->post_title, 0, 50) . "\n";
                    $this->threats_found[] = "Contenu suspect dans post ID {$post->ID}";
                    $suspicious_count++;
                }
            }
            
            // Vérification des commentaires suspects
            $suspicious_comments = $wpdb->get_results(
                "SELECT comment_ID, comment_author, comment_content FROM {$wpdb->comments} 
                 WHERE comment_content LIKE '%<script%'
                 OR comment_content LIKE '%http%'
                 OR comment_content LIKE '%viagra%'
                 OR comment_content LIKE '%casino%'
                 LIMIT 10"
            );
            
            if (!empty($suspicious_comments)) {
                echo "🚨 Commentaires suspects:\n";
                foreach ($suspicious_comments as $comment) {
                    echo "   ⚠️  Comment ID {$comment->comment_ID} par {$comment->comment_author}\n";
                    $this->warnings[] = "Commentaire suspect ID {$comment->comment_ID}";
                }
            }
            
            if ($suspicious_count == 0 && empty($suspicious_comments)) {
                echo "✅ Base de données propre\n";
            } else {
                echo "📊 Total éléments suspects: " . ($suspicious_count + count($suspicious_comments)) . "\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Erreur analyse DB: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    /**
     * Vérification de fichiers suspects
     */
    private function checkSuspiciousFiles() {
        echo "📁 7. RECHERCHE FICHIERS SUSPECTS\n";
        echo "---------------------------------\n";
        
        $found_suspicious = array();
        
        // Recherche fichiers suspects par nom
        foreach ($this->suspicious_files as $suspicious_file) {
            $filepath = $this->wordpress_path . $suspicious_file;
            if (file_exists($filepath)) {
                $found_suspicious[] = array(
                    'file' => $suspicious_file,
                    'size' => filesize($filepath),
                    'modified' => date('Y-m-d H:i:s', filemtime($filepath))
                );
                $this->threats_found[] = "Fichier suspect trouvé: $suspicious_file";
                $this->scan_stats['suspicious_files']++;
            }
        }
        
        // Recherche fichiers PHP dans uploads (très suspect)
        $uploads_dir = $this->wordpress_path . 'wp-content/uploads/';
        if (is_dir($uploads_dir)) {
            $php_in_uploads = $this->findPHPInUploads($uploads_dir);
            foreach ($php_in_uploads as $php_file) {
                $found_suspicious[] = array(
                    'file' => str_replace($this->wordpress_path, '', $php_file),
                    'size' => filesize($php_file),
                    'modified' => date('Y-m-d H:i:s', filemtime($php_file)),
                    'reason' => 'PHP dans uploads'
                );
                $this->threats_found[] = "Fichier PHP suspect dans uploads: " . basename($php_file);
                $this->scan_stats['suspicious_files']++;
            }
        }
        
        if (empty($found_suspicious)) {
            echo "✅ Aucun fichier suspect trouvé\n";
        } else {
            echo "🚨 " . count($found_suspicious) . " fichier(s) suspect(s) trouvé(s):\n\n";
            
            foreach ($found_suspicious as $file) {
                echo "❌ FICHIER SUSPECT:\n";
                echo "   📄 Fichier: {$file['file']}\n";
                echo "   📊 Taille: " . $this->formatBytes($file['size']) . "\n";
                echo "   📅 Modifié: {$file['modified']}\n";
                if (isset($file['reason'])) {
                    echo "   ⚠️  Raison: {$file['reason']}\n";
                }
                echo "\n";
            }
        }
        
        echo "\n";
    }
    
    /**
     * Recherche fichiers PHP dans le dossier uploads
     */
    private function findPHPInUploads($uploads_dir) {
        $php_files = array();
        
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($uploads_dir, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
                    $php_files[] = $file->getPathname();
                }
            }
        } catch (Exception $e) {
            // Dossier inaccessible
        }
        
        return $php_files;
    }
    
    /**
     * Vérification vulnérabilités plugins
     */
    private function checkPluginVulnerabilities() {
        echo "🔌 8. ANALYSE VULNÉRABILITÉS PLUGINS\n";
        echo "-----------------------------------\n";
        
        if (!function_exists('get_plugins')) {
            echo "⚠️  WordPress non chargé - analyse limitée\n\n";
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        
        $all_plugins = get_plugins();
        $active_plugins = get_option('active_plugins', array());
        
        echo "📊 Plugins installés: " . count($all_plugins) . "\n";
        echo "📊 Plugins actifs: " . count($active_plugins) . "\n\n";
        
        // Plugins connus pour avoir des vulnérabilités fréquentes
        $vulnerable_plugins = array(
            'wp-file-manager' => 'Vulnérabilités RCE fréquentes',
            'ultimate-member' => 'Failles d\'authentification connues',
            'wp-fastest-cache' => 'Vulnérabilités XSS',
            'contact-form-7' => 'Failles de validation',
            'elementor' => 'Vulnérabilités privilèges',
            'revslider' => 'Failles d\'upload historiques',
            'layerslider' => 'Vulnérabilités d\'inclusion',
            'wptouch' => 'Failles XSS multiples'
        );
        
        $risky_plugins = 0;
        $outdated_plugins = 0;
        
        foreach ($all_plugins as $plugin_path => $plugin_data) {
            $plugin_slug = dirname($plugin_path);
            
            // Vérification plugins à risque
            if (isset($vulnerable_plugins[$plugin_slug]) && in_array($plugin_path, $active_plugins)) {
                echo "🚨 Plugin à risque actif: {$plugin_data['Name']}\n";
                echo "   ⚠️  Risque: {$vulnerable_plugins[$plugin_slug]}\n";
                $this->vulnerabilities[] = "Plugin à risque actif: {$plugin_data['Name']}";
                $risky_plugins++;
            }
            
            // Vérification plugins non mis à jour depuis longtemps
            if (isset($plugin_data['Version'])) {
                // Cette vérification nécessiterait une API externe pour être précise
                // Ici nous faisons une vérification basique sur la version
                if (preg_match('/^[0-9]\./', $plugin_data['Version'])) {
                    $version_parts = explode('.', $plugin_data['Version']);
                    if (isset($version_parts[0]) && $version_parts[0] < 2) {
                        echo "⚠️  Plugin potentiellement obsolète: {$plugin_data['Name']} (v{$plugin_data['Version']})\n";
                        $this->warnings[] = "Plugin potentiellement obsolète: {$plugin_data['Name']}";
                        $outdated_plugins++;
                    }
                }
            }
        }
        
        if ($risky_plugins === 0 && $outdated_plugins === 0) {
            echo "✅ Aucun plugin à risque détecté\n";
        } else {
            echo "\n📊 Résumé plugins:\n";
            echo "   🚨 Plugins à risque: $risky_plugins\n";
            echo "   ⚠️  Plugins potentiellement obsolètes: $outdated_plugins\n";
        }
        
        echo "\n";
    }
    
    /**
     * Génération du rapport final
     */
    private function generateSecurityReport() {
        $end_time = microtime(true);
        $scan_duration = round($end_time - $this->start_time, 2);
        
        echo "📋 RAPPORT D'AUDIT SÉCURITÉ EXPERT\n";
        echo "==================================\n\n";
        
        // Statistiques du scan
        echo "📊 STATISTIQUES DU SCAN:\n";
        echo "⏱️  Durée: {$scan_duration} secondes\n";
        echo "📁 Fichiers scannés: {$this->scan_stats['files_scanned']}\n";
        echo "🦠 Malwares détectés: {$this->scan_stats['malware_detected']}\n";
        echo "📄 Fichiers suspects: {$this->scan_stats['suspicious_files']}\n\n";
        
        // Résumé des menaces
        $total_critical = count($this->threats_found);
        $total_vulnerabilities = count($this->vulnerabilities);
        $total_warnings = count($this->warnings);
        
        echo "🎯 RÉSUMÉ SÉCURITÉ:\n";
        echo "🚨 Menaces critiques: $total_critical\n";
        echo "⚠️  Vulnérabilités: $total_vulnerabilities\n";
        echo "💡 Avertissements: $total_warnings\n\n";
        
        // Détail des menaces critiques
        if (!empty($this->threats_found)) {
            echo "🚨 MENACES CRITIQUES - ACTION IMMÉDIATE REQUISE:\n";
            echo "===============================================\n";
            foreach ($this->threats_found as $i => $threat) {
                echo ($i + 1) . ". ❌ $threat\n";
            }
            echo "\n";
        }
        
        // Détail des vulnérabilités
        if (!empty($this->vulnerabilities)) {
            echo "⚠️  VULNÉRABILITÉS À CORRIGER:\n";
            echo "=============================\n";
            foreach ($this->vulnerabilities as $i => $vulnerability) {
                echo ($i + 1) . ". ⚠️  $vulnerability\n";
            }
            echo "\n";
        }
        
        // Avertissements
        if (!empty($this->warnings)) {
            echo "💡 AMÉLIORATIONS RECOMMANDÉES:\n";
            echo "==============================\n";
            foreach ($this->warnings as $i => $warning) {
                echo ($i + 1) . ". 💡 $warning\n";
            }
            echo "\n";
        }
        
        // Évaluation du niveau de risque
        if ($total_critical > 0) {
            $risk_level = "🚨 CRITIQUE";
            $risk_color = "rouge";
            $recommendation = "INTERVENTION D'URGENCE NÉCESSAIRE";
        } elseif ($total_vulnerabilities > 5) {
            $risk_level = "⚠️  ÉLEVÉ";
            $risk_color = "orange";
            $recommendation = "Correction rapide fortement recommandée";
        } elseif ($total_vulnerabilities > 0 || $total_warnings > 3) {
            $risk_level = "🟡 MODÉRÉ";
            $risk_color = "jaune";
            $recommendation = "Améliorations de sécurité suggérées";
        } else {
            $risk_level = "✅ FAIBLE";
            $risk_color = "vert";
            $recommendation = "Sécurité satisfaisante, maintenance préventive";
        }
        
        echo "🎯 NIVEAU DE RISQUE GLOBAL: $risk_level\n";
        echo "💡 RECOMMANDATION: $recommendation\n\n";
        
        // Score de sécurité
        $total_issues = $total_critical + $total_vulnerabilities + $total_warnings;
        if ($total_issues === 0) {
            $security_score = 100;
        } else {
            $security_score = max(0, 100 - ($total_critical * 20) - ($total_vulnerabilities * 5) - ($total_warnings * 2));
        }
        
        echo "📊 SCORE DE SÉCURITÉ: $security_score/100\n\n";
        
        // Actions recommandées
        echo "🔧 ACTIONS PRIORITAIRES:\n";
        echo "=======================\n";
        
        if ($total_critical > 0) {
            echo "1. 🚨 URGENT: Nettoyage des malwares et fichiers infectés\n";
            echo "2. 🔒 Changement immédiat des mots de passe administrateurs\n";
            echo "3. 🛡️  Scan complet avec outils professionnels\n";
            echo "4. 📞 Contact expert sécurité WordPress\n";
        } elseif ($total_vulnerabilities > 0) {
            echo "1. 🔧 Correction des vulnérabilités identifiées\n";
            echo "2. 🔐 Renforcement configuration sécurité\n";
            echo "3. 🔄 Mise à jour plugins et thèmes\n";
            echo "4. 📊 Audit régulier programmé\n";
        } else {
            echo "1. ✅ Maintenir les bonnes pratiques actuelles\n";
            echo "2. 🔄 Surveillance continue mise à jour\n";
            echo "3. 📅 Audit sécurité mensuel\n";
            echo "4. 💾 Vérification sauvegardes régulières\n";
        }
        
        echo "\n";
        
        // Contact expert si problèmes critiques
        if ($total_critical > 0 || $total_vulnerabilities > 5) {
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "🆘 SITE COMPROMIS OU À HAUT RISQUE DÉTECTÉ !\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "🔧 Nettoyage professionnel requis immédiatement\n";
            echo "🏆 Expert WordPress certifié - 12+ années d'expérience\n";
            echo "⚡ Intervention d'urgence sous 6h maximum\n";
            echo "✅ 120+ sites piratés nettoyés avec succès\n";
            echo "📞 Service professionnel: https://teddywp.com/depannage-wordpress/\n";
            echo "🛡️  Garantie \"Site propre ou remboursé\"\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        }
        
        echo "\n👨‍💻 Audit réalisé par Teddy - Expert WordPress\n";
        echo "🌐 TeddyWP.com | 📧 Dépannage d'urgence disponible 24/7\n";
        echo "📅 " . date('Y-m-d H:i:s') . " | Version script: 3.0\n";
    }
    
    /**
     * Formatage des tailles de fichiers
     */
    private function formatBytes($size, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
}

// =====================================================
// LANCEMENT DU SCRIPT
// =====================================================

// Vérification arguments ligne de commande
$wordpress_path = './';
if (isset($argv[1])) {
    $wordpress_path = rtrim($argv[1], '/') . '/';
    
    if (!is_dir($wordpress_path)) {
        echo "❌ Erreur: Le chemin '$wordpress_path' n'existe pas.\n";
        echo "Usage: php audit-securite.php [chemin-wordpress]\n";
        exit(1);
    }
}

echo "📍 Chemin WordPress: $wordpress_path\n\n";

// Lancement de l'audit
try {
    $auditor = new WordPressSecurityAuditor($wordpress_path);
    $auditor->runCompleteAudit();
} catch (Exception $e) {
    echo "❌ Erreur fatale durant l'audit: " . $e->getMessage() . "\n";
    echo "📞 Pour assistance: https://teddywp.com/depannage-wordpress/\n";
    exit(1);
}

echo "\n🏁 Audit de sécurité terminé avec succès !\n";
?>
