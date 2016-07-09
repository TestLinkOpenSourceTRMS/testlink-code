curl -v -H "Content-Type: application/xml" \
     -X POST --data "@redmine-issue02.xml" \
     -H "X-Redmine-API-Key: ff51e7e6c5b2349f95bef327d961e526abd45638" \
     http://localhost:8080/redmine/issues.xml
