#!/usr/bin/perl

use strict;
use warnings;
use Class::Date;

my $directory = shift;
my $script = shift;
my @args = ();
my $logfile = shift;

my $now = Class::Date->now;
my $time = $now;

while ( my $arg = shift ) {
    push( @args, $arg );
}

my $command = sprintf( 'cd %s; %s %s >> %s', $directory, $script, join( ' ', @args ), $logfile );
system( "echo [$time] $script >> $logfile" );
system( $command );
