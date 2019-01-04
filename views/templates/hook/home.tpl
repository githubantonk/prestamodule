<div id="mymodule_block_home">
	<h1>Cryptocurrency Converter Calculator{$linkpost}</h1>
	<div class="products">
		{if count($list_all)}
	  	<input name="num" type="text" value="1">
		<div><select name="from_">{foreach from=$list_all item=val}<option data-price="{$val.price}" data-symbol="{$val.symbol}" value="{$val.id}">{$val.name} ({$val.symbol})</option>{/foreach}</select></div>
		<div class="direction-switch"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="22" viewBox="0 0 16 22"><path d="M0 6.875v-3.75h10.82V0L16 5l-5.18 5V6.875H0zM16 18.875v-3.75H5.18V12L0 17l5.18 5v-3.125H16z"></path></svg></div>
		<input name="num2" type="text" value="1" readonly="readonly">
		<div><select name="to_">{foreach from=$list_all item=val}<option data-price="{$val.price}" data-symbol="{$val.symbol}" {if ($val.id == 1027)}selected {/if}value="{$val.id}">{$val.name} ({$val.symbol})</option>{/foreach}</select></div>
		{else}
		Not available
		{/if}
	</div>
	<div class="history10"><br>Recently converted<br>
		<div data-history10>
		</div>
	</div>
	<br><span>upd: {$time_upd}</span>
	<br><span class="error">{$error}</span>	
</div>