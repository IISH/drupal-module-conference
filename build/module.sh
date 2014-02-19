#!/bin/bash
#
# module-tar.gz.sh [instance]
#
# Package the module.

# Ensure a non zero exit value to break the build procedure.
set -e

module=$1
if [ -z "$module" ] ; then
	echo "Need a name of the module to package."
	exit -1
fi

if [ ! -d $module ] ; then
    echo "No such module: $module"
    exit -1
fi

cd $module

revision=$(git rev-parse HEAD)
target=target
file=$module.tar.gz
expect=target/$file
echo "Build $expect from $module, revision $revision"

# Remove previous builds.
if [ -d target ] ; then
	rm -r target
fi

mkdir -p target

tar --exclude=target --exclude=.git -zcvf $expect .
if [ -f $expect ] ; then
	echo "Done."
	exit 0
else
	echo "Packaging failed. No file found at $target."
	exit -1
fi
