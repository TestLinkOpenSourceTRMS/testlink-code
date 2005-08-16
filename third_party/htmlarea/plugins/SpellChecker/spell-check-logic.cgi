#! /usr/bin/perl -w

# Spell Checker Plugin for HTMLArea-3.0
# Sponsored by www.americanbible.org
# Implementation by Mihai Bazon, http://dynarch.com/mishoo/
#
# (c) dynarch.com 2003.
# Distributed under the same terms as HTMLArea itself.
# This notice MUST stay intact for use (see license.txt).
#
# $Id: spell-check-logic.cgi,v 1.3 2005/08/16 18:01:21 franciscom Exp $

use strict;
use utf8;
use Encode;
use Text::Aspell;
use XML::DOM;
use CGI;

my $TIMER_start = undef;
eval {
    use Time::HiRes qw( gettimeofday tv_interval );
    $TIMER_start = [gettimeofday()];
};
# use POSIX qw( locale_h );

binmode STDIN, ':utf8';
binmode STDOUT, ':utf8';

my $debug = 0;

my $speller = new Text::Aspell;
my $cgi = new CGI;

my $total_words = 0;
my $total_mispelled = 0;
my $total_suggestions = 0;
my $total_words_suggested = 0;

# FIXME: report a nice error...
die "Can't create speller!" unless $speller;

my $dict = $cgi->param('dictionary') || $cgi->cookie('dictionary') || 'en';

# add configurable option for this
$speller->set_option('lang', $dict);
$speller->set_option('encoding', 'UTF-8');
#setlocale(LC_CTYPE, $dict);

# ultra, fast, normal, bad-spellers
# bad-spellers seems to cause segmentation fault
$speller->set_option('sug-mode', 'normal');

my %suggested_words = ();
keys %suggested_words = 128;

my $file_content = decode('UTF-8', $cgi->param('content'));
$file_content = parse_with_dom($file_content);

my $ck_dictionary = $cgi->cookie(-name     => 'dictionary',
                                 -value    => $dict,
                                 -expires  => '+30d');

print $cgi->header(-type    => 'text/html; charset: utf-8',
                   -cookie  => $ck_dictionary);

my $js_suggested_words = make_js_hash(\%suggested_words);
my $js_spellcheck_info = make_js_hash_from_array
  ([
    [ 'Total words'           , $total_words ],
    [ 'Mispelled words'       , $total_mispelled . ' in dictionary \"'.$dict.'\"' ],
    [ 'Total suggestions'     , $total_suggestions ],
    [ 'Total words suggested' , $total_words_suggested ],
    [ 'Spell-checked in'      , defined $TIMER_start ? (tv_interval($TIMER_start) . ' seconds') : 'n/a' ]
   ]);

print qq^<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" media="all" href="spell-check-style.css" />
<script type="text/javascript">
  var suggested_words = { $js_suggested_words };
  var spellcheck_info = { $js_spellcheck_info }; </script>
</head>
<body onload="window.parent.finishedSpellChecking();">^;

print $file_content;
if ($cgi->param('init') eq '1') {
    my @dicts = $speller->dictionary_info();
    my $dictionaries = '';
    foreach my $i (@dicts) {
        next if $i->{jargon};
        my $name = $i->{name};
        if ($name eq $dict) {
            $name = '@'.$name;
        }
        $dictionaries .= ',' . $name;
    }
    $dictionaries =~ s/^,//;
    print qq^<div id="HA-spellcheck-dictionaries">$dictionaries</div>^;
}

print '</body></html>';

# Perl is beautiful.
sub spellcheck {
    my $node = shift;
    my $doc = $node->getOwnerDocument;
    my $check = sub {                 # called for each word in the text
        # input is in UTF-8
        my $word = shift;
        my $already_suggested = defined $suggested_words{$word};
        ++$total_words;
        if (!$already_suggested && $speller->check($word)) {
            return undef;
        } else {
            # we should have suggestions; give them back to browser in UTF-8
            ++$total_mispelled;
            if (!$already_suggested) {
                # compute suggestions for this word
                my @suggestions = $speller->suggest($word);
                my $suggestions = decode($speller->get_option('encoding'), join(',', @suggestions));
                $suggested_words{$word} = $suggestions;
                ++$total_suggestions;
                $total_words_suggested += scalar @suggestions;
            }
            # HA-spellcheck-error
            my $err = $doc->createElement('span');
            $err->setAttribute('class', 'HA-spellcheck-error');
            my $tmp = $doc->createTextNode;
            $tmp->setNodeValue($word);
            $err->appendChild($tmp);
            return $err;
        }
    };
    while ($node->getNodeValue =~ /([\p{IsWord}']+)/) {
        my $word = $1;
        my $before = $`;
        my $after = $';
        my $df = &$check($word);
        if (!$df) {
            $before .= $word;
        }
        {
            my $parent = $node->getParentNode;
            my $n1 = $doc->createTextNode;
            $n1->setNodeValue($before);
            $parent->insertBefore($n1, $node);
            $parent->insertBefore($df, $node) if $df;
            $node->setNodeValue($after);
        }
    }
};

sub check_inner_text {
    my $node = shift;
    my $text = '';
    for (my $i = $node->getFirstChild; defined $i; $i = $i->getNextSibling) {
        if ($i->getNodeType == TEXT_NODE) {
            spellcheck($i);
        }
    }
};

sub parse_with_dom {
    my ($text) = @_;
    $text = '<spellchecker>'.$text.'</spellchecker>';

    my $parser = new XML::DOM::Parser;
    if ($debug) {
        open(FOO, '>:utf8', '/tmp/foo');
        print FOO $text;
        close FOO;
    }
    my $doc = $parser->parse($text);
    my $nodes = $doc->getElementsByTagName('*');
    my $n = $nodes->getLength;

    for (my $i = 0; $i < $n; ++$i) {
        my $node = $nodes->item($i);
        if ($node->getNodeType == ELEMENT_NODE) {
            check_inner_text($node);
        }
    }

    my $ret = $doc->toString;
    $ret =~ s{<spellchecker>(.*)</spellchecker>}{$1}sg;
    return $ret;
};

sub make_js_hash {
    my ($hash) = @_;
    my $js_hash = '';
    while (my ($key, $val) = each %$hash) {
        $js_hash .= ',' if $js_hash;
        $js_hash .= '"'.$key.'":"'.$val.'"';
    }
    return $js_hash;
};

sub make_js_hash_from_array {
    my ($array) = @_;
    my $js_hash = '';
    foreach my $i (@$array) {
        $js_hash .= ',' if $js_hash;
        $js_hash .= '"'.$i->[0].'":"'.$i->[1].'"';
    }
    return $js_hash;
};
