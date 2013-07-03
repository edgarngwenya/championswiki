<LINK href="/w/scripts/log_uploader.css" rel="stylesheet" type="text/css"/>

<form name="event_planner" method="post" id="EventPlanner">
  <div class="form_block">
    <div class="form_block_title">Name</div>
    <input name="name" type="text" id="iname" size="40"/>
    <div id="name_error" class="error">
    </div>
  </div>
  
  <div class="form_block">
    <div class="form_block_title">Date ( MM/DD/YYYY )</div>
    <input name="date" type="text" id="idate" size="10" maxlength="10"/> 
    <div id="date_error" class="error">
    </div>
  </div>
  
  <div class="form_block">
    <div class="form_block_title">Time ( HH:MM )</div>
    <input name="time" type="text" id="itime" size="5" maxlength="5"/>
    <select name="ampm">
    	<option value="am">AM</option>
    	<option value="pm">PM</option>
    </select>
    <div id="time_error" class="error">
    </div>
  </div>
  
  <div class="form_block">
    <div class="form_block_title">Summary</div>
    <textarea name="summary" id="itime" style="width: 50%;" rows="5"></textarea>
    <div id="summary_error" class="error">
    </div>
  </div>
  
  <div class="form_block">
    <input type="submit" name="submit" id="isubmit" value="Add Event"/>
  </div>
</form>
