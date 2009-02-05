#! /bin/sh

path=`dirname $0`
tests=`find $path/unit -iname '*.php'`

for f in $tests ; do
  php $f
done

