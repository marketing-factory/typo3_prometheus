#!/usr/bin/env bash

trap 'cleanUp;exit 2' SIGINT

cleanUp() {
    ATTACHED_CONTAINERS=$(${CONTAINER_BIN} ps --filter network=${NETWORK} --format='{{.Names}}')
    for ATTACHED_CONTAINER in ${ATTACHED_CONTAINERS}; do
        ${CONTAINER_BIN} rm -f ${ATTACHED_CONTAINER} >/dev/null
    done
    ${CONTAINER_BIN} network rm ${NETWORK} >/dev/null
}

cleanCacheFiles() {
    echo -n "Clean caches ... "
    rm -rf \
        .Build/.cache \
        .php-cs-fixer.cache
    echo "done"
}

loadHelp() {
    read -r -d '' HELP <<EOF
EXT:prometheus test runner. Check code styles, lint PHP files and run static analysis.

Usage: $0 [options] [file]

Options:
    -s <...>
        Specifies which test suite to run
            - cgl: cgl test and fix all php files
            - clean: Clean temporary files
            - cleanCache: Clean cache folders
            - composer: "composer" with all remaining arguments dispatched.
            - composerNormalize: "composer normalize"
            - composerUpdate: "composer update", handy if host has no PHP
            - composerValidate: "composer validate"
            - lint: PHP linting
            - phpstan: PHPStan static analysis
            - phpstanBaseline: Generate PHPStan baseline
            - rector: Apply Rector rules

    -b <docker|podman>
        Container environment:
            - docker
            - podman

        If not specified, podman will be used if available. Otherwise, docker is used.

    -p <8.3|8.4|8.5>
        Specifies the PHP minor version to be used
            - 8.3: use PHP 8.3
            - 8.4: use PHP 8.4
            - 8.5: use PHP 8.5

    -n
        Only with -s cgl, composerNormalize, rector
        Activate dry-run in CGL check and composer normalize that does not actively change files and only prints broken ones.

    -u
        Update existing typo3/core-testing-*:latest container images and remove dangling local volumes.

    -h
        Show this help.

Examples:
    # Run PHP linting using PHP 8.3
    ./Build/Scripts/runTests.sh -p 8.3 -s lint

    # Run PHPStan using PHP 8.3
    ./Build/Scripts/runTests.sh -p 8.3 -s phpstan

    # Run CGL dry-run using PHP 8.3
    ./Build/Scripts/runTests.sh -p 8.3 -s cgl -n
EOF
}

# Test if docker exists, else exit out with error
if ! type "docker" >/dev/null 2>&1 && ! type "podman" >/dev/null 2>&1; then
    echo "This script relies on docker or podman. Please install" >&2
    exit 1
fi

# Option defaults
TEST_SUITE="cgl"
PHP_VERSION="8.3"
DRY_RUN=0
CI_PARAMS="${CI_PARAMS:-}"
CONTAINER_BIN=""
CONTAINER_HOST="host.docker.internal"

# Option parsing
OPTIND=1
INVALID_OPTIONS=()
while getopts "b:s:p:nhu" OPT; do
    case ${OPT} in
        s)
            TEST_SUITE=${OPTARG}
            ;;
        b)
            if ! [[ ${OPTARG} =~ ^(docker|podman)$ ]]; then
                INVALID_OPTIONS+=("${OPTARG}")
            fi
            CONTAINER_BIN=${OPTARG}
            ;;
        p)
            PHP_VERSION=${OPTARG}
            if ! [[ ${PHP_VERSION} =~ ^(8.3|8.4|8.5)$ ]]; then
                INVALID_OPTIONS+=("p ${OPTARG}")
            fi
            ;;
        n)
            DRY_RUN=1
            ;;
        h)
            loadHelp
            echo "${HELP}"
            exit 0
            ;;
        u)
            TEST_SUITE=update
            ;;
        \?)
            INVALID_OPTIONS+=("${OPTARG}")
            ;;
        :)
            INVALID_OPTIONS+=("${OPTARG}")
            ;;
    esac
done

