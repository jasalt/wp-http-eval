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
	// TODO Initialize WP REST API endpoint receiving Phel code to be evaluated

	// Running Phel code works as follows:
	// $projectRootDir = __DIR__ . '/';
	// require $projectRootDir . 'vendor/autoload.php';
	// $opts = new \Phel\Compiler\Infrastructure\CompileOptions;
	// $rf = new \Phel\Run\RunFacade;
	// $result = $rf->eval($input, $opts);
	// return $result;


} else {
	// Don't re-initialize Phel or run main namespace outside regular web request
	// context e.g. when starting REPL session or running as WP-CLI command.
	print("Skip running phel-wp-plugin\main outside web request context.\n");
}

/*
 * Register WP-CLI command 'wp phel' running Phel namespace at `src/cli.phel`
 * https://make.wordpress.org/cli/handbook/guides/commands-cookbook/
 */
if ( class_exists( 'WP_CLI' ) ) {
	WP_CLI::add_command( 'phel',
						 function ( $args ){
							 $projectRootDir = __DIR__ . '/';
							 require $projectRootDir . 'vendor/autoload.php';

							 Phel::run($projectRootDir, 'phel-wp-plugin\cli');
							 WP_CLI::success( "done!" );
						 }, ['shortdesc' => 'Runs Phel code as WP-CLI command']);
}
