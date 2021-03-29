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
        'userid': 2,
        'testprojectid': 1,
        'rolename': 'leader'}

print "Set role leader to a user..."
res = server.tl.setUserRoleOnProject(args)

if res == True:
    print "The role %s is granted to the user %s on the project %s."%(args['rolename'], args['userid'], args['testprojectid'])
else:
    print "Something's wrong: "
    for err in res:
        print err['message']

args = {'devKey': devKey,
        'userid': 9999,
        'testprojectid': 1,
        'rolename': 'leader'}

print "Set role leader to a non existing user..."
res = server.tl.setUserRoleOnProject(args)

if res == True:
    print "The role %s is granted to the user %s on the project %s."%(args['rolename'], args['userid'], args['testprojectid'])
else:
    print "Something's wrong: "
    for err in res:
        print err['message']
