#!/bin/bash
#
# modules.sh
#
# find all drupal modules and themes and package them.

# Ensure a non zero exit value to break the build procedure.
set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

for module in drupal-*
do
    $DIR/module.sh $module
done