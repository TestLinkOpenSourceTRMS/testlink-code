/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * filesource clientSayHello.java
 *
 * @Author	francisco.mancardi@gmail.com
 *
 * @internal revisions 
 */
import java.net.MalformedURLException;
import java.net.URL;
import java.util.ArrayList;
import java.util.Hashtable;
import java.util.Map;

import org.apache.xmlrpc.XmlRpcException;
import org.apache.xmlrpc.client.XmlRpcClient;
import org.apache.xmlrpc.client.XmlRpcClientConfigImpl;

public class clientSayHello
{		
	// Substitute your Dev Key Here
	public static final String DEV_KEY =  "CLIENTSAMPLEDEVKEY";

	// Substitute your Server URL Here
	public static final String SERVER_URL = "http://localhost:8080/development/gitrepo/tlcode/lib/api/xmlrpc.php";	

	public static void main(String[] args) 
	{
		clientSayHello.doIt("Please give me an answer!!!");
	}
	 
	public static void doIt(String msg)
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
			Object result = rpcClient.execute("tl.sayHello", params);
			System.out.println("Result was:\n");				
			// Map item = (Map)result;
			System.out.println(result);
			
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
		
}