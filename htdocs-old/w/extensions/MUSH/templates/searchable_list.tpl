<div>
	<ul>
	{foreach key=i item=m from=$articles}
		<li><a href="{$m.page_link}">{$m.title}</a></li>
	{foreachelse}
		No media articles were found.
	{/foreach}
	</ul>
</div>