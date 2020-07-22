. ./setURI.sh
APIKEY="Apikey:c94048220527a3d038db5c19e1156c08"
ACTION=testplans/64/platforms

JSON=./json/addPlatforms.json
http PUT $URI$ACTION $APIKEY  < $JSON
