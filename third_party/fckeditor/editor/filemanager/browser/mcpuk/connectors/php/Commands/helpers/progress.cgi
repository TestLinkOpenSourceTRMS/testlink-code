#!/usr/bin/perl -w

# PHP File Uploader with progress bar Version 1.43	
# Copyright (C) Raditha Dissanyake 2003,2004
# http://www.raditha.com

# Licence:
# The contents of this file are subject to the Mozilla Public
# License Version 1.1 (the "License"); you may not use this file
# except in compliance with the License. You may obtain a copy of
# the License at http://www.mozilla.org/MPL/
# 
# Software distributed under the License is distributed on an "AS
# IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or
# implied. See the License for the specific language governing
# rights and limitations under the License.
# 
# The Initial Developer of the Original Code is Raditha Dissanayake.
# Portions created by Raditha are Copyright (C) 2003,2004
# Raditha Dissanayake. All Rights Reserved.
# 
# Portions contributed by Orest Kinasevych are Copyright (C)
# 2003 Kinasevych Saj

#
# CHANGES
# 1.00 
#   No longer uses cookies. This has two major benefits; the first
#   being that minority of users who do not like cookies are not 
#   inconvinienced. Secondly there is one less dependecy and this 
#   would lead to a smoother setup for most people.
#   (if you want to use cookies look at the contrib folder)
#
# 1.02
#   added a cache control header in version 1.02
#
# 1.10
#   Added a more detailed progress bar in version 
#
# 1.42
#   The organization of the temporary files have been improved so to
#   make is easier to clean up after abandoned uploads. (as suggested
#   by Igor Kryltsov)
#
#
	


use CGI;
use Fcntl qw(:DEFAULT :flock);

#use Carp;		
# Carp is only needed if you want debugging. Uncomment the above line
# if you comment out any of the carp statements in the body of the 
# script.


#
# the most obvious issue that we will face is file locking.
# if two threads read and write from the same file at the same
# time only one of them will be allowed to finish the operation.
# Since we are storing our temporary data in a file we are likely to
# run into that exact same problem.
# We can't overcome it but we can make sure the progress bar does
# not display junk when that happens.
#
#
# status codes = 0-uploading, 1-started, 2- complete


$query = new CGI();
$sessionid = $query->param('sessionid');
$sessionid =~ s/[^a-zA-Z0-9]//g;  # santized as suggested by Terrence Johnson.

$iTotal = $query->param('iTotal');
$iRead = $query->param('iRead');
$status =  $query->param('iStatus');


##
# The code that deals with calculating elapsed time, time remaining 
# and upload speed were contributed by Orest Kinasevych 
##

##
# Get values assigned for current time and upload start time
##
#$dtnow = $query->param('dtnow'); # assign value for current time
$dtnow=time;
$dtstart = $query->param('dtstart'); # assign value for upload start time
##


#carp "$dtnow  $dtstart";

$thisUrl = $query->url;

##
# Elapsed time
# Calculate elapsed time and format for display
##
$dtelapsed = $dtnow - $dtstart;
$dtelapsed_sec = ($dtelapsed % 60); # gets number of seconds
$dtelapsed_min = ((($dtelapsed - $dtelapsed_sec) % 3600) / 60); # gets number of minutes
$dtelapsed_hours = (((($dtelapsed - $dtelapsed_sec) - ($dtelapsed_min * 60)) % 86400) / 3600);
# gets number of hours; assuming that we won't be going into days!
if ($dtelapsed_sec < 10) { $dtelapsed_sec = "0$dtelapsed_sec"; } # append leading zero
if ($dtelapsed_min < 10) { $dtelapsed_min = "0$dtelapsed_min"; } # append leading zero
if ($dtelapsed_hours < 10) { $dtelapsed_hours = "0$dtelapsed_hours"; } # append leading zero
$dtelapsedf = "$dtelapsed_hours:$dtelapsed_min:$dtelapsed_sec"; # display as 00:00:00
##

