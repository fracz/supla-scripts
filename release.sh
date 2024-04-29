#!/usr/bin/env bash

# example command to release: ./release.sh

cd "$(dirname "$0")"

#export BUILDKIT_PROGRESS=plain

DOCKER_BUILDKIT=1 docker build --file release.Dockerfile --output . .
