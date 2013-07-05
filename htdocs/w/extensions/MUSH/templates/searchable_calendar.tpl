<LINK href="/w/extensions/MUSH/calendar.css" rel="stylesheet" type="text/css"/>
<SCRIPT src="/w/extensions/MUSH/jquery-1.2.6.js" type="text/javascript"></script>
<SCRIPT src="/w/extensions/MUSH/calendar.js" type="text/javascript"></script>

<div class="calendar" id="{$id}">
	<div class="calendar_title">
		<div class="left">
			<input type="button" value="&lt;&lt;" onClick="CalendarManager.decrementMonth('{$id}');"/>
			<select name="monthSelect" onChange="CalendarManager.setMonth('{$id}', this.value);">
				{section name=dayofweek start=0 loop=12}
					<option value="{$smarty.section.dayofweek.iteration}">{$months[$smarty.section.dayofweek.index]}</option>
				{/section}
			</select>
			<input type="button" value="&gt;&gt;" onClick="CalendarManager.incrementMonth('{$id}');"/>
		</div>
		<div class="middle">
			<b>Media</b><br/>
			{$date|date_format:'%B %Y'}
		</div>
		<div class="right">
			<input type="button" value="&lt;&lt;" onClick="CalendarManager.decrementYear('{$id}');"/>
			<select name="yearSelect" onChange="CalendarManager.setYear('{$id}', this.value);">
				{foreach name=parent from=$years item=year}
					<option value="{$year}">{$year}</option>
				{/foreach}
			</select>
			<input type="button" value="&gt;&gt;" onClick="CalendarManager.incrementYear('{$id}');"/>
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
				{assign var='count' value=$smarty.section.week.index*7+$smarty.section.dayofweek.index}
				{assign var='d' value=$count-$start+1}
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
						<li><a href="{$i.page_link}" title="{$i.title}">{$i.title}</a></li>
						{/foreach}
					</ul>
				</div>
				{else}
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
