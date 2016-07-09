package org.testlink.api.client.sample;

import java.net.MalformedURLException;
import java.net.URL;
import java.util.ArrayList;
import java.util.Hashtable;
import java.util.Map;

import org.apache.xmlrpc.XmlRpcException;
import org.apache.xmlrpc.client.XmlRpcClient;
import org.apache.xmlrpc.client.XmlRpcClientConfigImpl;

public class TestlinkAPIXMLRPCClient 
{		
	// Substitute your Dev Key Here
	public static final String DEV_KEY =  "f2a979d533cdd9761434bba60a88e4d8";
	// Substitute your Server URL Here
	public static final String SERVER_URL = "http://qa/testlink_sandbox/api/xmlrpc.php";	
	
	/**
	 * report test execution to TestLink API
	 * 
	 * @param int tcid
	 * @param int tpid
	 * @param String status
	 */
	public static void testLinkReport(int tcid, int tpid, String status)
	{
		try 
		{
			XmlRpcClient rpcClient;
			XmlRpcClientConfigImpl config;
			
			config = new XmlRpcClientConfigImpl();
			config.setServerURL(new URL(SERVER_URL));
			rpcClient = new XmlRpcClient();
			rpcClient.setConfig(config);		
			
			ArrayList<Object> params = new ArrayList<Object>();
			Hashtable<String, Object> executionData = new Hashtable<String, Object>();				
			executionData.put("devKey", DEV_KEY);
			executionData.put("tcid", tcid);
			executionData.put("tpid", tpid);
			executionData.put("status", status);
			params.add(executionData);
			
			Object[] result = (Object[]) rpcClient.execute("tl.reportTCResult", params);

			// Typically you'd want to validate the result here and probably do something more useful with it
			System.out.println("Result was:\n");				
			for (int i=0; i< result.length; i++)
			{
				Map item = (Map)result[i];
				System.out.println("Keys: " + item.keySet().toString() + " values: " + item.values().toString());
			}
		}
		catch (MalformedURLException e)
		{
			e.printStackTrace();
		}
		catch (XmlRpcException e)
		{
			e.printStackTrace();
		}
	}
		
	public static void main(String[] args) 
	{
		// Substitute this for a valid tcid and tpid within your project
		TestlinkAPIXMLRPCClient.testLinkReport(1132, 56646, "p");		
	}
}