<LINK href="/w/scripts/calendar.css" rel="stylesheet" type="text/css"/>
<SCRIPT src="/w/scripts/jquery-1.2.6.js" type="text/javascript"></script>
<SCRIPT src="/w/scripts/calendar.js" type="text/javascript"></script>

{php}
	$id = $this->get_template_vars( 'id' );
	$date = $_COOKIE['calendar-' . $id ] ? $_COOKIE['calendar-' . $id ] : date( 'Y-m-d' );
	$dateColumn = $this->get_template_vars( 'dateColumn' ) ? 
		$this->get_template_vars( 'dateColumn' ) : 
		'ooc_date';
	$start = date( 'N', strtotime( substr( $date, 0, 7 ) . '-01' ) ) % 7;
	$daysinmonth = date( 't', strtotime( $date ) );
	$this->assign('date', strtotime( $date ));
	$this->assign('start', $start);
	$this->assign('daysinmonth', $daysinmonth);
	$this->assign('weeks', ceil( ($start + $daysinmonth) / 7 ) );
		
	$events = array();
	foreach ( $this->get_template_vars( 'articles' ) as $m ) {
		if ( substr( $date, 0, 7) == substr( $m[$dateColumn], 0, 7 ) ) {
			$n = intval( substr( $m[$dateColumn], 8, 2 ) );
			if ( !$events[$n] ) {
				$events[$n] = array( $m );
			}
			else {
				$events[$n][] = $m;
			}
		}
	}
	$this->assign('events', $events);
	$this->assign('months', array( 
		'January', 'February', 'March', 'April', 'May', 'June',
		'July', 'August', 'September', 'October', 'November', 'December'
	));

	$options =  $this->get_template_vars( 'options' );
	$endYear = $options['endYear'] ? $options['endYear'] : date( 'Y' );
	$startYear = $options['startYear'] ? $options['startYear'] : $endYear - 4;
	
	$years = array();
	for ( $year = $startYear; $year <= $endYear; $year++ ) {
		$years[] = $year;
	}
	$this->assign( 'years', $years );
	$this->assign( 'currentDate',  ( date('m') == substr( $date, 5,2 )) ? date( 'd' ) : 0 );
{/php}

{$dump}

<div class="calendar" id="{$id}">
	<div class="calendar_title">
		<div class="left">
			<input type="button" value="&lt;&lt;" onClick="CalendarManager.decrementMonth( '{$id}' );"/>
			<select name="monthSelect" onChange="CalendarManager.setMonth( '{$id}', this.value );">
				{section name=dayofweek start=0 loop=12}
					<option value="{$smarty.section.dayofweek.iteration}">{$months[$smarty.section.dayofweek.index]}</option>
				{/section}
			</select>
			<input type="button" value="&gt;&gt;" onClick="CalendarManager.incrementMonth( '{$id}' );"/>
		</div>
		<div class="middle">
			<b>Media</b><br/>
			{$date|date_format:'%B %Y'}
		</div>
		<div class="right">
			<input type="button" value="&lt;&lt;" onClick="CalendarManager.decrementYear( '{$id}' );"/>
			<select name="yearSelect" onChange="CalendarManager.setYear( '{$id}', this.value );">
				{foreach name=parent from=$years item=year}
				<option value="{$year}">{$year}</option>
				{/foreach}
			</select>
			<input type="button" value="&gt;&gt;" onClick="CalendarManager.incrementYear( '{$id}' );"/>
		</div>
		<div class="clear"></div>
  </div>

  <div class="calendar_header">
    <div class="sunday">Sun</div>
    <div class="notsunday">Mon</div>
    <div class="notsunday">Tue</div>
    <div class="notsunday">Wed</div>
    <div class="notsunday">Thu</div>
    <div class="notsunday">Fri</div>
    <div class="notsunday">Sat</div>
    <div class="clear"></div>
  </div>
  
  {section name=week start=0 loop=$weeks}
  <div class="calendar_body">
    {section name=dayofweek start=0 loop=7}
      {assign var='count' value=`$smarty.section.week.index*7+$smarty.section.dayofweek.index`}
      {assign var='d' value=`$count-$start+1`}
      {if $currentDate > 0 && $d == $currentDate}
      	{assign var='on' value=' on'}
      {else}
       	{assign var='on' value=''}
      {/if}
      {if $smarty.section.dayofweek.iteration == 1}
		<div class="sunday{$on}">
	  {elseif $smarty.section.dayofweek.iteration == 7}
		<div class="saturday{$on}">
	  {else}
	  	<div class="notsunday{$on}">
	  {/if}
	  {if $start <= $count && $count - $start < $daysinmonth }
		  <div class="date">
			{$d}
		  </div>
		  <div class="calendar_items">
		    <ul>
			{foreach from=$events[$d] item=i}
			  <li><a href="{$i.page_link}">{$i.title}</a></li>
			{/foreach}
			</ul>
		  </div>
	  {/if}
		</div>
    {/section}
    <div class="clear"></div>
  </div>
  {/section}
</div>

<script type="text/javascript">
	CalendarManager.initialize( '{$id}', '{$date|date_format:'%Y-%m-%d'}' );
</script>