##
# Upload speed
##
$bSpeed = 0; # if not yet determined
if ($dtelapsed > 0) # avoid divide by zero errors
{
	$bSpeed = $iRead / $dtelapsed; # Bytes uploaded / Seconds elapsed = Bytes/Second speed
	$bitSpeed = $bSpeed * 8; # bps
	$kbitSpeed = $bitSpeed / 1000; # Kbps
}
else
{
	$kbitSpeed = $bSpeed; # just pass the zero value
}
$bSpeedf = sprintf("%d",$kbitSpeed); # remove decimals


##
# Est remaining time
# Calculate remaining time based on upload speed so far
##

$bRemaining = $iTotal - $iRead; # Total size - amount uploaded = amount remaining
$dtRemaining = 0;
if ($bSpeed > 0) {
	# Bytes remaining / Bytes/Second = Seconds 
	$dtRemaining = $bRemaining / $bSpeed;
}
$dtRemaining = sprintf("%d",$dtRemaining); # remove decimals
$dtRemaining_sec = ($dtRemaining % 60); # gets number of seconds
$dtRemaining_min = ((($dtRemaining - $dtRemaining_sec) % 3600) / 60); # gets number of minutes
$dtRemaining_hours = (((($dtRemaining - $dtRemaining_sec) - ($dtRemaining_min * 60)) % 86400) / 3600); # gets number of hours; assuming that we won't be going into days!
if ($dtRemaining_sec < 10)
{
 	# append leading zero
	$dtRemaining_sec = "0$dtRemaining_sec";
}
if ($dtRemaining_min < 10)
{
	# append leading zero
	$dtRemaining_min = "0$dtRemaining_min";
}
if ($dtRemaining_hours < 10)
{
 	# append leading zero
	$dtRemaining_hours = "0$dtRemaining_hours";
}
$dtRemainingf = "$dtRemaining_hours:$dtRemaining_min:$dtRemaining_sec"; # display as 00:00:00

##
# The values for iStatus are
#	0 - in progress
#	1 - New upload
#	2 - Complete
##

#carp "iTotal = $iTotal, iRead = $iRead, status = $status, sessionId = $sessionid";

require("./header.cgi");


sub readFlength()
{
	
	if(open (STAT, $monitor_file))
	{
		sysopen(STAT,  $monitor_file, O_RDONLY)
				or die "can't open numfile: $!";
		$ofh = select(STAT); $| = 1; select ($ofh);
		$iTotal = <STAT>;

		#carp "trying to read the stuff in $iTotal";
		if(defined($iTotal) && $iTotal ne "")
		{
			return 1;
		
		}
		else
		{
			return 0;
		}
	} 
	return 0;
	
}

##
# many thanx to Terrence Johnson who pointed out the fact that i should have added 
# cache control header.
##

print "Pragma: no-cache\n";
print "Content-type: text/xml\n\n ";

if($status == 1)
{
	#new upload starting
	show_starting();
}
elsif($status ==0)
{
	##
	# in progress
	# we will try to read in the total size of data to be transfered from the
	# shared file. It will also tell us how much data has been transfered upto
	# now.
	##
	$bRead = -s "$post_data_file";
		
	if(defined $bRead)
	{
		# We have  been able to read in it from the file.
		$percent = $bRead * 100 / $iTotal;
		$iRead=$bRead;
		
	}
	else
	{
		&show_error();
		exit();
	}

	#
	# division results in truncation errors at times so don't compare percentage
	# There have been occaisional reports of the progress bar showing 100% but not
	# disappearing even after file upload has been completed.
	#
	# Nils Menrad came up with the solution which is to modify the end of upload
	# test.
	#
	if((($iTotal == $bRead) && $bRead != 0) || $bRead>$iTotal) 
	{
		if($status == 1 && -e "$signal_file")
		{
			$bRead=0;
			$status=0;
			&get_last_values();
		}
		else
		{
			show_complete();
			unlink $monitor_file;
			unlink $post_data_file;
			unlink $signal_file;
			
			exit;
		}
	}
	else
	{
		$kachal = "$bRead , $iTotal";
	}


	&make_progress_bar();
	exit;
}
else 
{
	show_complete();
}

