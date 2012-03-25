# FogBugz PHP API

* @see [XML API Documentation](http://fogbugz.stackexchange.com/fogbugz-xml-api)

This is a small API used to wrap up the FogBugz API into an easy to call
object. It allows access to all of the API commands exposed by FogBugz,
and returns a SimpleXMLElement object for any commands that return data.

# Sample Code

    $fogbugz = new FogBugz(
        'username@example.com',
        'password',
        'http://example.fogbugz.com'
    );
    $fogbugz->logon();
    $fogbugz->startWork(array(
      'ixBug' => 23442
    ));


# Magic Methods

The API uses __call() to make a method for each api endpoint in the FogBugz API.
For example, to call the stopWork api endpoint, simple call a method on the
fogbugz object $fogbugz->stopWork(). If you want to call the api with specific
parameters, supply those to the function as an associative array, as in the
sample above.

# Return Format

Remember that the api methods return SimpleXMLElement objects. See the sample.php
file for an example of this.

