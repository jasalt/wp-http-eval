<?php
/*
Plugin Name: WP HTTP Eval
Description: Special plugin for administrative tasks. This probably should be removed if it's left laying around.
Version: 0.1
Author: jasalt
Author URI: https://codeberg.org/jasalt
*/

use Phel\Phel;

if (isset($PHP_SELF) && $PHP_SELF !== "./vendor/bin/phel"){
    add_action('rest_api_init', function() {
        register_rest_route('wp-http-eval/v1', '/eval', [
            'methods' => 'POST',
            'callback' => function(WP_REST_Request $request) {
                $projectRootDir = __DIR__ . '/';
                require $projectRootDir . 'vendor/autoload.php';

				Phel::bootstrap($projectRootDir);

                $input = $request->get_body();
                $opts = new \Phel\Compiler\Infrastructure\CompileOptions;
                $rf = new \Phel\Run\RunFacade;
				$rf->runNamespace('phel\\core');

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