# Exit on invalid options
if [ ${#INVALID_OPTIONS[@]} -ne 0 ]; then
    echo "Invalid option(s):" >&2
    for I in "${INVALID_OPTIONS[@]}"; do
        echo "-"${I} >&2
    done
    echo >&2
    echo "call \".Build/Scripts/runTests.sh -h\" to display help and valid options"
    exit 1
fi

COMPOSER_ROOT_VERSION="14.0.x-dev"
HOST_UID=$(id -u)
USERSET=""
if [ $(uname) != "Darwin" ]; then
    USERSET="--user $HOST_UID"
fi

THIS_SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" >/dev/null && pwd)"
cd "$THIS_SCRIPT_DIR" || exit 1
cd ../../ || exit 1
ROOT_DIR="${PWD}"

mkdir -p .Build/.cache

IMAGE_PREFIX="docker.io/"
TYPO3_IMAGE_PREFIX="ghcr.io/typo3/"
CONTAINER_INTERACTIVE="-it --init"

IS_CORE_CI=0
if [ "${CI}" == "true" ]; then
    IS_CORE_CI=1
    IMAGE_PREFIX=""
    CONTAINER_INTERACTIVE=""
fi

if [[ -z "${CONTAINER_BIN}" ]]; then
    if type "podman" >/dev/null 2>&1; then
        CONTAINER_BIN="podman"
    elif type "docker" >/dev/null 2>&1; then
        CONTAINER_BIN="docker"
    fi
fi

IMAGE_PHP="${TYPO3_IMAGE_PREFIX}core-testing-$(echo "php${PHP_VERSION}" | sed -e 's/\.//'):latest"
IMAGE_ALPINE="${IMAGE_PREFIX}alpine:3.8"

shift $((OPTIND - 1))

SUFFIX=$(echo $RANDOM)
NETWORK="t3prometheus-${SUFFIX}"
${CONTAINER_BIN} network create ${NETWORK} >/dev/null

if [ ${CONTAINER_BIN} = "docker" ]; then
    CONTAINER_COMMON_PARAMS="${CONTAINER_INTERACTIVE} --rm --network ${NETWORK} --add-host "${CONTAINER_HOST}:host-gateway" ${USERSET} -v ${ROOT_DIR}:${ROOT_DIR} -w ${ROOT_DIR}"
else
    CONTAINER_HOST="host.containers.internal"
    CONTAINER_COMMON_PARAMS="${CONTAINER_INTERACTIVE} ${CI_PARAMS} --rm --network ${NETWORK} -v ${ROOT_DIR}:${ROOT_DIR} -w ${ROOT_DIR}"
fi

# Suite execution
case ${TEST_SUITE} in
    cgl)
        DRY_RUN_OPTIONS=''
        if [ "${DRY_RUN}" -eq 1 ]; then
            DRY_RUN_OPTIONS='--dry-run --diff'
        fi
        COMMAND="php -dxdebug.mode=off .Build/bin/php-cs-fixer fix -v ${DRY_RUN_OPTIONS} --config=.php-cs-fixer.dist.php --using-cache=no"
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name cgl-${SUFFIX} -e COMPOSER_CACHE_DIR=.Build/.cache/composer -e COMPOSER_ROOT_VERSION=${COMPOSER_ROOT_VERSION} ${IMAGE_PHP} /bin/sh -c "${COMMAND}"
        SUITE_EXIT_CODE=$?
        ;;
    clean)
        cleanCacheFiles
        ;;
    cleanCache)
        cleanCacheFiles
        ;;
    composer)
        COMMAND=(composer "$@")
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name composer-command-${SUFFIX} -e COMPOSER_CACHE_DIR=.Build/.cache/composer -e COMPOSER_ROOT_VERSION=${COMPOSER_ROOT_VERSION} ${IMAGE_PHP} "${COMMAND[@]}"
        SUITE_EXIT_CODE=$?
        ;;
    composerNormalize)
        if [ "${DRY_RUN}" -eq 1 ]; then
            COMMAND=(composer normalize -n)
        else
            COMMAND=(composer normalize)
        fi
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name composer-command-${SUFFIX} -e COMPOSER_CACHE_DIR=.Build/.cache/composer -e COMPOSER_ROOT_VERSION=${COMPOSER_ROOT_VERSION} ${IMAGE_PHP} "${COMMAND[@]}"
        SUITE_EXIT_CODE=$?
        ;;
    composerUpdate)
        rm -rf .Build/bin .Build/vendor ./composer.lock
        cp ${ROOT_DIR}/composer.json ${ROOT_DIR}/composer.json.orig
        if [ -f "${ROOT_DIR}/composer.json.testing" ]; then
            cp ${ROOT_DIR}/composer.json ${ROOT_DIR}/composer.json.orig
        fi
        COMMAND=(composer require --no-ansi --no-interaction --no-progress)
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name composer-install-${SUFFIX} -e COMPOSER_CACHE_DIR=.Build/.cache/composer -e COMPOSER_ROOT_VERSION=${COMPOSER_ROOT_VERSION} ${IMAGE_PHP} "${COMMAND[@]}"
        SUITE_EXIT_CODE=$?
        cp ${ROOT_DIR}/composer.json ${ROOT_DIR}/composer.json.testing
        mv ${ROOT_DIR}/composer.json.orig ${ROOT_DIR}/composer.json
        ;;
    composerValidate)
        COMMAND=(composer validate "$@")
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name composer-command-${SUFFIX} -e COMPOSER_CACHE_DIR=.Build/.cache/composer -e COMPOSER_ROOT_VERSION=${COMPOSER_ROOT_VERSION} ${IMAGE_PHP} "${COMMAND[@]}"
        SUITE_EXIT_CODE=$?
        ;;
    lint)
        COMMAND="find . -name \\*.php ! -path \"./.Build/*\" -print0 | xargs -0 -n1 -P4 php -dxdebug.mode=off -l >/dev/null"
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name lint-${SUFFIX} -e COMPOSER_CACHE_DIR=.Build/.cache/composer -e COMPOSER_ROOT_VERSION=${COMPOSER_ROOT_VERSION} ${IMAGE_PHP} /bin/sh -c "${COMMAND}"
        SUITE_EXIT_CODE=$?
        ;;
    phpstan)
        COMMAND="php -dxdebug.mode=off .Build/bin/phpstan --configuration=Build/phpstan/phpstan.neon"
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name phpstan-${SUFFIX} -e COMPOSER_CACHE_DIR=.Build/.cache/composer -e COMPOSER_ROOT_VERSION=${COMPOSER_ROOT_VERSION} ${IMAGE_PHP} /bin/sh -c "${COMMAND}"
        SUITE_EXIT_CODE=$?
        ;;
    phpstanBaseline)
        COMMAND="php -dxdebug.mode=off .Build/bin/phpstan --configuration=Build/phpstan/phpstan.neon --generate-baseline=Build/phpstan/phpstan-baseline.neon -v"
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name phpstan-${SUFFIX} -e COMPOSER_CACHE_DIR=.Build/.cache/composer -e COMPOSER_ROOT_VERSION=${COMPOSER_ROOT_VERSION} ${IMAGE_PHP} /bin/sh -c "${COMMAND}"
        SUITE_EXIT_CODE=$?
        ;;
    rector)
        DRY_RUN_OPTIONS=''
        if [ "${DRY_RUN}" -eq 1 ]; then
            DRY_RUN_OPTIONS='--dry-run'
        fi
        COMMAND="php -dxdebug.mode=off .Build/bin/rector process ${DRY_RUN_OPTIONS} --config=Build/rector/rector.php --no-progress-bar --ansi"
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name rector-${SUFFIX} -e COMPOSER_CACHE_DIR=.Build/.cache/composer -e COMPOSER_ROOT_VERSION=${COMPOSER_ROOT_VERSION} ${IMAGE_PHP} /bin/sh -c "${COMMAND}"
        SUITE_EXIT_CODE=$?
        ;;
    update)
        echo "> pull ${TYPO3_IMAGE_PREFIX}core-testing-* versions of those ones that exist locally"
        ${CONTAINER_BIN} images "${TYPO3_IMAGE_PREFIX}core-testing-*" --format "{{.Repository}}:{{.Tag}}" | xargs -I {} ${CONTAINER_BIN} pull {}
        echo ""
        echo "> remove \"dangling\" ${TYPO3_IMAGE_PREFIX}/core-testing-* images (those tagged as <none>)"
        ${CONTAINER_BIN} images --filter "reference=${TYPO3_IMAGE_PREFIX}/core-testing-*" --filter "dangling=true" --format "{{.ID}}" | xargs -I {} ${CONTAINER_BIN} rmi -f {}
        echo ""
        ;;
    *)
        loadHelp
        echo "Invalid -s option argument ${TEST_SUITE}" >&2
        echo >&2
        echo "${HELP}" >&2
        exit 1
        ;;
esac

cleanUp

echo "" >&2
echo "###########################################################################" >&2
echo "Result of ${TEST_SUITE}" >&2
echo "Container runtime: ${CONTAINER_BIN}" >&2
if [[ ${IS_CORE_CI} -eq 1 ]]; then
    echo "Environment: CI" >&2
else
    echo "Environment: local" >&2
fi
echo "PHP: ${PHP_VERSION}" >&2
if [[ ${SUITE_EXIT_CODE} -eq 0 ]]; then
    echo "SUCCESS" >&2
else
    echo "FAILURE" >&2
fi
echo "###########################################################################" >&2
echo "" >&2

exit $SUITE_EXIT_CODE
