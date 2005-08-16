#!/bin/sh

CLASSNAME="Template"

LIBPATH="lib"

FILE=PHPLIB.php
cat $LIBPATH/$FILE | \
sed -e s/class\ Template_PHPLIB/class\ $CLASSNAME/ | \
sed -e s/function\ Template_PHPLIB/function\ $CLASSNAME/ | \
cat > foobar.tmp
mv foobar.tmp $LIBPATH/$FILE

for FILE in layersmenu.inc.php treemenu.inc.php plainmenu.inc.php
do
cat $LIBPATH/$FILE | \
sed -e s/Template_PHPLIB\(/$CLASSNAME\(/ > foobar.tmp
mv foobar.tmp $LIBPATH/$FILE
done

