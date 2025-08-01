<?php
/**
 * TEST CONNECTION BASE DE DONNÉES WORDPRESS - Script Expert
 * 
 * Script spécialisé pour diagnostiquer les problèmes de connexion DB WordPress
 * Résout l'erreur "Error establishing a database connection" 
 * 
 * Basé sur 12+ années d'expérience - 800+ interventions
 * Cette erreur représente 25% des pannes WordPress critiques
 * 
 * @author Teddy - Expert WordPress  
 * @version 2.0
 * @website https://teddywp.com
 * @service https://teddywp.com/depannage-wordpress/
 * 
 * Usage: php test-db-connection.php [chemin-wordpress]
 */

// Configuration robuste
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 60);
set_time_limit(60);

class WordPressDatabaseTester {
    
    private $wordpress_path;
    private $db_config = array();
    private $connection_tests = array();
    private $start_time;
    
    // Types d'erreurs DB les plus courantes (stats sur 800+ interventions)
    private $common_db_errors = array(
        'access_denied' => 45,          // 45% - Identifiants incorrects
        'connection_refused' => 25,     // 25% - Serveur MySQL down/inaccessible  
        'unknown_database' => 15,       // 15% - Base de données inexistante
        'host_unreachable' => 10,       // 10% - Problème réseau/firewall
        'too_many_connections' => 5     // 5% - Limite connexions dépassée
    );
    
    public function __construct($wordpress_path = './') {
        $this->wordpress_path = rtrim($wordpress_path, '/') . '/';
        $this->start_time = microtime(true);
        
        echo "🗄️  TEST CONNECTION BASE DE DONNÉES WORDPRESS\n";
        echo "============================================\n";
        echo "👨‍💻 Développé par Teddy - Expert WordPress\n";
        echo "📊 Basé sur l'analyse de 800+ pannes DB WordPress\n";
        echo "⚡ Résolution rapide des erreurs de connexion\n\n";
        
        if (!is_dir($this->wordpress_path)) {
            die("❌ Erreur: Chemin WordPress introuvable: {$this->wordpress_path}\n");
        }
        
        echo "📍 Site WordPress: {$this->wordpress_path}\n\n";
    }
    
    /**
     * Test complet de la base de données
     */
    public function runDatabaseTest() {
        echo "🔍 DÉMARRAGE TEST BASE DE DONNÉES\n";
        echo "=================================\n\n";
        
        $this->loadDatabaseConfig();
        $this->testBasicConnection();
        $this->testDatabaseAccess();
        $this->testWordPressTables();
        $this->testDatabasePerformance();
        $this->analyzeServerStatus();
        $this->generateDatabaseReport();
    }
    
    /**
     * Chargement configuration base de données
     */
    private function loadDatabaseConfig() {
        echo "📋 1. CHARGEMENT CONFIGURATION DATABASE\n";
        echo "---------------------------------------\n";
        
        $wp_config_path = $this->wordpress_path . 'wp-config.php';
        
        if (!file_exists($wp_config_path)) {
            die("❌ Erreur fatale: wp-config.php introuvable\n");
        }
        
        echo "✅ wp-config.php trouvé\n";
        
        // Lecture sécurisée de wp-config.php
        $wp_config_content = file_get_contents($wp_config_path);
        
        // Extraction des constantes DB
        $db_constants = array('DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_CHARSET', 'DB_COLLATE');
        
        foreach ($db_constants as $constant) {
            if (preg_match("/define\s*\(\s*['\"]" . $constant . "['\"]\s*,\s*['\"](.*?)['\"]\s*\)/", $wp_config_content, $matches)) {
                $this->db_config[$constant] = $matches[1];
                
                // Masquage du mot de passe pour l'affichage
                if ($constant === 'DB_PASSWORD') {
                    $display_value = str_repeat('*', min(8, strlen($matches[1])));
                } else {
                    $display_value = $matches[1];
                }
                
                echo "✅ $constant: $display_value\n";
            } else {
                echo "❌ $constant: NON DÉFINI\n";
                $this->connection_tests[] = array(
                    'test' => 'Configuration',
                    'status' => 'FAILED',
                    'error' => "$constant manquant dans wp-config.php"
                );
            }
        }
        
        // Vérification valeurs critiques
        if (empty($this->db_config['DB_HOST'])) {
            die("❌ Erreur fatale: DB_HOST non défini\n");
        }
        
        if (empty($this->db_config['DB_NAME'])) {
            die("❌ Erreur fatale: DB_NAME non défini\n");
        }
        
        echo "\n📊 Configuration chargée avec succès\n";
        
        // Analyse du host pour port personnalisé
        if (strpos($this->db_config['DB_HOST'], ':') !== false) {
            list($host, $port) = explode(':', $this->db_config['DB_HOST']);
            echo "🔍 Host détecté: $host (Port: $port)\n";
        } else {
            echo "🔍 Host détecté: {$this->db_config['DB_HOST']} (Port: 3306 par défaut)\n";
        }
        
        echo "\n";
    }
    
