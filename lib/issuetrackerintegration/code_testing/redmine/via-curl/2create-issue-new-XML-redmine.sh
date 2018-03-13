 curl 'http://fmancardi.m.redmine.org/issues.xml' \
  -X POST \
  -H 'X-Redmine-API-Key: 1c3438cf46cd880e77f623cf65a043fbaf9c0c86' \
  -H 'Content-Type: application/xml' \
-d '<issue><subject>/saado/TS100/SAA-48:00</subject><description>dddd</description><project_id>tl01</project_id></issue>'