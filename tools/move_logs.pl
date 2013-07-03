#!/usr/local/bin/perl

use strict;
use warnings;

use DBI;

my $dbh = DBI->connect( 'dbi:mysql:jaburo_champs', 'jaburo', 'eexeinoh' );

my $sth = $dbh->prepare_cached( "
select p.page_id, p.page_title
from page p, categorylinks cl
where p.page_id = cl.cl_from
and cl.cl_to = 'Logs';
"); 

my $update_sth = $dbh->prepare_cached("
update page set page_namespace = ? where page_id = ?
");

$sth->execute;

while ( my $h = $sth->fetchrow_hashref ) {
	print $h->{page_title} . "\n";
	$update_sth->execute( 0, $h->{page_id} );
}