    /**
     * Test de connexion basique
     */
    private function testBasicConnection() {
        echo "🔌 2. TEST CONNEXION BASIQUE\n";
        echo "---------------------------\n";
        
        $start_time = microtime(true);
        
        try {
            echo "🔄 Tentative connexion MySQL...\n";
            
            // Tentative avec PDO (plus robuste)
            $dsn = "mysql:host={$this->db_config['DB_HOST']};charset=utf8mb4";
            
            $pdo = new PDO($dsn, 
                          $this->db_config['DB_USER'], 
                          $this->db_config['DB_PASSWORD'],
                          array(
                              PDO::ATTR_TIMEOUT => 10,
                              PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                              PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                          ));
            
            $connection_time = round((microtime(true) - $start_time) * 1000, 2);
            
            echo "✅ CONNEXION RÉUSSIE!\n";
            echo "⏱️  Temps connexion: {$connection_time}ms\n";
            
            // Informations serveur MySQL
            $server_info = $pdo->query("SELECT VERSION() as version")->fetch(PDO::FETCH_ASSOC);
            echo "🗄️  Version MySQL: {$server_info['version']}\n";
            
            // Variables MySQL importantes
            $variables = $pdo->query("SHOW VARIABLES WHERE Variable_name IN ('max_connections', 'wait_timeout', 'max_allowed_packet')")->fetchAll(PDO::FETCH_KEY_PAIR);
            
            echo "📊 Max connexions: {$variables['max_connections']}\n";
            echo "📊 Timeout: {$variables['wait_timeout']}s\n";
            echo "📊 Max packet: " . $this->formatBytes($variables['max_allowed_packet']) . "\n";
            
            $this->connection_tests[] = array(
                'test' => 'Connexion MySQL',
                'status' => 'SUCCESS',
                'time' => $connection_time,
                'details' => "MySQL {$server_info['version']}"
            );
            
            // Test performance connexion
            if ($connection_time > 1000) {
                echo "⚠️  Connexion lente détectée (>{$connection_time}ms)\n";
                $this->connection_tests[] = array(
                    'test' => 'Performance connexion',
                    'status' => 'WARNING', 
                    'details' => "Connexion lente: {$connection_time}ms"
                );
            }
            
        } catch (PDOException $e) {
            $connection_time = round((microtime(true) - $start_time) * 1000, 2);
            
            echo "❌ ÉCHEC CONNEXION MySQL\n";
            echo "⏱️  Temps avant échec: {$connection_time}ms\n";
            echo "📝 Erreur: " . $e->getMessage() . "\n\n";
            
            // Diagnostic de l'erreur spécifique
            $this->diagnoseDatabaseError($e->getMessage());
            
            $this->connection_tests[] = array(
                'test' => 'Connexion MySQL',
                'status' => 'FAILED',
                'error' => $e->getMessage(),
                'time' => $connection_time
            );
            
            return false;
        }
        
        echo "\n";
        return true;
    }
    
