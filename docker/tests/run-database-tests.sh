#!/usr/bin/env bash
set -euo pipefail

# Linux: host networking reaches MySQL even when it listens on loopback only.
# Credentials are forwarded from the environment, never baked into the image.
: "${TEST_DB_PASSWORD:?Set TEST_DB_PASSWORD to the dedicated test database password}"
export TEST_DB_HOST="${TEST_DB_HOST:-127.0.0.1}"
export TEST_DB_PORT="${TEST_DB_PORT:-3306}"
export TEST_DB_USERNAME="${TEST_DB_USERNAME:-root}"
export TEST_DB_DATABASE=supla_scripts_test
export TEST_DB_RESET=supla_scripts_test

cd "$(dirname "$0")/../.."
echo 'Rebuilding all tables in supla_scripts_test; no other database is permitted.'
docker build --target database-tests -f docker/tests/Dockerfile -t supla-scripts-database-tests .
docker run --rm --network host --read-only --tmpfs /tmp --tmpfs /app/var \
    -e TEST_DB_HOST -e TEST_DB_PORT -e TEST_DB_USERNAME -e TEST_DB_PASSWORD \
    -e TEST_DB_DATABASE -e TEST_DB_RESET \
    supla-scripts-database-tests "$@"
