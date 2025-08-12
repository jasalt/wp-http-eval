# Installation

After activating the plugin, setup token in `wp-config.php` with `define('WP_HTTP_EVAL_TOKEN', 'secret123');`.

## Curl client example

```
curl -X POST -H "X-WP-HTTP-EVAL-TOKEN: secret123" -H "Content-Type: text/plain" --data "(+ 1 2 3)" http://example.test/wp-json/wp-http-eval/v1/eval
{"success":true,"result":6}
```

Should be only ever be used over https and it's enforced with domains other than localhost and ones ending with `.test`.

Requiring Phel namespaces is possible also:
```
curl -X POST -H "X-WP-HTTP-EVAL-TOKEN: secret123" -H "Content-Type: text/plain" --data "(require phel\html)(html/html [:p \"foo\"])" http://example.test/wp-json/wp-http-eval/v1/eval
{"success":true,"result":"<p>foo<\/p>"}
```

## Phel PHP HTTP client example

With Phel installed either as Phar named `phel` in `PATH` or via Composer where it's callable via `vendor/bin/phel` at the current directory, example HTTP client code is included at `client-example/client.phel` which can be run as follows:

```
WP_HTTP_EVAL_TOKEN=secret123 WP_HTTP_EVAL_HOST=http://example.test phel run client.phel
Requesting http://example.test/wp-json/wp-http-eval/v1/eval
Response: {"success":true,"result":"<h1>Requested WP backend at vvv<\/h1>"}
```
