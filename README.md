# Usage

First setup token in `wp-config.php` with `define('WP_HTTP_EVAL_TOKEN', 'secret123');` and evaluate with:

```
curl -X POST -H "X-WP-HTTP-EVAL-TOKEN: secret123" -H "Content-Type: text/plain" --data "(+ 1 2 3)" https://example.com/wp-json/wp-http-eval/v1/eval
```

Should be only ever be used over https and it's enforced with domains other than localhost and ones ending with `.test`.
