URI=http://localhost/development/github/testlink-code/lib/api/rest/v2/
AUTH=c94048220527a3d038db5c19e1156c08:pinkfloyd
ACTION=testcases
JSON=x-with-cr-AL.json
http POST $URI$ACTION --auth $AUTH < $JSON
