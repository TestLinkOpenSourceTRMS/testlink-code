# Using Sublime Text with RESTer plugin to access TestLink REST API
The files present in this folder are examples that can be run inside  
the Sublime Text editor using https://github.com/pjdietz/rester-sublime-http-client  

This can be a good approach to involve the developers in test case design 
allowing them to write simple tests without leaving the editor.  

I've tried with Pretty JSON plugin to improve the layout of the response text and worked OK!!

![./media/sublime-rester.png](./media/sublime-rester.png)


**Notes if you run Sublime 3 and Sublime 4 side by side**
[side by side](https://www.sublimetext.com/docs/side_by_side.html)
Because I did my tests using the same data directory for both versions and Pretty JSON  
is not compatible with Sublime 4 the formatting did not work.  
But when using Sublime 3 everything was OK!!.

# Notes about using Visual Studio Code with REST Client
https://marketplace.visualstudio.com/items?itemName=humao.rest-client
It works also like a charm, response is automatically JSON prettified 

![./media/vscode-rest-client.png](./media/vscode-rest-client.png)


# Notes regarding authentication token  
You will see in the different scripts  
Authorization: Basic YjgzNTk0OTJjYTIzM2ZkMWNlNTVkNjM2M2NkMDI2Y2Y6dQ==   

the value: YjgzNTk0OTJjYTIzM2ZkMWNlNTVkNjM2M2NkMDI2Y2Y6dQ==  
  
is your TestLink API/Script key encoded base64   