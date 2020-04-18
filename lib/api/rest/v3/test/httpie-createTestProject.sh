#URI=http://testlink-2.0.0/lib/api/rest/v3/
. ./setURI.sh
echo $URI
APIKEY="Apikey:c94048220527a3d038db5c19e1156c08"
ACTION=testprojects
JSON=./json/createTestProject.json
http POST $URI$ACTION $APIKEY  < $JSON
