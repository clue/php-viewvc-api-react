# Changelog

## 0.4.0 (2016-04-16)

* Feature: Add streaming API for fetching larger files
  (#20 by @clue)

  ```php
  $stream = $client->fetchFileStream($path);
  
  $stream->on('data', function ($chunk) {
      echo $chunk;
  });
  
  $stream->on('error', function (Exception $error) {
      echo 'Error: ' . $error->getMessage() . PHP_EOL;
  });
  
  $stream->on('close', function () {
      echo '[DONE]' . PHP_EOL;
  });
  ```

* Update dependencies, more SOLID code base

## 0.3.0 (2015-09-04)

* Feature / BC break: Build properly escaped URIs via Browser
  ([#17](https://github.com/clue/php-viewvc-api-react/pull/17))

  ```php
  // old
  $client = new Client($uri, $browser);
  
  // new
  $client = new Client($browser->withBase($uri));
  ```

* Update dependencies, more SOLID code base

## 0.2.0 (2015-04-10)

* Feature: Add `Client::fetchLog()` method
  ([#14](https://github.com/clue/php-viewvc-api-react/pull/14))

* Feature: Support including deleted files (attic) in CVS directory listings
  ([#11](https://github.com/clue/php-viewvc-api-react/pull/11))

* Feature: Support mixed UTF-8 and ISO-8859-1 (Latin1) encodings
  ([#12](https://github.com/clue/php-viewvc-api-react/pull/12))

* Fix: Improve parsing broken XHTML structure
  ([#10](https://github.com/clue/php-viewvc-api-react/pull/10), [#13](https://github.com/clue/php-viewvc-api-react/pull/13))

## 0.1.1 (2015-04-07)

* Fix: Accessing deleted files in older revisions now works
  ([#6](https://github.com/clue/php-viewvc-api-react/pull/6))

* Fix: Improve parsing XHTML structure
  ([#7](https://github.com/clue/php-viewvc-api-react/pull/7))

* Fix: Add trailing slash to base URL
  ([#5](https://github.com/clue/php-viewvc-api-react/pull/5))

* Improve test suite and directory listing example
  ([#8](https://github.com/clue/php-viewvc-api-react/pull/8), [#9](https://github.com/clue/php-viewvc-api-react/pull/9))

## 0.1.0 (2015-04-06)

* First tagged release
