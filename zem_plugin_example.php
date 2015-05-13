<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ('abc' is just an example).
// Uncomment and edit this line to override:
# $plugin['name'] = 'abc_plugin';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 0;

$plugin['version'] = '0.1';
$plugin['author'] = 'Threshold State';
$plugin['author_uri'] = 'http://thresholdstate.com/';
$plugin['description'] = 'Simple plugin examples';

// Plugin load order:
// The default value of 5 would fit most plugins, while for instance comment
// spam evaluators or URL redirectors would probably want to run earlier
// (1...4) to prepare the environment for everything else that follows.
// Values 6...9 should be considered for plugins which would work late.
// This order is user-overrideable.
# $plugin['order'] = 5;

// Plugin 'type' defines where the plugin is loaded
// 0 = public       : only on the public side of the website (default)
// 1 = public+admin : on both the public and non-AJAX admin side
// 2 = library      : only when include_plugin() or require_plugin() is called
// 3 = admin        : only on the non-AJAX admin side
// 4 = admin+ajax   : only on admin side
// 5 = public+admin+ajax   : on both the public and admin side
$plugin['type'] = 1;

// Plugin 'flags' signal the presence of optional capabilities to the core plugin loader.
// Use an appropriately OR-ed combination of these flags.
// The four high-order bits 0xf000 are available for this plugin's private use.
if (!defined('PLUGIN_HAS_PREFS')) define('PLUGIN_HAS_PREFS', 0x0001); // This plugin wants to receive "plugin_prefs.{$plugin['name']}" events
if (!defined('PLUGIN_LIFECYCLE_NOTIFY')) define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002); // This plugin wants to receive "plugin_lifecycle.{$plugin['name']}" events

$plugin['flags'] = PLUGIN_HAS_PREFS | PLUGIN_LIFECYCLE_NOTIFY;

// Plugin 'textpack' is optional. It provides i18n strings to be used in conjunction with gTxt().
$plugin['textpack'] = <<< EOT
#@public
#@language en-gb
zem_greeting => Hello,
zem_default_name => Alice
#@language de-de
zem_greeting => Hallo,
zem_default_name => Elise
#@test
#@language en-gb
zem_type_something => Type something:
#@language de-de
zem_type_something => Schreibe etwas:
EOT;

if (!defined('txpinterface'))
	@include_once('zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h1. Textile-formatted help goes here

# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

// ----------------------------------------------------
// Example public side tags

	// A simple self-closing tag
	// <txp:zem_hello_world name="Bob" />

	function zem_hello_world($atts) {
		extract(lAtts(array(
			'name'  => gTxt('zem_default_name'),
		),$atts));

		// The returned value will replace the tag on the page
		return gTxt('zem_greeting').$name;
	}

	// A simple enclosing tag
	// <txp:zem_lowercase>I LIKE SPAM</txp:lowercase>

	function zem_lowercase($atts, $thing='') {
		return strtolower(parse($thing));
	}

	// A simple conditional tag
	// <txp:zem_if_alice name="Alice">
	// Alice!
	// <txp:else />
	// Not alice.
	// </txp:zem_if_alice>

	function zem_if_alice($atts, $thing) {
		extract(lAtts(array(
			'name'  => 'Bob',
		),$atts));

		return parse(EvalElse($thing, ($name == 'Alice')));
	}

// ----------------------------------------------------
// Example admin side plugin

	// Add a new tab to the Content area.
	// "test" is the name of the associated event; "testing" is the displayed title
	if (@txpinterface == 'admin') {
		$myevent = 'test';
		$mytab = 'testing';

		// Set the privilege levels for our new event
		add_privs($myevent, '1,2');

		// Add a new tab under 'extensions' associated with our event
		register_tab("extensions", $myevent, $mytab);

		// 'zem_admin_test' will be called to handle the new event
		register_callback("zem_admin_test", $myevent);

		// 'zem_admin_test_lifecycle' will be called on plugin installation, activation, deactivation, and deletion
		register_callback("zem_admin_test_lifecycle", "plugin_lifecycle.zem_plugin_example");

		// 'zem_admin_test_prefs' will be called from the Options link on the plugin tab
		register_callback('zem_admin_test_prefs', 'plugin_prefs.zem_plugin_example');

		// Set the privilege levels for our plugin's prefs event
		add_privs('plugin_prefs.zem_plugin_example', '1');

		// Emit additional CSS rules for the admin side
		register_callback('zem_admin_test_style', 'admin_side', 'head_end');
	}

	function zem_admin_test($event, $step) {

		// ps() returns the contents of POST vars, if any
		$something = ps("something");
		pagetop("Testing", (ps("do_something") ? "you typed: $something" : ""));

		// The eInput/sInput part of the form is important, setting the event and step respectively

		echo "<div align=\"center\" style=\"margin-top:3em\">";
		echo form(
			tag("Test Form", "h3").
			graf(gTxt('zem_type_something').
				fInput("text", "something", $something, "edit", "", "", "20", "1").
				fInput("submit", "do_something", "Go", "smallerbox").
				eInput("test").sInput("step_a")
			," style=\"text-align:center\"")
		);
		echo "</div>";
	}

	// Act upon activation/deactivation, installation/deletion.
	// $event will be "plugin_lifecycle.zem_plugin_example"
	// $step will be one of "installed", "enabled", disabled", and "deleted"
	function zem_admin_test_lifecycle($event, $step) {
		// Enable/disable this plugin, then view source to see the output.
		echo comment("$event $step").n;
	}

	// Act upon plugin "Options" event.
	// $event will be "plugin_prefs.zem_plugin_example"
	function zem_admin_test_prefs($event, $step) {
		$saved = false;
		if (gps('save')) {
			// save preferences...
	  		$saved = gTxt('preferences_saved');
	  	}

	  	pagetop(gTxt('zem_example_plugin_title'), $saved);
		echo "<div class='zem_modal'></div>".
			form(
			n.hed(gTxt('zem_example_plugin_title'), 3).
			($saved ? n.graf($saved, ' class="zem_confirmation"') : '').
			n.graf('Hi there!').
			n.eInput('plugin_prefs.zem_plugin_example').
			n.graf(
				fInput('submit', 'save', gTxt('save'), 'smallerbox', '', '', '', '', 'zem_example_plugin_save').
				href(gTxt('cancel'), '?event=plugin', ' id="zem_example_plugin_cancel"'),
				' id="zem_example_plugin_buttons"'),
	        '', '', 'post', 'zem_modal'
		);
	}

	// Emit additional CSS rules for the admin side at the end of the <head> element
	function zem_admin_test_style($event, $step)
	{
		echo n.'<style type="text/css">
	div.zem_modal{background-color:black;opacity:0.2;position:absolute;top:0;left:0;width:100%;height:100%}
	form.zem_modal{z-index:1000;width:20em;position:absolute;top:30px;left:50%;margin-left:-10em;background-color:white;padding:20px;border:2px solid #fc3;}
	form.zem_modal h3{border-bottom: 1px solid #ddd;padding-bottom:2px}
	p.zem_confirmation{text-align:center;background-color:#ffffcc;border:1px solid #ffcc33;}
	#zem_example_plugin_cancel{margin-left: 1em;}
	#zem_example_plugin_buttons{border-top: 1px solid #ddd;padding-top:5px;margin-top:5px;}
	</style>
	<!--[if gte IE 5]>
	<style type="text/css">div.zem_modal{filter: alpha(opacity = 20);}</style>
	<![endif]-->'
		.n;
	}
# --- END PLUGIN CODE ---

?>
