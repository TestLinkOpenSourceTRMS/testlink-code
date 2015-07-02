#!/usr/bin/perl
#
# TestLink Open Source Project - http://testlink.sourceforge.net/
# This script is distributed under the GNU General Public License 2 or later.
#
# Filename $RCSfile: clientCreateBuild.pl,v $
#
# ------------------------------------------------------------------------------
# Sample Contributed by: Renato S de Araujo
#
# Tested on windows using strawberry-perl-5.10.1.2-portable (Francisco Mancardi)
# After installing strawberry perl you need to do following steps:
# 1. go to strawberry perl installation dir
# 2. run portableshell.bat
# 3. using cpan command install RPC XML:
#    cpan RPC:XML:Client
# 4. if something fails with this instructions, do some search on Internet
# 5. after succesful installation: set in this script correct parameters
#    testlink xmlrpc server location, tl items id, etc.
#
# 6. now run the script: 
#    perl clientCreateBuild.pl
#    
# ------------------------------------------------------------------------------
#
#
# @version $Revision: 1.2 $
# @modified $Date: 2010/07/10 15:34:32 $ by $Author: franciscom $
# @Author: Renato S de Araujo
#
# rev: 
#
#
use RPC::XML::Client;
my $server_url = 'http://localhost:8900/head-20100702/lib/api/xmlrpc.php';
# my $server_url = 'http://localhost:8900/head-20100501/lib/api/xmlrpc.php';
# my $server_url = 'http://localhost:8600/testlink-1.9.beta4/lib/api/xmlrpc.php';

my $devkey='CLIENTSAMPLEDEVKEY';

my $testplanid=425;
my $buildname='8.0.28';
my $notes='Created by API';

my $cli = RPC::XML::Client->new($server_url);
print "\nTest Link XML-RPC API \n";
print "Testing Server: $server_url \n";
my $build=$cli->send_request('tl.createBuild',
                              {
                               devKey=>$devkey,
                               testplanid=>$testplanid,
                               buildname=>$buildname,
                               buildnotes=>$notes
                              }
                              );

my @resp = @{$build->value};
my $b  = $resp[0];

foreach my $k (keys(%$b)){
    print "key \'$k\' has value  \'$b->{$k}\'\n";
}