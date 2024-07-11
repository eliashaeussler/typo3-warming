#!/usr/bin/env bash
# shellcheck disable=SC2155,SC2059
set -e

readonly artifactFile=".Build/vendor/typo3/PackageArtifact.php"
readonly tempFile="$(mktemp)"

CYAN="\e[36m"
YELLOW="\e[43m"
NC="\e[0m"

printf "${YELLOW}Running script to fix invalid package path in PackageArtifact.${NC}\n"
printf "${CYAN}Please remove this script once https://review.typo3.org/c/Packages/TYPO3.CMS/+/85212 is released.${NC}\n"

awk '{ gsub(/packagePath";s:2:"\/\/"/, "packagePath\";s:0:\"\""); print }' "$artifactFile" > "$tempFile" \
    && mv "$tempFile" "$artifactFile"
