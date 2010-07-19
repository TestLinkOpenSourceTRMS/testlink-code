#! /usr/bin/python
#
# Testlink API Sample Python 3.1.2 getProjects() - Client implementation
# 
import xmlrpc.client

class TestlinkAPIClient:        
    # substitute your server URL Here
    SERVER_URL = "http://localhost:8900/head-20100702/lib/api/xmlrpc.php"
    
    def __init__(self, devKey):
        self.server = xmlrpc.client.ServerProxy(self.SERVER_URL)
        self.devKey = devKey

    def getInfo(self):
        return self.server.tl.about()

    def getProjects(self):
        return self.server.tl.getProjects(dict(devKey=self.devKey))

# substitute your Dev Key Here
client = TestlinkAPIClient("CLIENTSAMPLEDEVKEY")

# get info about the server
print(client.getInfo())

print(client.getProjects())