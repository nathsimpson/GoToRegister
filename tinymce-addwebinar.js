(function() {
    tinymce.PluginManager.add( 'custom_link_class', function( editor, url ) {
        // Add Button to Visual Editor Toolbar
        editor.addButton('custom_link_class', {
            title: 'Add Webinar Registration Form',
            cmd: 'custom_link_class',
            image: url + '/icon.png',

        });
		// Add Command when Button Clicked
		editor.addCommand('custom_link_class', function() {
		    // Check we have selected some text that we want to link
		 
		    // Ask the user to enter a URL
		    var webinarKey = prompt('Please enter the webinar key');
		    if ( !webinarKey ) {
		        // User cancelled - exit
		        return;
		    }
		    if (webinarKey.length === 0) {
		        // User didn't enter a URL - exit
		        return;
		    }

		    // Insert selected text back into editor, wrapping it in an anchor tag
		    editor.execCommand('mceInsertContent', false, '[generateForm webinar_key="'+ webinarKey +'"]');
		});
    });
})();