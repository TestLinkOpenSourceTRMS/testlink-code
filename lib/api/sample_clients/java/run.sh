#!/bin/sh
# Unix
if [ "$#" -eq 1 ]
then 
	java -classpath .:./lib/xmlrpc-client-3.1.jar:./lib/xmlrpc-common-3.1.jar:./lib/ws-commons-util-1.0.2.jar $1
else
	echo usage:$0 filename
fi
