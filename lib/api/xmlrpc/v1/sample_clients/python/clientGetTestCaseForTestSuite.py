#!/usr/bin/python

# from www.teamst.org
#
import sys,xmlrpclib

URL = "http://YOURSERVER/testlink/lib/api/xmlrpc.php"
DEVKEY = "YOURKEY"
conn = xmlrpclib.Server(URL)

data = {}
data["devKey"] = DEVKEY
data["testsuiteid"] = "3"
data["deep"] = "true"
data["details"] = "full"
tcinfo = conn.tl.getTestCasesForTestSuite(data)

print tcinfo