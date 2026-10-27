# url-builder
Builds URL's the way pecl_http does this.

Requires PHP 8.5+: parsing and building run on the native [`ext-uri`](https://www.php.net/manual/en/book.uri.php)
extension, with the query component handled as a PHP array via `parse_str()`/`http_build_query()`.

## Tests

```sh
docker compose run --rm test
```
