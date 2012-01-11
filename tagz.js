$.require('/G87/js/FB/Facebook.js');

var tagz = 
{
  url: '/t/',
  
	// TODO: check if the second parameter is an object 
	//  or a function. If its a function then assume its
	//  a success callback else the object will have the 
	//  success, error and complete callbacks. (this is for 
	//  all core functions)
  query: function(query, callback)
  {
    $.getJSON(tagz.url, {q: query}, callback);
  },
  
  create: function(obj, callback)
  {
		var objJson = JSON.stringify(obj);
		
		$.ajax({
			type: 'POST',
			url: tagz.url,
			data: {obj: objJson},
			success: callback
		});
  },
  
  update: function(obj, callback) 
  {
    var objJson = JSON.stringify(obj);
		
		$.ajax({
			type: 'PUT',
			url: tagz.url,
			data: {obj: objJson},
			success: callback
		});
  },
  
  remove: function(objId, callback)
  {
    $.ajax({
			type: 'DELETE',
			url: tagz.url,
			data: {id: objId},
			success: callback
		});
  },
  
  // This is to handle the tag input field
  input: 
  {
    tagUrl: "/t/?q="+escape("--selectCol='tags'"),
    
    _tagsList: null,
    
    // Sets the text field as a tagz input field.
		// params
		//  input: Selector for the input field 
    set: function(params)
    {
      if(!params)  return;
      var input = $(params.input);
      // Is the tagList populated?
      if(tagz.input._tagsList != null)
      {
        // Run autocomplete on the input field
        tagz.input._attachAutocomplete(input);
        return;
      }
      
      // Make a request to populate the tagsList and then run auto complete on the input
//      $.getJSON(tagz.input.tagUrl, function(data)
//      {
//        tagz.input._tagsList = data;
//        tagz.input._attachAutocomplete(input);
//      });

      tagz.query("--selectCol='tags'", function(data)
			{
				tagz.input._tagsList = data;
				tagz.input._attachAutocomplete(input);
			});
    },
		
		_split: function(val)
    {
      return val.split(/,\s*/);
    },
    
    _extractLast: function(term)
    {
      return tagz.input._split(term).pop();
    },
    
    _attachAutocomplete: function(textField)
    {
      $(textField).autocomplete(
      {
        minLength: 0,
        source: function( request, response ) 
        {
          // delegate back to autocomplete, but extract the last term
          response($.ui.autocomplete.filter(tagz.input._tagsList, tagz.input._extractLast(request.term)));
        },
        // source: tagzUrl, 
        focus: function() 
        {
          // prevent value inserted on focus
          return false;
        },
        select: function( event, ui ) 
        {
          var terms = tagz.input._split( this.value );
          // remove the current input
          terms.pop();
          // add the selected item
          terms.push( ui.item.value );
          // add placeholder to get the comma-and-space at the end
          terms.push( "" );
          this.value = terms.join( ", " );
          return false;
        }
      });
    }
  },
  
  requestLogin: function() {
    Facebook.requestLogin();
  },
  
  requestLogout: function() {
    Facebook.requestLogout();
  },
  
  renderUserPanel: function(sel) {
    Facebook.panel.render(sel);
  },
  
  on: function(eventType, handler) {
    Facebook.on(eventType, handler);
  },
  
  off: function(eventType, handler) {
    Facebook.off(eventType, handler);
  }
};
