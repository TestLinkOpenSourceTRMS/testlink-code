#!/usr/bin/env ruby

# Testlink API Sample Ruby Client implementation
require 'xmlrpc/client'

class TestlinkAPIClient  
  # substitute your server URL Here
  SERVER_URL = "http://qa/testlink_sandbox/api/xmlrpc.php"
  
  def initialize(dev_key)
    @server = XMLRPC::Client.new2(SERVER_URL)
    @devKey = dev_key
  end
  
  def reportTCResult(tcid, tpid, status)
    args = {"devKey"=>@devKey, "tcid"=>tcid, "tpid"=>tpid, "status"=>status}
    @server.call("tl.reportTCResult", args)
  end
end

# substitute your Dev Key Here
client = TestlinkAPIClient.new("f2a979d533cdd9761434bba60a88e4d8")
# Substitute for tcid and tpid that apply to your project
result = client.reportTCResult(1132, 56646, "f")
# Typically you'd want to validate the result here and probably do something more useful with it
puts "result was: %s" %(result)