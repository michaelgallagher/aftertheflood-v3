(function () {
'use strict';


// Bind the internal ajaxSave callback compatibility functions 
if (typeof(Bwf) != 'undefined')
{  
  Bwf.bind('bwf_epEditor', 'ajaxSave', function(){
    Bwf._cmpEpEditor();
  });
  
  Bwf.bind('bwf_wygwam', 'ajaxSave', function(){
    Bwf._cmpwygWam();
  });
  
  Bwf.bind('bwf_eeRTR', 'ajaxSave', function(){
    Bwf._cmpeeRTE();
  });
}


Bwf._cmpEpEditor = function()
{
  $('.epEditorContent').each(function(i,item) {
    var editorId = jQuery(item).attr('id'),
    fieldId,
    fieldValue;

    // Check for the different version of the epEditor - older ones don't use the underscore
    if (editorId.indexOf("_epEditorIFrame") != -1) {
      fieldId = editorId.replace(/_epEditorIFrame/,'');
    } else {
      fieldId = editorId.replace(/epEditorIFrame/,'');
    }
	
    fieldValue = jQuery(item).get(0).contentWindow.document.body.innerHTML;
    $('input[name="'+fieldId+'"]').val(fieldValue);
    
    Bwf.debug('Better Workflow: EpEditor field '+fieldId+' processed pre ajax save');
  });
};


Bwf._cmpwygWam = function()
{
  // Can we find a CKEDITOR object
  if(typeof(CKEDITOR) != 'undefined')
  {
    var instance;
    for (instance in CKEDITOR.instances) {
      CKEDITOR.instances[instance].updateElement();
      Bwf.debug('Better Workflow: CKEditor based field '+CKEDITOR.instances[instance].name+' processed pre ajax save');
    }
  }
};


Bwf._cmpeeRTE = function()
{
  var self = this;
  // Get all EE RTE fields
  $('.WysiHat-editor').each(function(i,item) {
    var fieldId = $(this).next('textarea').attr('id');
    var fieldValue = $(item).html();
    self._setEditorContents(fieldId, fieldValue);
    
    Bwf.debug('Better Workflow: EE RTE based field '+fieldId+' processed pre ajax save');
  });
};


Bwf._getEditorContents = function(obj)
{
  if(obj.get(0).nodeName.toLowerCase() == 'iframe') {
    return obj.get(0).contentWindow.document.body.innerHTML;
  }
  if(obj.get(0).nodeName.toLowerCase() == 'textarea') {
    return obj.val();
  }
};


Bwf._setEditorContents = function(fieldId, value)
{
  // If this is just a [br] tag remove it
  if(value == '<br>') value = '';
  try {
    $('#'+fieldId).val(value);
  } catch(e) {
    Bwf.debug('Better Workflow: Updating the RTE contents for field '+fieldId+' pre save failed');
  }
};

}).call(window);