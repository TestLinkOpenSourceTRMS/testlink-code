curl -D- -u testlink.forum:forum -X POST --data "@issueWithPriority.json" \
     -H "Content-Type: application/json" https://testlink.atlassian.net/rest/api/latest/issue/