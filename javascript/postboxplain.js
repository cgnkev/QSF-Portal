/* Used by POST_BOX_PLAIN template */

function insertCode( code, textarea ) {
  /* IE Support. (Damn you Microsoft) */
  if( document.selection ) {
    textarea.focus();
    range = document.selection.createRange();

    if(range.parentElement() != textarea) { 
      return false; 
    }

    range.text = code;
    range.select();
  }
  /* Firefox and others who also handle this correctly */
  else if( textarea.selectionStart || textarea.selectionStart == '0' ) {
    var start = textarea.selectionStart;
    var end   = textarea.selectionEnd;

    textarea.value = textarea.value.substr(0, start) + code + textarea.value.substr(end, textarea.value.length);

    textarea.focus();
    textarea.setSelectionRange( start + code.length, start + code.length );
  }
  /* Fallback case. You never know. */
  else {
    textarea += code;
  }
}

function getText(textarea) {
  if(document.selection) {
    sel = document.selection.createRange();
    return sel.text;
  } else if (window.getSelection) {
    return textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
  } else {
    return "";
  }
}

function bbCode(tag, textarea) {
  var text = getText(textarea);

  if (text) {
    var code = "[" + tag + "]" + text + "[/" + tag + "]";
  } else {
	e = document.getElementById('qsf_' + tag);
    if (e.value.indexOf("*") != -1) {
      var code = "[/" + tag + "]";
      e.value = e.value.substring(0,(e.value.length-1));
    } else {
      var code = "[" + tag + "]";
      e.value += "*";
    }
  }

  insertCode(code, textarea);
}

function bbcURL(type, textarea) {
  var text = getText(textarea);
  var isURL = (text.substring(0,7) == "http://");

  if ( type == 'img' ) {
    if (isURL) {
      var code = "[" + type + "]" + text + "[/" + type + "]";
    } else {
      var code = text + "[" + type + "]" + prompt(textarea.jsdata_url + ":","") + "[/" + type + "]";
    }
  } else if( type == 'youtube' ) {
    var code = "[" + type + "]" + prompt(textarea.jsdata_youtube_url + ":","") + text + "[/" + type + "]";
  } else {
    var code = "[" + type + "=" + (isURL ? text : prompt(textarea.jsdata_address + ":","")) + "]" + ((text && !isURL) ? text : prompt(textarea.jsdata_detail + ":","")) + "[/" + type + "]";
  }
  insertCode(code, textarea);
}

function bbcFont(attrib, list, textarea) {
  var value = list.options[list.selectedIndex].value;
  if (value && attrib) {
    insertCode("[" + attrib + "=" + value + "]" + getText(textarea) + "[/" + attrib + "]", textarea);
  }
  setTimeout(function() {
	  list.options[0].selected = true;
  	},10);
}

/* Code to handle clickable smiley's */

function insertSmiley(smiley, ta) {
  insertCode(getText(ta) + ' ' + smiley + ' ', ta);
  return false;
}

/* Functions related to creating the interfaces */

function addOptions(select, options, styleColor) {
	for	(var item in options) {
		var selectOption = document.createElement("option");
		selectOption.value = item;
		if (styleColor) {
			selectOption.style.color = item;
		}
		selectOption.appendChild(document.createTextNode(options[item]));
		select.appendChild(selectOption);
	}
}

function createSelect(descriptor, textarea) {
	var select = document.createElement("select");

	select.id = 'qsf_' + descriptor.tag;

	var selectOption = document.createElement("option");
	selectOption.appendChild(document.createTextNode(descriptor.title));
	select.appendChild(selectOption);

	addOptions(select, descriptor.options, (descriptor.tag == 'color'));

	select.onchange = function() {
		if (descriptor.action == 'bbcFont') {
			bbcFont(descriptor.tag, select, textarea);
		}
	};

	return select;
}

function createButton(descriptor, textarea) {
	var button = document.createElement("input");

	button.type = "button";
	button.title = descriptor.title;
	button.value = descriptor.value;
	button.id = 'qsf_' + descriptor.tag;

	button.onclick = function() {
		if (descriptor.action == 'bbCode') {
			bbCode(descriptor.tag, textarea);
		} else if (descriptor.action == 'bbcURL') {
			bbcURL(descriptor.tag, textarea);
		}
	};
	if (descriptor.style) {
		button.setAttribute('style', descriptor.style);
	}

	return button;
}

