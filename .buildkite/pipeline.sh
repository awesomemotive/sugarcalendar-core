#!/bin/bash

# exit immediately on failure, or if an undefined variable is used
set -eu

# begin the pipeline.yml file
echo "steps:"

phpVersions=('7.0' '7.1' '7.2' '7.3' '7.4')
wpVersions=('5.2.7' '5.3.4' '5.4.2' '5.5.3' '5.6.3' 'latest')

# add a new command step to run the tests in each test directory
for phpVersion in ${phpVersions[@]}; do
  for wpVersion in ${wpVersions[@]}; do
    echo "  - env:"
    echo "      TEST_INPLACE: \"0\""
    echo "      TEST_PHP_VERSION: \""$phpVersion"\""
    echo "      TEST_WP_VERSION: "$wpVersion""
    echo "      WP_MULTISITE: \"0\""
    echo "    label: 'PHP: "$phpVersion" | WP: "$wpVersion" | Multisite: No'"
    echo "    agents:"
    echo "      queue: \"sc\""
    echo "    plugins:"
    echo "      - docker-compose#v3.7.0:"
    echo "          config: docker-compose-phpunit.yml"
    echo "          env:"
    echo "            - WP_MULTISITE"
    echo "            - TEST_INPLACE"
    echo "          propagate-uid-gid: true"
    echo "          pull-retries: 3"
    echo "          run: wordpress"
  done
done

echo "  - env:"
echo "      TEST_INPLACE: \"0\""
echo "      TEST_PHP_VERSION: \""$phpVersion"\""
echo "      TEST_WP_VERSION: "$wpVersion""
echo "      WP_MULTISITE: \"1\""
echo "    label: 'PHP: "$phpVersion" | WP: "$wpVersion" | Multisite: Yes'"
echo "    agents:"
echo "      queue: \"sc\""
echo "    plugins:"
echo "      - docker-compose#v3.7.0:"
echo "          config: docker-compose-phpunit.yml"
echo "          env:"
echo "            - WP_MULTISITE"
echo "            - TEST_INPLACE"
echo "          propagate-uid-gid: true"
echo "          pull-retries: 3"
echo "          run: wordpress"