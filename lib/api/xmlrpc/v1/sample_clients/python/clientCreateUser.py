#!/usr/bin/python

# for python 2
import xmlrpclib

serverName="YOURSERVER"

SERVER_URL = "https://%s.si.c-s.fr/lib/api/xmlrpc/v1/xmlrpc.php"

devKey="YOURAPIKEY"

server = xmlrpclib.Server(SERVER_URL%serverName)

print server.tl.about()

# check Key
if not (server.tl.checkDevKey({'devKey': devKey})):
    raise "Wrong key"

args = {'devKey': devKey,
        'login': 'uapi',
        'firstname': 'User',
        'lastname': 'API',
        'email': 'user.api@your.domain.org',
        'password': 'yourpwd'}

print "Create user with password..."
userID = server.tl.createUser(args)

if isinstance(userID, str):
    print "User %s created."%userID
else:
    print "Something's wrong: "
    for err in userID:
        print err['message']

args = {'devKey': devKey,
        'login': 'uapi2',
        'firstname': 'User',
        'lastname': 'API2',
        'email': 'user.api2@your.domain.org'}

print "Create user without password..."
userID = server.tl.createUser(args)

if isinstance(userID, str):
    print "User %s created."%userID
else:
    print "Something's wrong: "
    for err in userID:
        print err['message']

args = {'devKey': devKey,
        'login': 'uapi2',
        'firstname': 'User',
        'lastname': 'API2',
        'email': 'user.api2@your.domain.org'}

print "Create user with existing uid..."
userID = server.tl.createUser(args)

if isinstance(userID, str):
    print "User %s created."%userID
else:
    print "Something's wrong: "
    for err in userID:
        print err['message']