// Note: This is normally run onfocus on the text area it's associated with
function initTextarea(textarea) {
	textarea.buttonEvents = new Array();

	textarea.buildButtons = function(buttonArray) {
		// Builds the buttons before the text area based on the information in the array
		var bbCodeButtons = document.createElement("div");
		bbCodeButtons.id = 'qsf_bbcode_buttons';

		var oldButtonIndex = 0;

		for (var buttonIndex in buttonArray) {
			// Quickie hack to avoid problems with user-added javascript extensions.
			if (buttonArray[buttonIndex].title == "undefined")
				continue; 
			// Check if we want to add a break
			if (buttonArray[buttonIndex].action == 'bbcFont' && 
					buttonArray[oldButtonIndex].action != 'bbcFont') {
				bbCodeButtons.appendChild(document.createElement("br"));		
			}

			var button = null;
		
			if (buttonArray[buttonIndex].action == 'bbcFont') {
				button = createSelect(buttonArray[buttonIndex], textarea);
			} else {
				button = createButton(buttonArray[buttonIndex], textarea);
			}
			bbCodeButtons.appendChild(button);

			// If there is a keynum attached then add that to our events array
			if (buttonArray[buttonIndex].keynum) {
				textarea.buttonEvents[buttonArray[buttonIndex].keynum] = new Object();
				textarea.buttonEvents[buttonArray[buttonIndex].keynum].tag = buttonArray[buttonIndex].tag;
				textarea.buttonEvents[buttonArray[buttonIndex].keynum].action = buttonArray[buttonIndex].action;
				textarea.buttonEvents[buttonArray[buttonIndex].keynum].runme = function() {
					if (this.action == 'bbCode') {
						bbCode(this.tag, textarea);
					} else if (this.action == 'bbcURL') {
						bbcURL(this.tag, textarea);
					}
				};
			}

			oldButtonIndex = buttonIndex;
		}

		bbCodeButtons.appendChild(document.createElement("br"));

		textarea.parentNode.insertBefore(bbCodeButtons, textarea);
	};

	var handler = function(text) {
		// Main data fetching handler
		var responseData = text.parseJSON();

		if (responseData.length == 0) return;

		// copy across language variables
		textarea.jsdata_address = responseData['lang']['jsdata_address'];
		textarea.jsdata_detail = responseData['lang']['jsdata_detail'];
		textarea.jsdata_url = responseData['lang']['jsdata_url'];
		textarea.jsdata_youtube_url = responseData['lang']['jsdata_youtube_url'];

		// Create buttons using button data
		textarea.buildButtons(responseData['buttons']);
	};

	textarea.dataFetcher = getHTTPObject(handler);
	textarea.dataFetcher.requestData('jsdata', 'data', 'bbcode');

	textarea.caretPos = false;

	textarea.onfocus = function() {
		// Attach events to textarea but only after it has gotten the focus once

		textarea.onclick= function() {
			if (textarea.createTextRange) {
				textarea.caretPos = document.selection.createRange().duplicate();
			}
		};

		textarea.onkeyup = textarea.onclick;
		textarea.onmouseout = textarea.onclick;
		textarea.onfocus = textarea.onclick;
	}

	// Keyboard shortcuts
	textarea.onkeydown = function(e) {
		// Check if they are trying to move to another cell!
		var keynum;

		if(window.event) // IE
		{
			keynum = window.event.keyCode;
			if (!(window.event.modifiers & window.event.CONTROL_MASK)) return true;
		}
		else if(e.which) // Netscape/Firefox/Opera
		{
			keynum = e.which;
			if (!e.ctrlKey) return true;
		}

		// Handles a CTRL+keynum event to see if there's a control to use for it
		for (var eventNum in textarea.buttonEvents) {
			if (eventNum == keynum) {
				textarea.buttonEvents[eventNum].runme();
				return false;
			}
		}
		return true;
	};
}

function bbcodeInit() {
	var textarea = document.getElementById("bbcode");

	if (!textarea) {
		textarea = document.getElementById("bbcodequick");
		if (!textarea) return; // bail out

		textarea.onfocus = function() {
			initTextarea(textarea);
		}
	} else {
		initTextarea(textarea);
	}
}

addLoadEvent(bbcodeInit);