#
# Since the progress bar is in html, so it needs to refresh itself periodicaly to
# obtain new values. The refresh url with the query string is generated by this 
# function.

sub make_url
{

	#print "Content-type: text/html\n\n ";
	#print "hellow $iTotal $iStatus $sessionid $iRead <br>\n" ;

	##
	$url = "$thisUrl?iTotal=$iTotal&iRead=$iRead&iStatus=$status&sessionid=$sessionid&dtnow=$dtnow&dtstart=$dtstart";
	##
	$url =~ s/\n//;
	
	return $url;

}

sub make_progress_bar
{
	$url = make_url();
	
	print <<__PART1__;
	<UploadProgress sessionID="$sessionid">
		<RefreshURL><![CDATA[$url]]></RefreshURL>
		<TotalBytes>$iTotal</TotalBytes>
		<ReadBytes>$iRead</ReadBytes>
		<Status>$status</Status>
		<Speed>$bSpeedf</Speed>
		<TimeRemaining>$dtRemainingf</TimeRemaining>
		<TimeElapsed>$dtelapsedf</TimeElapsed>
	</UploadProgress>
__PART1__

}


sub show_complete
{
	
	$status=2;
	$url = make_url();

	print <<__PART2__;
	<UploadProgress sessionID="$sessionid">
		<RefreshURL><![CDATA[$url]]></RefreshURL>
		<TotalBytes>$iTotal</TotalBytes>
		<ReadBytes>$iRead</ReadBytes>
		<Status>$status</Status>
		<Speed>$bSpeedf</Speed>
		<TimeRemaining>0</TimeRemaining>
		<TimeElapsed>$dtelapsedf</TimeElapsed>
	</UploadProgress>
__PART2__

}

sub show_starting
{
	#carp "starting";
	if(readFlength() == 1)
	{
		$status=0;
	}
	$url = make_url();

	print <<__PART2__;
	<UploadProgress sessionID="$sessionid">
		<RefreshURL><![CDATA[$url]]></RefreshURL>
		<TotalBytes>$iTotal</TotalBytes>
		<ReadBytes>$iRead</ReadBytes>
		<Status>$status</Status>
		<Speed>$bSpeedf</Speed>
		<TimeRemaining>$dtRemainingf</TimeRemaining>
		<TimeElapsed>$dtelapsedf</TimeElapsed>
	</UploadProgress>
__PART2__
}

sub show_error
{
	$url = make_url();
	print <<__PART2__;
	<UploadProgress sessionID="$sessionid">
		<RefreshURL><![CDATA[$url]]></RefreshURL>
		<TotalBytes>$iTotal</TotalBytes>
		<ReadBytes>$iRead</ReadBytes>
		<Status>-1</Status>
		<Speed>$bSpeedf</Speed>
		<TimeRemaining>$dtRemainingf</TimeRemaining>
		<TimeElapsed>$dtelapsedf</TimeElapsed>
	</UploadProgress>
__PART2__

}

# this function may not return;
sub get_last_values()
{
	if($status == 1)
	{
		show_starting();
		exit;
	}
	else
	{

	 	if($status == 2)
		{

			
			show_complete();
			exit;
		}
		else
		{

			#
			# we have done everything possible to try to retrieve the data
			# now try to calculate the percentage once again
			#
			$iTotal = $iTotal;
			$bRead = $iRead;

			if(defined($iTotal) && $iTotal != 0)
			{
				$percent = $bRead * 100 / $iTotal;
				$kachal="1";
			}
			else
			{
				&show_error();
				exit;
			}
		}
	}
}
