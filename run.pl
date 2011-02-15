#!/usr/bin/perl 

use lib qw(lib);
use warnings;
use strict;
use English qw( -no_match_vars );
use Time::HiRes qw(time);

my $sleep = 10;
my $msg_log = '/var/log/twitter-collage/run.log';

# ---- consume ----

while (1) {

    my $cmd = 'php /servers/develop/twitter-collage/twitter-search.php';
    log_to_file("Command:$cmd\n", $msg_log);
    my $res = `$cmd`;
    log_to_file("Response:$res\n", $msg_log);

    $cmd = 'php /servers/develop/twitter-collage/make-images.php';
    log_to_file("Command:$cmd\n", $msg_log);
    $res = `$cmd`;
    log_to_file("Response:$res\n", $msg_log);

    $cmd = 'php /servers/develop/twitter-collage/collage-build.php';
    log_to_file("Command:$cmd\n", $msg_log);
    $res = `$cmd`;
    log_to_file("Response:$res\n", $msg_log);

    sleep $sleep;
}


sub log_to_file {
    my $msg = shift;
    my $filename = shift;

    my $fh;
    open $fh, ">>", $filename or die;
    print $fh "$msg\n";
    close $fh;

    return;
}
