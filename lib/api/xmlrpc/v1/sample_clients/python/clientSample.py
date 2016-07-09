#! /usr/bin/python
"""
Testlink API Sample Python 2.x Client implementation
"""
import xmlrpclib

class TestlinkAPIClient:        
    # substitute your server URL Here
    SERVER_URL = "http://qa/testlink_sandbox/api/xmlrpc.php"
    
    def __init__(self, devKey):
        self.server = xmlrpclib.Server(self.SERVER_URL)
        self.devKey = devKey

    def reportTCResult(self, tcid, tpid, status):
        data = {"devKey":self.devKey, "tcid":tcid, "tpid":tpid, "status":status}
        return self.server.tl.reportTCResult(data)

    def getInfo(self):
        return self.server.tl.about()

# substitute your Dev Key Here
client = TestlinkAPIClient("f2a979d533cdd9761434bba60a88e4d8")
# get info about the server
print client.getInfo()
# Substitute for tcid and tpid that apply to your project
result = client.reportTCResult(1132, 56646, "p")
# Typically you'd want to validate the result here and probably do something more useful with it
print "reportTCResult result was: %s" %(result)