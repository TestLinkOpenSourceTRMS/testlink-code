curl -D- -u testlink.forum:forum -X POST --data "@issueWithPriorityByName.json" \
     -H "Content-Type: application/json" https://testlink.atlassian.net/rest/api/latest/issue/