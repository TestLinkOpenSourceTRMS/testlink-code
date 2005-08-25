#!/usr/bin/perl

# PHP File Uploader with progress bar Version 1.43
# Copyright (C) Raditha Dissanyake 2003
# http://www.raditha.com

# Licence:
# The contents of this file are subject to the Mozilla Public
# License Version 1.1 (the "License"); you may not use this file
# except in compliance with the License. You may obtain a copy of
# the License at http://www.mozilla.org/MPL/
# 
# Software distributed under this License is distributed on an "AS
# IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or
# implied. See the License for the specific language governing
# rights and limitations under the License.
# 
# The Initial Developer of the Original Code is Raditha Dissanayake.
# Portions created by Raditha are Copyright (C) 2003
# Raditha Dissanayake. All Rights Reserved.
# 

# CHANGES:
# As of version 1.00 cookies were abolished!
# as of version 1.02 stdin is no longer set to non blocking.
# 1.40 - POST is no longer required and processing is more efficient.
#	Please refer online docs  for details.
# 1.42 - The temporary locations were changed, to make it easier to
#	clean up afterwards.	

use CGI;
use Fcntl qw(:DEFAULT :flock);
use File::Temp qw/ tempfile tempdir /;
#use Carp;


@qstring=split(/&/,$ENV{'QUERY_STRING'});
@p1 = split(/=/,$qstring[0]);
$sessionid = $p1[1];
$sessionid =~ s/[^a-zA-Z0-9]//g;  # sanitized as suggested by Terrence Johnson.

@p1 = split(/=/,$qstring[1]);
$php_uploader = $p1[1];

require("./header.cgi");

#carp "$post_data_file and $monitor_file";

$content_type = $ENV{'CONTENT_TYPE'};
$len = $ENV{'CONTENT_LENGTH'};
$bRead=0;
$|=1;

sub bye_bye {
	$mes = shift;
	print "Content-type: text/html\n\n";
	print "<br>$mes<br>\n";

	exit;
}


#
# The thing to watch out for is file locking. Only
# one thread may open a file for writing at any given time.
# 

if (-e "$post_data_file") {
	unlink("$post_data_file");
}

if (-e "$monitor_file") {
	unlink("$monitor_file");
}


sysopen(FH, $monitor_file, O_RDWR | O_CREAT, 0x777)
	or die "can't open numfile: $!";

# autoflush FH
$ofh = select(FH); $| = 1; select ($ofh);
flock(FH, LOCK_EX)
	or die "can't write-lock numfile: $!";
seek(FH, 0, 0)
	or die "can't rewind numfile : $!";
print FH $len;	
close(FH);	
	
sleep(1);


open(TMP,">","$post_data_file") or &bye_bye ("can't open temp file");

#
# read and store the raw post data on a temporary file so that we can
# pass it though to a CGI instance later on.
#



my $i=0;

$ofh = select(TMP); $| = 1; select ($ofh);
			
while (read (STDIN ,$LINE, 32768) && $bRead < $len )
{
	$bRead += length $LINE;
	
	select(undef, undef, undef,0.01);	# sleep for 0.35 of a second.
	
	# Many thanx to Patrick Knoell who came up with the optimized value for
	# the duration of the sleep

	$i++;
	print TMP $LINE;
}

close (TMP);



#
# We don't want to decode the post data ourselves. That's like
# reinventing the wheel. If we handle the post data with the perl
# CGI module that means the PHP script does not get access to the
# files, but there is a way around this.
#
# We can ask the CGI module to save the files, then we can pass
# these filenames to the PHP script. In other words instead of
# giving the raw post data (which contains the 'bodies' of the
# files), we just send a list of file names.
#
#print "\n\n";
open(STDIN,"$post_data_file") or die "can't open temp file";

# chmod the file so everyone can read it
# added by Ben Lancaster (benlanc@ster.me.uk)
chmod (0666, $post_data_file);

my $cg = new CGI();
my $qstring="?";
my %vars = $cg->Vars;
my $j=0;

while(($key,$value) = each %vars)
{
 	
	$file_upload = $cg->param($key);

	if(defined $value && $value ne '')
	{	

		my $fh = $cg->upload($key);
		#print "::".$key."::".$fh."::\n";
		if(defined $fh)
		{
			#carp $fh;
			($tmp_fh, $tmp_filename) = tempfile();

			# chmod the file so everyone can read it
			# added by Ben Lancaster (benlanc@ster.me.uk)	
			chmod (0666, $tmp_filename);
			
			while(<$fh>) {
				print $tmp_fh $_;
			}

			close($tmp_fh);

			$fsize =(-s $fh);

			
			$fh =~ s/([^a-zA-Z0-9_\-.])/uc sprintf("%%%02x",ord($1))/eg;
			
			$tmp_filename =~ s/([^a-zA-Z0-9_\-.])/uc sprintf("%%%02x",ord($1))/eg;
			$qstring .= "file[$key][name]=$fh&file[$key][size]=$fsize&";
			$qstring .= "file[$key][tmp_name]=$tmp_filename&";
			$j++;
		}
		else
		{
			$value =~ s/([^a-zA-Z0-9_\-.])/uc sprintf("%%%02x",ord($1))/eg;
			$qstring .= "$key=$value&" ;
		}
	}
}


my $url = $php_uploader . $qstring . "&" . $ENV{'QUERY_STRING'};

open (SIGNAL,">", $signal_file);
print SIGNAL "\n";
close (SIGNAL);

print "Location: $url\n\n";