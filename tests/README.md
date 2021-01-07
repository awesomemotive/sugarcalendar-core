# Sugar Calendar Unit Tests [![Build status](https://badge.buildkite.com/80a91dff42e83fa09a0b68ed75b93c26920d619d46694d01c0.svg)](https://buildkite.com/sandhills-development-llc/sugar-calendar)


This folder contains all the tests for Sugar Calendar.

## Running Tests
### How to run the tests locally
The ideal way to run the unit tests locally is by using the included Docker Compose files for the stack and for PHPUnit.

Requirements:
- Docker (19.03.13 or newer)
- Docker Compose (1.25.5 or newer)

To run the tests, use the following command:
```
bin/run-tests-local.sh -p 7.4 -w latest
```

The flags `-p` and `-w` control the PHP and WordPress versions for the tests, respectively.

This command sends the `phpunit` command to the `wordpress_phpunit` container.

## Writing Tests
For more information on how to write PHPUnit Tests, see [PHPUnit's Website](http://www.phpunit.de/manual/3.6/en/writing-tests-for-phpunit.html).
