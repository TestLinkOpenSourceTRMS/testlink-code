# Using Sublime Text with RESTer plugin to access TestLink REST API
The files present in this folder are examples that can be run inside  
the Sublime Text editor using https://github.com/pjdietz/rester-sublime-http-client  

This can be a good approach to involve the developers in test case design 
allowing them to write simple tests without leaving the editor.  

I've tried without look to use the Pretty JSON plugin to improve the layout 
of the response text, but in my installation it failed.   

# Notes about using Visual Studio Code with REST Client
https://marketplace.visualstudio.com/items?itemName=humao.rest-client
It works also like a charm, with a minor advantage: response is automatically prettified 

![./media/vscode-rest-client.png](./media/vscode-rest-client.png)


# Notes regarding authentication token  
You will see in the different scripts  
Authorization: Basic YjgzNTk0OTJjYTIzM2ZkMWNlNTVkNjM2M2NkMDI2Y2Y6dQ==   

the value: YjgzNTk0OTJjYTIzM2ZkMWNlNTVkNjM2M2NkMDI2Y2Y6dQ==  
  
is your TestLink API/Script key encoded base64   