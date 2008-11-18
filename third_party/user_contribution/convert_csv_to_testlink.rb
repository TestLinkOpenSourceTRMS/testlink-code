# convert_csv_to_testlink.rb
#
# author: Cynthia Sadler
# usage: ruby convert_csv_to_testlink.rb infile.csv outfile.xml
#
#   where infile.cvs is a comma-separated-value file with four fields:
#   name, summary, steps, expected results
#
# The steps field and expected results field may have multiple steps. If
# multiple steps are used, use newlines to delimit the steps, and enclose 
# entire field with quotes. E.g., here is a sample row with multiple steps:
#
# "Sample test case","Verify something.","0. PREREQ: delete previous stuff.
# 1. Do something.
# 2. Do another thing.","A. Verify this.
# B. Verify that."
#

require 'rubygems'
require 'csv'

$infile = ARGV[0]
$testlinkxml = ARGV[1]
$temp = Array.new
$testdata = Array.new

$beginning = '<?xml version="1.0" encoding="UTF-8"?><testcases>	'
$end = "</testcases>"

File.open($testlinkxml, "w+") do |f|
  f.puts $beginning
  CSV::Reader.parse(File.open($infile)) do |row|
    # skip the first row which is a header
    next if row[0].data == 'name'
    
    # testcase  
    f.puts '<testcase name="' + row[0] + '">'

    # summary
    f.puts '  <summary><![CDATA[' + row[1] + ']]></summary>'
    
    # steps
    
    if row[2] != nil then
      f.puts '  <steps><![CDATA['
      steps = row[2].split("\n")
      $i = 0
      while $i < steps.length
        f.puts '<p>' + steps[$i] + '</p>'
        $i += 1
      end
      f.puts ']]></steps>'
    else
      f.puts '  <steps><![CDATA[]]></steps>'  
    end
    

    # expected results
    
    if row[2] != nil then
      f.puts '  <expectedresults><![CDATA['
      steps = row[3].split("\n")
      $i = 0
      while $i < steps.length
        f.puts '<p>' + steps[$i] + '</p>'
        $i += 1      
      end
      f.puts ']]></expectedresults>'
    else
      f.puts '  <expectedresults><![CDATA[]]></expectedresults>'  
    end
    

    # close testcase tag
    f.puts '</testcase>'
  end
  # closing document
  f.puts $end  
end
