/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: clientAddTestCaseToTestPlan.java,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2010/06/24 17:25:57 $ by $Author: asimon83 $
 * @Author: francisco.mancardi@gmail.com
 *
 * rev: 
 */
import java.net.MalformedURLException;
import java.net.URL;
import java.util.ArrayList;
import java.util.Hashtable;
import java.util.Map;

import org.apache.xmlrpc.XmlRpcException;
import org.apache.xmlrpc.client.XmlRpcClient;
import org.apache.xmlrpc.client.XmlRpcClientConfigImpl;

public class clientAddTestCaseToTestPlan 
{		
	// Substitute your Dev Key Here
	public static final String DEV_KEY =  "CLIENTSAMPLEDEVKEY";

	// Substitute your Server URL Here
	public static final String SERVER_URL = "http://localhost:8600/testlink-1.8.5b/lib/api/xmlrpc.php";	
	 
	public static void doIt(int testProjectID, int testPlanID, String testCaseExternalID, int version)
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
			Hashtable<String, Object> methodData = new Hashtable<String, Object>();				
			methodData.put("devKey", DEV_KEY);
			methodData.put("testprojectid", testProjectID);
			methodData.put("testplanid", testPlanID);
			methodData.put("testcaseexternalid", testCaseExternalID);
			methodData.put("version", version);
			params.add(methodData);
			
			Object result = rpcClient.execute("tl.addTestCaseToTestPlan", params);
			// Typically you'd want to validate the result here and probably do something more useful with it
			System.out.println("Result was:\n");				
			Map item = (Map)result;
			System.out.println("Keys: " + item.keySet().toString() + " values: " + item.values().toString());
			
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
		clientAddTestCaseToTestPlan.doIt(1,2,"QAZ-5",1);
	}
}