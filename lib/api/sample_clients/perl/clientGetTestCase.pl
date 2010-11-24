#!/usr/bin/perl
#
# TestLink Open Source Project - http://testlink.sourceforge.net/
# This script is distributed under the GNU General Public License 2 or later.
#
# Filename $RCSfile: clientGetTestCase.pl,v $
#
# ------------------------------------------------------------------------------
#
# @version $Revision: 1.1 $
# @modified $Date: 2010/11/24 18:05:29 $ by $Author: franciscom $
# @Author: 
#
# rev: 
#
#
use RPC::XML::Client;

$devkey='DEVKEY';
$client = RPC::XML::Client->new('http://SERVERNAME/testlink/lib/api/xmlrpc.php');
$testplan_id = XY;

$tcinfo=$client->send_request('tl.getTestCase', { devKey=>$devkey, testcaseexternalid=>'XY-1' });
@resp_array = @{$tcinfo->value};
$tc_id = $resp_array[0]->{"testcase_id"};

$data = { "devKey"=>$devkey, "testcaseid"=>$tc_id, "testplanid"=>$testplan_id, "status"=>"p", "notes"=>"perl example 1", "guess"=>"TRUE" };
$tcinfo=$client->send_request('tl.reportTCResult', $data);
@resp_array = @{$tcinfo->value};
$b  = $resp_array[0];
foreach my $k (keys(%$b)) { print "key \'$k\' has value  \'$b->{$k}\'\n" }


$tcinfo=$client->send_request('tl.getTestCase', { devKey=>$devkey, testcaseexternalid=>'XY-2' });
@resp_array = @{$tcinfo->value};
$tc_id = $resp_array[0]->{"testcase_id"};

$data = { "devKey"=>$devkey, "testcaseid"=>$tc_id, "testplanid"=>$testplan_id, "status"=>"f", "notes"=>"perl example 2", "guess"=>"TRUE" };
$tcinfo=$client->send_request('tl.reportTCResult', $data);
@resp_array = @{$tcinfo->value};
$b  = $resp_array[0];
foreach my $k (keys(%$b)) { print "key \'$k\' has value  \'$b->{$k}\'\n" }
