# Unix
if [ "$#" -eq 1 ]
then 
	javac -classpath ./lib/xmlrpc-client-3.1.jar:./lib/xmlrpc-common-3.1.jar $1
else
	echo usage:$0 filename.java
fi