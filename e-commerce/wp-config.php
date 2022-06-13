<?php
/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en « wp-config.php » et remplir les
 * valeurs.
 *
 * Ce fichier contient les réglages de configuration suivants :
 *
 * Réglages MySQL
 * Préfixe de table
 * Clés secrètes
 * Langue utilisée
 * ABSPATH
 *
 * @link https://fr.wordpress.org/support/article/editing-wp-config-php/.
 *
 * @package WordPress
 */

// For optimisation purpose, disable cron
// then use orther tool to run them :
// curl -vsS 'https://web-agency.local.dev/e-commerce/wp-cron.php?doing_wp_cron'

// HIGH opti level :
define('DISABLE_WP_CRON', true);

// Medium opti level :
define('WP_AUTO_UPDATE_CORE', false);
define('AUTOMATIC_UPDATER_DISABLED', true);

// https://www.inmotionhosting.com/support/edu/wordpress/wordpress-changing-the-site-url-and-home-settings/
define('RELOCATE',true);
define('WP_HOME','https://web-agency.local.dev/e-commerce');
define('WP_SITEURL','https://web-agency.local.dev/e-commerce');
// define('WPLANG','fr_FR'); // obsolète depuis la version 4.0.0 !

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define( 'DB_NAME', 'WebAgency_eCommerce' );
// FOR MAINTENANCE PURPOSE, you can change db name to avoid concurrency
// between your maintenances scripts and some external users
// define( 'DB_NAME', 'wrong-one-to-keep-db-import-safe' );

/** Utilisateur de la base de données MySQL. */
define( 'DB_USER', 'root' );

/** Mot de passe de la base de données MySQL. */
define( 'DB_PASSWORD', 'root' );

/** Adresse de l’hébergement MySQL. */
define( 'DB_HOST', '127.0.0.1:8889' );

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/**
 * Type de collation de la base de données.
 * N’y touchez que si vous savez ce que vous faites.
 */
define( 'DB_COLLATE', '' );

/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clés secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'G$=uX@|oTnKT ~e+V>Pt 0f11C/mcJ!x(6eoI^1VMJc^hGx=mqw4&ZG)$|#pa>|K');
define('SECURE_AUTH_KEY',  'dSSG_25VyF!cH2vl>g{lBRjF_hJXU$thp[g*O]`*9u|J7!v0$X0uD:K)@y-^^579');
define('LOGGED_IN_KEY',    'sDe-zN,JXu+3&TVFe1AVY,t-xIt^:+Ok#n*+&kSuSZ*tl (rlCg~LnX.&n]=b4np');
define('NONCE_KEY',        't_[;qmxa&_~78MLUdME@Y|10&@,P!<lam$C?65eA x3:TZm>Cdr795kjmgk_qd_d');
define('AUTH_SALT',        '=dfYsGlWH&n[3v-%{aD,!yeX,|Z%SbU&p[j>|n,Z1dtLQc53icA()C&6?<23PYhb');
define('SECURE_AUTH_SALT', '-vs_~;@FMQ`+B,*ov:R~FUI$>sg,?oUzWb5k|YrY1_IcNh}1mDVbW4YuE1qciy%A');
define('LOGGED_IN_SALT',   'qSuBn?N61 h5v5 17aew]]u? =<]gv#l!JiqN-+|lJ(^<)oq8XfU!+7*Cm+{C(8]');
define('NONCE_SALT',       'zGq^4Iu$ABI/Kc?h8|x{tD<0fJ5Z%yVNF3pY}2`iybU66&ulJ|z{:o!HFan`SI,X');
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix = 'wp_';

/**
 * Pour les développeurs : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortement recommandé que les développeurs d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur le Codex.
 *
 * @link https://fr.wordpress.org/support/article/debugging-in-wordpress/
 */
// https://kinsta.com/fr/base-de-connaissances/wordpress-logs-erreurs-acces/#raw-wordpress-logs
define( 'WA_Config_SHOULD_DEBUG', false );
// define( 'WA_Config_SHOULD_DEBUG', true );
// define( 'WA_Config_SHOULD_DEBUG', [ true, true, false ] );

define( 'WP_DEBUG', !! WA_Config_SHOULD_DEBUG );
define( 'WP_DEBUG_LOG', !! WA_Config_SHOULD_DEBUG ); // wp-content/debug.log
define( 'SAVEQUERIES', !! WA_Config_SHOULD_DEBUG ); // https://fr.wordpress.org/plugins/debug-bar/

/* C’est tout, ne touchez pas à ce qui suit ! Bonne publication. */

/** Chemin absolu vers le dossier de WordPress. */
if ( ! defined( 'ABSPATH' ) )
  define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once( ABSPATH . 'wp-settings.php' );
