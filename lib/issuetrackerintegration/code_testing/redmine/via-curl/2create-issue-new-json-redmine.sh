 curl 'http://fmancardi.m.redmine.org/issues.json' \
  -X POST \
  -H 'X-Redmine-API-Key: 1c3438cf46cd880e77f623cf65a043fbaf9c0c86' \
  -H 'Content-Type: application/json' \
  -d '{ "issue": {"subject": "A new issue 20180221", "project_id": "tl01"} }'