#!/usr/bin/perl

use strict;
use warnings;
use lib qw( modules-spectrum modules );

use Class::Date;
use Spectrum::Config;

my $date = Class::Date->now;
my $filename = sprintf( 'backups/champs_%02d_%02d_%04d.sql.gz', 
    $date->month,
    $date->day,
    $date->year
);

Spectrum::Config->file( '/home/engwenya/champs/conf/CHAMPS.conf' );
my $config = Spectrum::Config->new;

my ($database) = $config->{DBConnect} =~ /database=([^;]+)/;

my $command = sprintf( 'mysqldump -u %s --password=%s %s | gzip > %s', 
    $config->{UserConnectName},
    $config->{UserPassword},
    $database,
    $filename,
);

system( $command );
#system( sprintf( 'scp %s engwenya@jaburo.dyndns.org:~/backups', $filename ) );