    /**
     * Test d'accès à la base de données spécifique
     */
    private function testDatabaseAccess() {
        echo "🏢 3. TEST ACCÈS BASE DE DONNÉES\n";
        echo "-------------------------------\n";
        
        $start_time = microtime(true);
        
        try {
            echo "🔄 Connexion à la base '{$this->db_config['DB_NAME']}'...\n";
            
            $dsn = "mysql:host={$this->db_config['DB_HOST']};dbname={$this->db_config['DB_NAME']};charset=utf8mb4";
            
            $pdo = new PDO($dsn, 
                          $this->db_config['DB_USER'], 
                          $this->db_config['DB_PASSWORD'],
                          array(
                              PDO::ATTR_TIMEOUT => 10,
                              PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                          ));
            
            $db_access_time = round((microtime(true) - $start_time) * 1000, 2);
            
            echo "✅ ACCÈS BASE DE DONNÉES RÉUSSI!\n";
            echo "⏱️  Temps accès: {$db_access_time}ms\n";
            
            // Informations sur la base de données
            $db_info = $pdo->query("SELECT 
                COUNT(*) as table_count,
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
                FROM information_schema.tables 
                WHERE table_schema = '{$this->db_config['DB_NAME']}'")->fetch(PDO::FETCH_ASSOC);
            
            echo "📊 Nombre de tables: {$db_info['table_count']}\n";
            echo "📊 Taille base: {$db_info['size_mb']} MB\n";
            
            // Charset de la base
            $charset_info = $pdo->query("SELECT DEFAULT_CHARACTER_SET_NAME as charset, DEFAULT_COLLATION_NAME as collation FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = '{$this->db_config['DB_NAME']}'")->fetch(PDO::FETCH_ASSOC);
            
            echo "🔤 Charset: {$charset_info['charset']}\n";
            echo "🔤 Collation: {$charset_info['collation']}\n";
            
            // Vérification charset recommandé
            if ($charset_info['charset'] !== 'utf8mb4') {
                echo "⚠️  Charset non recommandé (utf8mb4 conseillé)\n";
                $this->connection_tests[] = array(
                    'test' => 'Charset base de données',
                    'status' => 'WARNING',
                    'details' => "Charset actuel: {$charset_info['charset']}, recommandé: utf8mb4"
                );
            }
            
            $this->connection_tests[] = array(
                'test' => 'Accès base de données',
                'status' => 'SUCCESS',
                'time' => $db_access_time,
                'details' => "{$db_info['table_count']} tables, {$db_info['size_mb']} MB"
            );
            
        } catch (PDOException $e) {
            $db_access_time = round((microtime(true) - $start_time) * 1000, 2);
            
            echo "❌ ÉCHEC ACCÈS BASE DE DONNÉES\n";
            echo "⏱️  Temps avant échec: {$db_access_time}ms\n";
            echo "📝 Erreur: " . $e->getMessage() . "\n\n";
            
            $this->diagnoseDatabaseError($e->getMessage());
            
            $this->connection_tests[] = array(
                'test' => 'Accès base de données',
                'status' => 'FAILED',
                'error' => $e->getMessage(),
                'time' => $db_access_time
            );
            
            return false;
        }
        
        echo "\n";
        return true;
    }
    
    /**
     * Test des tables WordPress
     */
    private function testWordPressTables() {
        echo "🏗️  4. VÉRIFICATION TABLES WORDPRESS\n";
        echo "-----------------------------------\n";
        
        try {
            $dsn = "mysql:host={$this->db_config['DB_HOST']};dbname={$this->db_config['DB_NAME']};charset=utf8mb4";
            $pdo = new PDO($dsn, $this->db_config['DB_USER'], $this->db_config['DB_PASSWORD']);
            
            // Détection du préfixe WordPress
            $prefix_candidates = array('wp_', 'wordpress_', 'blog_');
            $table_prefix = null;
            
            foreach ($prefix_candidates as $candidate) {
                $result = $pdo->query("SHOW TABLES LIKE '{$candidate}options'")->rowCount();
                if ($result > 0) {
                    $table_prefix = $candidate;
                    break;
                }
            }
            
            if (!$table_prefix) {
                // Recherche automatique du préfixe
                $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($tables as $table) {
                    if (preg_match('/^(.+)options$/', $table, $matches)) {
                        $table_prefix = $matches[1];
                        break;
                    }
                }
            }
            
            if (!$table_prefix) {
                echo "❌ Aucune table WordPress trouvée\n";
                echo "💡 La base existe mais ne contient pas WordPress\n\n";
                
                $this->connection_tests[] = array(
                    'test' => 'Tables WordPress',
                    'status' => 'FAILED',
                    'error' => 'Aucune table WordPress trouvée'
                );
                
                return false;
            }
            
            echo "✅ Préfixe WordPress détecté: $table_prefix\n";
            
            // Tables WordPress essentielles
            $essential_tables = array(
                'options' => 'Configuration site',
                'posts' => 'Articles et pages', 
                'users' => 'Utilisateurs',
                'usermeta' => 'Métadonnées utilisateurs',
                'postmeta' => 'Métadonnées articles',
                'comments' => 'Commentaires',
                'commentmeta' => 'Métadonnées commentaires',
                'terms' => 'Termes taxonomies',
                'term_taxonomy' => 'Taxonomies',
                'term_relationships' => 'Relations termes'
            );
            
            $missing_tables = array();
            $table_stats = array();
            
            foreach ($essential_tables as $table => $description) {
                $full_table_name = $table_prefix . $table;
                
                $result = $pdo->query("SHOW TABLES LIKE '$full_table_name'")->rowCount();
                
                if ($result > 0) {
                    // Statistiques de la table
                    $count = $pdo->query("SELECT COUNT(*) FROM $full_table_name")->fetchColumn();
                    echo "✅ $full_table_name: $count entrées\n";
                    $table_stats[$table] = $count;
                } else {
                    echo "❌ $full_table_name: MANQUANTE\n";
                    $missing_tables[] = $table;
                }
            }
            
            if (empty($missing_tables)) {
                echo "\n✅ Toutes les tables WordPress essentielles présentes\n";
                
                $this->connection_tests[] = array(
                    'test' => 'Tables WordPress',
                    'status' => 'SUCCESS',
                    'details' => count($essential_tables) . " tables vérifiées"
                );
                
                // Vérifications de cohérence
                $this->checkTableIntegrity($pdo, $table_prefix, $table_stats);
                
            } else {
                echo "\n❌ Tables manquantes: " . implode(', ', $missing_tables) . "\n";
                echo "💡 Installation WordPress corrompue ou incomplète\n";
                
                $this->connection_tests[] = array(
                    'test' => 'Tables WordPress',
                    'status' => 'FAILED',
                    'error' => 'Tables manquantes: ' . implode(', ', $missing_tables)
                );
            }
            
        } catch (PDOException $e) {
            echo "❌ Erreur vérification tables: " . $e->getMessage() . "\n";
            
            $this->connection_tests[] = array(
                'test' => 'Tables WordPress',
                'status' => 'FAILED', 
                'error' => $e->getMessage()
            );
        }
        
        echo "\n";
    }
    
    /**
     * Vérification intégrité des tables
     */
    private function checkTableIntegrity($pdo, $prefix, $stats) {
        echo "🔍 Vérification intégrité des tables...\n";
        
        // Vérifications de cohérence basiques
        $integrity_checks = array();
        
        // Vérification users/usermeta
        if (isset($stats['users']) && isset($stats['usermeta'])) {
            if ($stats['users'] > 0 && $stats['usermeta'] == 0) {
                $integrity_checks[] = "Utilisateurs sans métadonnées";
            }
        }
        
        // Vérification posts/postmeta
        if (isset($stats['posts']) && isset($stats['postmeta'])) {
            if ($stats['posts'] > 10 && $stats['postmeta'] == 0) {
                $integrity_checks[] = "Articles sans métadonnées";
            }
        }
        
        // Test de requête WordPress typique
        try {
            $site_url = $pdo->query("SELECT option_value FROM {$prefix}options WHERE option_name = 'siteurl'")->fetchColumn();
            if ($site_url) {
                echo "✅ Site URL: $site_url\n";
            } else {
                $integrity_checks[] = "URL du site non définie";
            }
            
            $admin_email = $pdo->query("SELECT option_value FROM {$prefix}options WHERE option_name = 'admin_email'")->fetchColumn();
            if ($admin_email) {
                echo "✅ Email admin: $admin_email\n";
            }
            
        } catch (PDOException $e) {
            $integrity_checks[] = "Erreur lecture options: " . $e->getMessage();
        }
        
        if (empty($integrity_checks)) {
            echo "✅ Intégrité des données vérifiée\n";
        } else {
            echo "⚠️  Problèmes d'intégrité détectés:\n";
            foreach ($integrity_checks as $issue) {
                echo "   • $issue\n";
            }
        }
    }
    
    /**
     * Test de performance base de données
     */
    private function testDatabasePerformance() {
        echo "⚡ 5. TEST PERFORMANCE BASE DE DONNÉES\n";
        echo "------------------------------------\n";
        
        try {
            $dsn = "mysql:host={$this->db_config['DB_HOST']};dbname={$this->db_config['DB_NAME']};charset=utf8mb4";
            $pdo = new PDO($dsn, $this->db_config['DB_USER'], $this->db_config['DB_PASSWORD']);
            
            // Test 1: Requête simple
            $start = microtime(true);
            $pdo->query("SELECT 1")->fetch();
            $simple_query_time = round((microtime(true) - $start) * 1000, 2);
            
            echo "📊 Requête simple: {$simple_query_time}ms\n";
            
            // Test 2: Requête complexe (si tables WordPress présentes)
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            $wp_options_table = null;
            
            foreach ($tables as $table) {
                if (preg_match('/options$/', $table)) {
                    $wp_options_table = $table;
                    break;
                }
            }
            
            if ($wp_options_table) {
                $start = microtime(true);
                $pdo->query("SELECT * FROM $wp_options_table WHERE autoload = 'yes' LIMIT 100")->fetchAll();
                $complex_query_time = round((microtime(true) - $start) * 1000, 2);
                
                echo "📊 Requête complexe: {$complex_query_time}ms\n";
                
                if ($complex_query_time > 500) {
                    echo "⚠️  Requête lente détectée - optimisation DB recommandée\n";
                    $this->connection_tests[] = array(
                        'test' => 'Performance DB',
                        'status' => 'WARNING',
                        'details' => "Requête lente: {$complex_query_time}ms"
                    );
                }
            }
            
            // Test 3: Multiple connexions rapides
            $start = microtime(true);
            for ($i = 0; $i < 5; $i++) {
                $test_pdo = new PDO($dsn, $this->db_config['DB_USER'], $this->db_config['DB_PASSWORD']);
                $test_pdo->query("SELECT 1")->fetch();
                $test_pdo = null;
            }
            $multi_conn_time = round((microtime(true) - $start) * 1000, 2);
            
            echo "📊 5 connexions multiples: {$multi_conn_time}ms\n";
            
            // Évaluation performance globale
            $total_score = $simple_query_time + ($complex_query_time ?? 0) + $multi_conn_time;
            
            if ($total_score < 100) {
                echo "✅ Performance base de données: EXCELLENTE\n";
            } elseif ($total_score < 500) {
                echo "✅ Performance base de données: BONNE\n";
            } elseif ($total_score < 1000) {
                echo "⚠️  Performance base de données: MOYENNE\n";
            } else {
                echo "❌ Performance base de données: DÉGRADÉE\n";
                echo "💡 Optimisation serveur recommandée\n";
            }
            
            $this->connection_tests[] = array(
                'test' => 'Performance globale',
                'status' => $total_score < 500 ? 'SUCCESS' : 'WARNING',
                'details' => "Score total: {$total_score}ms"
            );
            
        } catch (PDOException $e) {
            echo "❌ Erreur test performance: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    /**
     * Analyse du statut serveur MySQL
     */
    private function analyzeServerStatus() {
        echo "🖥️  6. ANALYSE STATUT SERVEUR MYSQL\n";
        echo "----------------------------------\n";
        
        try {
            $dsn = "mysql:host={$this->db_config['DB_HOST']};charset=utf8mb4";
            $pdo = new PDO($dsn, $this->db_config['DB_USER'], $this->db_config['DB_PASSWORD']);
            
            // Statut des connexions
            $status = $pdo->query("SHOW STATUS WHERE Variable_name IN ('Connections', 'Threads_connected', 'Max_used_connections', 'Aborted_connects')")->fetchAll(PDO::FETCH_KEY_PAIR);
            
            echo "📈 Connexions totales: " . ($status['Connections'] ?? 'N/A') . "\n";
            echo "📈 Connexions actives: " . ($status['Threads_connected'] ?? 'N/A') . "\n";
            echo "📈 Max connexions utilisées: " . ($status['Max_used_connections'] ?? 'N/A') . "\n";
            echo "📈 Connexions avortées: " . ($status['Aborted_connects'] ?? 'N/A') . "\n";
            
            // Warnings si problèmes détectés
            if (isset($status['Aborted_connects']) && $status['Aborted_connects'] > 100) {
                echo "⚠️  Nombre élevé de connexions avortées\n";
                echo "💡 Vérifiez les identifiants et la stabilité réseau\n";
            }
            
            // Processus MySQL en cours
            try {
                $processes = $pdo->query("SHOW PROCESSLIST")->fetchAll(PDO::FETCH_ASSOC);
                $active_processes = count($processes);
                
                echo "⚙️  Processus actifs: $active_processes\n";
                
                // Recherche de requêtes lentes
                $slow_queries = 0;
                foreach ($processes as $process) {
                    if (isset($process['Time']) && $process['Time'] > 30) {
                        $slow_queries++;
                    }
                }
                
                if ($slow_queries > 0) {
                    echo "⚠️  Requêtes lentes détectées: $slow_queries\n";
                    echo "💡 Optimisation de la base recommandée\n";
                }
                
            } catch (PDOException $e) {
                echo "⚠️  Impossible d'analyser les processus (permissions limitées)\n";
            }
            
            // Variables importantes
            $variables = $pdo->query("SHOW VARIABLES WHERE Variable_name IN ('innodb_buffer_pool_size', 'key_buffer_size', 'query_cache_size')")->fetchAll(PDO::FETCH_KEY_PAIR);
            
            if (!empty($variables)) {
                echo "\n🔧 Configuration MySQL:\n";
                foreach ($variables as $var => $value) {
                    if (is_numeric($value)) {
                        $value = $this->formatBytes($value);
                    }
                    echo "   $var: $value\n";
                }
            }
            
        } catch (PDOException $e) {
            echo "⚠️  Analyse serveur limitée: " . $e->getMessage() . "\n";
            echo "💡 Permissions insuffisantes ou version MySQL ancienne\n";
        }
        
        echo "\n";
    }
    
    /**
     * Diagnostic des erreurs base de données
     */
    private function diagnoseDatabaseError($error_message) {
        echo "🔍 DIAGNOSTIC ERREUR SPÉCIFIQUE:\n";
        echo "-------------------------------\n";
        
        $error_lower = strtolower($error_message);
        
        if (strpos($error_lower, 'access denied') !== false) {
            echo "🎯 PROBLÈME: Identifiants de connexion incorrects\n";
            echo "💡 SOLUTIONS:\n";
            echo "   1. Vérifiez DB_USER et DB_PASSWORD dans wp-config.php\n";
            echo "   2. Connectez-vous à cPanel/PHPMyAdmin pour valider les identifiants\n";
            echo "   3. Créez un nouvel utilisateur MySQL si nécessaire\n\n";
            
        } elseif (strpos($error_lower, 'connection refused') !== false || strpos($error_lower, 'can\'t connect') !== false) {
            echo "🎯 PROBLÈME: Serveur MySQL inaccessible\n";
            echo "💡 SOLUTIONS:\n";
            echo "   1. Vérifiez que MySQL est démarré sur le serveur\n";
            echo "   2. Vérifiez DB_HOST dans wp-config.php (localhost, IP, nom de domaine)\n";
            echo "   3. Contactez votre hébergeur pour statut du serveur MySQL\n";
            echo "   4. Vérifiez les firewall et restrictions réseau\n\n";
            
        } elseif (strpos($error_lower, 'unknown database') !== false) {
            echo "🎯 PROBLÈME: Base de données inexistante\n";
            echo "💡 SOLUTIONS:\n";
            echo "   1. Vérifiez DB_NAME dans wp-config.php\n";
            echo "   2. Créez la base de données via cPanel/PHPMyAdmin\n";
            echo "   3. Restaurez la base depuis une sauvegarde si disponible\n";
            echo "   4. Vérifiez les droits d'accès à la base\n\n";
            
        } elseif (strpos($error_lower, 'too many connections') !== false) {
            echo "🎯 PROBLÈME: Limite de connexions MySQL dépassée\n";
            echo "💡 SOLUTIONS:\n";
            echo "   1. Attendez quelques minutes et réessayez\n";
            echo "   2. Contactez l'hébergeur pour augmenter max_connections\n";
            echo "   3. Optimisez les plugins pour réduire les connexions\n";
            echo "   4. Implémentez un système de cache\n\n";
            
        } elseif (strpos($error_lower, 'timeout') !== false || strpos($error_lower, 'timed out') !== false) {
            echo "🎯 PROBLÈME: Timeout de connexion\n";
            echo "💡 SOLUTIONS:\n";
            echo "   1. Vérifiez la stabilité de la connexion réseau\n";
            echo "   2. Augmentez les timeouts PHP (max_execution_time)\n";
            echo "   3. Contactez l'hébergeur pour problèmes de performance MySQL\n";
            echo "   4. Vérifiez la charge serveur\n\n";
            
        } else {
            echo "🎯 PROBLÈME: Erreur MySQL générique\n";
            echo "💡 SOLUTIONS GÉNÉRALES:\n";
            echo "   1. Vérifiez tous les paramètres dans wp-config.php\n";
            echo "   2. Testez la connexion avec un client MySQL externe\n";
            echo "   3. Consultez les logs du serveur MySQL\n";
            echo "   4. Contactez votre hébergeur pour assistance\n\n";
        }
        
        echo "📞 Besoin d'aide experte ? Service de dépannage WordPress 24/7\n";
        echo "🌐 https://teddywp.com/depannage-wordpress/\n\n";
    }
    
    /**
     * Génération du rapport final
     */
    private function generateDatabaseReport() {
        $end_time = microtime(true);
        $test_duration = round($end_time - $this->start_time, 2);
        
        echo "📋 RAPPORT TEST BASE DE DONNÉES\n";
        echo "===============================\n\n";
        
        echo "⏱️  Durée des tests: {$test_duration} secondes\n";
        echo "🧪 Tests effectués: " . count($this->connection_tests) . "\n\n";
        
        // Classification des résultats
        $success_count = 0;
        $warning_count = 0;
        $failed_count = 0;
        
        foreach ($this->connection_tests as $test) {
            switch ($test['status']) {
                case 'SUCCESS':
                    $success_count++;
                    break;
                case 'WARNING':
                    $warning_count++;
                    break;
                case 'FAILED':
                    $failed_count++;
                    break;
            }
        }
        
        echo "📊 RÉSUMÉ DES TESTS:\n";
        echo "✅ Réussis: $success_count\n";
        echo "⚠️  Avertissements: $warning_count\n";
        echo "❌ Échecs: $failed_count\n\n";
        
        // Détail des tests
        echo "📋 DÉTAIL DES TESTS:\n";
        echo "===================\n";
        
        foreach ($this->connection_tests as $i => $test) {
            $status_icon = array(
                'SUCCESS' => '✅',
                'WARNING' => '⚠️ ',
                'FAILED' => '❌'
            );
            
            echo ($i + 1) . ". {$status_icon[$test['status']]} {$test['test']}\n";
            
            if (isset($test['time'])) {
                echo "   ⏱️  Temps: {$test['time']}ms\n";
            }
            
            if (isset($test['details'])) {
                echo "   📝 Détails: {$test['details']}\n";
            }
            
            if (isset($test['error'])) {
                echo "   ❌ Erreur: {$test['error']}\n";
            }
            
            echo "\n";
        }
        
        // Évaluation globale
        if ($failed_count > 0) {
            $global_status = "🚨 CRITIQUE";
            $recommendation = "Intervention immédiate requise";
        } elseif ($warning_count > 2) {
            $global_status = "⚠️  ATTENTION";
            $recommendation = "Optimisations recommandées";
        } elseif ($warning_count > 0) {
            $global_status = "🟡 ACCEPTABLE";
            $recommendation = "Surveillance et améliorations mineures";
        } else {
            $global_status = "✅ EXCELLENT";
            $recommendation = "Base de données optimale";
        }
        
        echo "🎯 STATUT GLOBAL: $global_status\n";
        echo "💡 RECOMMANDATION: $recommendation\n\n";
        
        // Actions prioritaires
        if ($failed_count > 0) {
            echo "🚨 ACTIONS URGENTES:\n";
            echo "===================\n";
            
            $critical_actions = array();
            foreach ($this->connection_tests as $test) {
                if ($test['status'] === 'FAILED') {
                    $critical_actions[] = "Corriger: {$test['test']}";
                }
            }
            
            foreach ($critical_actions as $i => $action) {
                echo ($i + 1) . ". $action\n";
            }
            echo "\n";
        }
        
        // Conseils de maintenance
        echo "🔧 CONSEILS DE MAINTENANCE:\n";
        echo "==========================\n";
        echo "1. 📅 Testez la connexion DB hebdomadairement\n";
        echo "2. 💾 Sauvegardez régulièrement la base de données\n";
        echo "3. 🧹 Optimisez les tables mensuellement\n";
        echo "4. 📊 Surveillez les performances de requêtes\n";
        echo "5. 🔐 Changez les mots de passe DB périodiquement\n\n";
        
        // Contact expert si problèmes critiques
        if ($failed_count > 0) {
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "🆘 PROBLÈME CRITIQUE BASE DE DONNÉES DÉTECTÉ !\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "🔧 Résolution experte recommandée immédiatement\n";
            echo "⚡ Intervention d'urgence sous 6h maximum\n";
            echo "🏆 800+ bases de données WordPress réparées\n";
            echo "✅ Garantie \"Problème résolu ou remboursé\"\n";
            echo "📞 Service professionnel: https://teddywp.com/depannage-wordpress/\n";
            echo "🛡️  Expert WordPress certifié - 12+ années d'expérience\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        }
        
        echo "\n👨‍💻 Test réalisé par Teddy - Expert WordPress\n";
        echo "🌐 TeddyWP.com | 📧 Support base de données 24/7\n";
        echo "📅 " . date('Y-m-d H:i:s') . " | Version script: 2.0\n";
    }
    
    /**
     * Formatage des tailles
     */
    private function formatBytes($size, $precision = 2) {
        if ($size == 0) return '0 B';
        
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $base = log($size, 1024);
        
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[floor($base)];
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
        echo "Usage: php test-db-connection.php [chemin-wordpress]\n";
        exit(1);
    }
}

// Lancement du test
try {
    $tester = new WordPressDatabaseTester($wordpress_path);
    $tester->runDatabaseTest();
} catch (Exception $e) {
    echo "❌ Erreur fatale: " . $e->getMessage() . "\n";
    echo "📞 Support expert: https://teddywp.com/depannage-wordpress/\n";
    exit(1);
}

echo "\n🏁 Test de base de données terminé !\n";
echo "💡 Consultez le rapport pour les actions recommandées\n";
echo "📞 Besoin d'aide ? Expert WordPress disponible 24/7\n";
?>
