#!/bin/bash
#
# modules.sh
#
# find all drupal modules and themes and package them.

# Ensure a non zero exit value to break the build procedure.
set -e

for module in drupal-*
do
    ./module.sh $module
done