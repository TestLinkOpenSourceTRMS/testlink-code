. ./setURI.sh
APIKEY="Apikey:c94048220527a3d038db5c19e1156c08"
ACTION=builds/12
JSON=./json/updateBuild.json
http PUT $URI$ACTION $APIKEY  < $JSON
