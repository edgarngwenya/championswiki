<LINK href="/w/scripts/log_uploader.css" rel="stylesheet" type="text/css"/>

<form name="log_upload" method="post" id="LogUploader" enctype="multipart/form-data">
  <div class="form_block">
    <div class="form_block_title">Log Name</div>
    <div class="radio_option">
      <input type="radio" name="name_method" id="inametitle" value="name"/>
      <span>Choose a name.</span>
      <div class="option">
        <div class="option_title">Name</div>
        <div class="option_value"><input class="text" name="name" id="ititle" value=""/></div>
      </div>
    </div>
    <div class="radio_option">
      <input type="radio" name="name_method" id="inameplot_and_scene" value="plot"/>
      <span>Name the log according to it's plot. (ie. "Plot Name, Scene 1" ).</span>
      <div class="option">
        <div class="option_title">Plot Name</div>
        <div class="option_value"><input class="text" name="plot" id="iplot" value=""/></div>
      </div>
      <div id="name_error" class="error">
      </div>
    </div>
    
  </div>
  <div class="form_block">
    <div class="form_block_title">File</div>
    <input name="file" type="file" id="ifile" />
    <div id="file_error" class="error">
    </div>
  </div>
  <div class="form_block">
    <div class="form_block_title"><span>Logger Name</span>
    <span style="font-weight: normal">(Name of the character who created the log.)</span></div>
    <input name="logger" id="idate" class="text"/>
    <div id="logger_error" class="error">
    </div>
  </div>
  <div class="form_block">
    <div class="form_block_title"><span>Date</span><span style="font-weight: normal">(MM/DD/YYYY)</span></div>
    <input name="date" id="idate" class="text" value=""/>
    <div id="date_error" class="error">
    </div>
  </div>
  <div class="form_block">
    <input type="submit" name="submit" id="isubmit" value="Upload"/>
  </div>
</form>
