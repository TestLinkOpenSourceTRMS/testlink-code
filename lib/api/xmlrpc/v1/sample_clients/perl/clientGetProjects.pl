#!/usr/bin/perl
#
# TestLink Open Source Project - http://testlink.sourceforge.net/
# This script is distributed under the GNU General Public License 2 or later.
#
# Filename $RCSfile: clientGetProjects.pl,v $
#
# ------------------------------------------------------------------------------
#
# Tested on windows using strawberry-perl-5.10.1.2-portable (Francisco Mancardi)
# After installing strawberry perl you need to do following steps:
#
# 1. go to strawberry perl installation dir
# 2. run portableshell.bat
# 3. using cpan command install RPC XML:
#    cpan RPC:XML:Client
#
# 4. if something fails with this instructions, do some search on Internet
#
# 5. after succesful installation: set in this script correct parameters
#    testlink xmlrpc server location, tl items id, etc.
#
# 6. now run the script: 
#    perl clientCreateBuild.pl
#    
# ------------------------------------------------------------------------------
#
#
# @version $Revision: 1.1 $
# @modified $Date: 2010/07/10 16:12:30 $ by $Author: franciscom $
# @Author: Francisco Mancardi
#
# rev: 
#
#
# use utf8;
use Encode;
#use Text::Iconv;
#use Unicode::String;
use RPC::XML::Client;
my $method_on_test = 'getProjects';
my $devkey='CLIENTSAMPLEDEVKEY';
my $server_url = 'http://localhost:8900/head-20100702/lib/api/xmlrpc.php';
# my $server_url = 'http://localhost:8900/head-20100501/lib/api/xmlrpc.php';
# my $server_url = 'http://localhost:8600/testlink-1.9.beta4/lib/api/xmlrpc.php';

my $cli = RPC::XML::Client->new($server_url);
print "\nTest Link XML-RPC API \n";
print "Testing method: $method_on_test() \n";
print "Testing Server: $server_url \n\n";

my $answer = $cli->send_request('tl.' . $method_on_test,{devKey=>$devkey});
my @resp = @{$answer->value};
my $loop2do = scalar (@resp);
my $feedback = 1;
for (my $idx=0; $idx < $loop2do; $idx++,$feedback++)
{ 
  print "Result Record $feedback \n";
  print "------------------------------------------------------------------------------------------- \n";
  my $b  = $resp[$idx];
  foreach my $k (keys(%$b))
  {
      print "key \'$k\' has value  \'$b->{$k}\'\n";
      
      if( $k eq 'name' )
      {
        # my $dd = pack("C*", unpack('U*', $b->{$k}));
        # $dd = encode("iso-8859-1", $b->{$k}); 
        # $dd = decode("cp1252", $b->{$k});
        $dd = decode("iso-8859-1", $b->{$k});
        print "$dd\n";
      }
  }
  print "\n";
}