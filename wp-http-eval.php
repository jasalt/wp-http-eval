<?php
/*
Plugin Name: WP HTTP Eval
Description: Special plugin for administrative tasks. This probably should be removed if it's left laying around.
Version: 0.1
Author: jasalt
Author URI: https://codeberg.org/jasalt
*/

require_once("admin-widget.php");

use Phel\Phel;
use Phel\Lang\Registry;
use Phel\Shared\CompilerConstants;
use Phel\Shared\ReplConstants;

// loadAllPhelNamespaces from:
// https://github.com/phel-lang/phel-lang/blob/925ea02f4d9b4960071b06e5e8a24f4ae9ff2932/src/php/Run/Infrastructure/Command/ReplCommand.php#L132
function loadAllPhelNamespaces($rf){
	$startupFile = __DIR__ . "/vendor/phel-lang/phel-lang/src/php/Run/Domain/Repl/startup.phel";
	$namespace = $rf->getNamespaceFromFile($startupFile)
					->getNamespace();

	$srcDirectories = [
		dirname($startupFile),
		...$rf->getAllPhelDirectories(),
	];
	$namespaceInformation = $rf->getDependenciesForNamespace(
		$srcDirectories,
		[$namespace, 'phel\\core'],
	);

	foreach ($namespaceInformation as $info) {
		$rf->evalFile($info);
	}

	// Ugly Hack: Set source directories for the repl
	Registry::getInstance()->addDefinition('phel\\repl', 'src-dirs', $srcDirectories);

}

if (isset($PHP_SELF) && $PHP_SELF !== "./vendor/bin/phel"){
    add_action('rest_api_init', function() {
        register_rest_route('wp-http-eval/v1', '/eval', [
            'methods' => 'POST',
            'callback' => function(WP_REST_Request $request) {
                $host = parse_url(home_url(), PHP_URL_HOST);
                if (!is_ssl() && !in_array($host, ['localhost', '127.0.0.1']) && !str_ends_with($host, '.test')) {
                    return new WP_Error('https_required', 'Requests must be made over HTTPS', ['status' => 403]);
                }

                $projectRootDir = __DIR__ . '/';
                require $projectRootDir . 'vendor/autoload.php';

				Phel::bootstrap($projectRootDir);

                $input = $request->get_body();
                $opts = new \Phel\Compiler\Infrastructure\CompileOptions;
                $rf = new \Phel\Run\RunFacade;

				// https://github.com/phel-lang/phel-lang/blob/925ea02f4d9b4960071b06e5e8a24f4ae9ff2932/src/php/Run/Infrastructure/Command/ReplCommand.php#L105

				loadAllPhelNamespaces($rf);

				Registry::getInstance()->addDefinition(
					CompilerConstants::PHEL_CORE_NAMESPACE,
					ReplConstants::REPL_MODE,
					true,
				);

                try {
                    $result = $rf->eval($input, $opts);
                    return ['success' => true, 'result' => $result];
                } catch (Exception $e) {
                    return new WP_Error('phel_error', $e->getMessage(), ['status' => 400]);
                }
            },
            'permission_callback' => function(WP_REST_Request $request) {
                $auth_token = $request->get_header('X-WP-HTTP-EVAL-TOKEN');
                return defined('WP_HTTP_EVAL_TOKEN') && $auth_token === WP_HTTP_EVAL_TOKEN;
            }
        ]);
    });
} else {
	// Don't re-initialize Phel or run main namespace outside regular web request
	// context e.g. when starting REPL session or running as WP-CLI command.
	print("Skip running phel-wp-plugin\main outside web request context.\n");
}

/*
 * Register WP-CLI command 'wp phel' running Phel namespace at `src/cli.phel`
 * https://make.wordpress.org/cli/handbook/guides/commands-cookbook/
 */
// if ( class_exists( 'WP_CLI' ) ) {
// 	WP_CLI::add_command( 'phel',
// 						 function ( $args ){
// 							 $projectRootDir = __DIR__ . '/';
// 							 require $projectRootDir . 'vendor/autoload.php';

// 							 Phel::run($projectRootDir, 'phel-wp-plugin\cli');
// 							 WP_CLI::success( "done!" );
// 						 }, ['shortdesc' => 'Runs Phel code as WP-CLI command']);
// }
