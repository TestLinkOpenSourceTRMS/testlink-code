 # 
 curl 'https://testlinktest.planio.com/issues.json' \
  -X POST \
  -H 'X-Redmine-API-Key: c1796c2215a55723f134a4c546e9c121' \
  -H 'Content-Type: application/json' \
  -d '{ 
  "issue": 
  {"subject": "issue 20181020.02", 
   "project_id": "website-redesign"} }'