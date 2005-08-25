#!/usr/bin/perl

# Mega Upload
# PHP File Uploader with progress bar Version 1.42
# Copyright (C) Raditha Dissanyake 2003
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
# Portions created by Raditha are Copyright (C) 2003
# Raditha Dissanayake. All Rights Reserved.
# 

$tmp_dir="/www/m/c/mcpuk.net/tmp";

$|=1;								#unbuffers streams

$interval=1;				  # how often to refresh the progress bar

$max_upload = 500000000000; # set this to whatever you feel suitable for you.
					    


# don't change the next few lines unless you have a very good reason to.

$post_data_file = "$tmp_dir/$sessionid"."_postdata";
$monitor_file = "$tmp_dir/$sessionid"."_flength";
$signal_file = "$tmp_dir/$sessionid"."_signal";


1;
