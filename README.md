# Installation

After activating the plugin, setup token in `wp-config.php` with `define('WP_HTTP_EVAL_TOKEN', 'secret123');`.

To enable the admin dashboard widget for evaluating Phel code, also add:
`define('WP_HTTP_EVAL_WIDGET', true);`

The widget provides a convenient interface for administrators to evaluate Phel expressions directly from the WordPress dashboard, with support for Ctrl+Enter keyboard shortcut.

## Development container setup

Start a bare bones WordPress installation with the plugin installed by running `docker compose up` in this directory.

## Installing as plugin on existing WordPress site

To populate the `vendor/` path, `composer install` needs to be run first. After this the repository can be placed in `wp-content/plugins/` directory or zip can be created from the repository folder for installing on a site.

There's some ceaveats to the Composer autoloader in plugins as explained in [phel-wp-plugin](https://github.com/jasalt/phel-wp-plugin) readme which need to be considered if the same Composer dependencies are used in separate places (plugins or theme).

# API client examples

Examples are written using the development container setup exposing WordPress installation at http://localhost:8081

## Curl

```
curl -X POST -H "X-WP-HTTP-EVAL-TOKEN: secret123" -H "Content-Type: text/plain" --data "(+ 1 2 3)" http://localhost:8081/wp-json/wp-http-eval/v1/eval

{"success":true,"result":6}
```

Should be only ever be used over https and it's enforced with domains other than localhost and ones ending with `.test`.

Requiring Phel namespaces is possible also:
```
curl -X POST -H "X-WP-HTTP-EVAL-TOKEN: secret123" -H "Content-Type: text/plain" --data "(require phel\html)(html/html [:p \"foo\"])" http://localhost:8081/wp-json/wp-http-eval/v1/eval

{"success":true,"result":"<p>foo<\/p>"}
```

## Phel PHP HTTP client example

With Phel installed either as Phar named `phel` in `PATH` or via Composer where it's callable via `vendor/bin/phel` at the current directory, example HTTP client code is included at `client-example/client.phel` which can be run as follows:

```
WP_HTTP_EVAL_TOKEN=secret123 WP_HTTP_EVAL_HOST=http://localhost:8081 phel run client-example/client.phel

Requesting http://localhost:8081/wp-json/wp-http-eval/v1/eval
Response: {"success":true,"result":"<h1>Requested WP backend at 8de8a50072c7<\/h1>"}
```

# TODO
- Remove other admin widgets
- Enable admin widget based on PHP constant in wp-config.php
