. ./setURI.sh
APIKEY="Apikey:c94048220527a3d038db5c19e1156c08"
ACTION=testsuites
JSON=./json/createTestSuite.json
http POST $URI$ACTION $APIKEY  < $JSON
