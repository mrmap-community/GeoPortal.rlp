<?php
/**
 * Language labels for module "web_txrealurlmanagementM1"
 *
 * This file is detected by the translation tool.
 */

$LOCAL_LANG = Array (
	'default' => Array (
		'preVars'=>'<p>This section can contain from zero to anz number of segments divided by "/". Each segment is bound to a GET-var by configuration in the key "preVars".</p> <p>The number of segments in the pre-vars section is determined exactly by the arrays in "preVars" configuration.</p> <p>Typical usage would be to bind a pre-var to the "L" GET parameter so the language of the site is represented by the first segment of the virtual path.</p>',
		'pagePath'=>'<p>The page path determining the page ID of the page. The default method is to just show the page ID, but through configuration you can translate say "contact/company_info/" into a page ID. The number of segments of the path used to resolve the page ID depends on the method used.</p>',
		'fixedPostVars'=>'<p>Fixed post vars is a sequence of fixed bindings between GET-vars and path segments, just as the pre-vars are. This is normally not useful to configure for a whole site since general parameters to pass around should probably be set as pre-vars, but you can configure fixed post vars for a single page where some application runs and in that case may come in handy.</p> <p>Typical usage is to apply this for a single page ID running a specific application in the system.</p>',
		'postVarSets'=>'<p>postVarSets are sequences of GET-var bindings(in pre-var style) initiated by the first segment of the path being an identification keyword for the sequence.</p> <p>Decoding of postVarSets will continue until all remaining segments of the virtual path has been translated.</p> <p>This method can be used to effectively encode groups of GET vars (sets), typically for various plugins used on the website.</p> <p>Typical usage is to configure postVarSets of each plugin on the website.</p>',
		'fileName'=>'<p>The filename is always identified as the segment of the virtual path after the last slash ("/"). In the "fileName" configuration a filename can be mapped to a number of GET vars that will be set if the filename matches the index key in the array.</p> <p>Typical usage is to use the filename to encode the "type" or "print" GET vars of a site.</p>',
		
		
		'context_menu_edit'=>'Edit',
		'context_menu_newAfter'=>'New After',
		'context_menu_newBefore'=>'New Before',
		'context_menu_newInside'=>'New Inside',
		'context_menu_delete'=>'Delete',
		'context_menu_moveUp'=>'Move Up',
		'context_menu_moveDown'=>'Move Down',
		
		'selfDefinedHosts'=>'Self Define',
		
	
		
		'realurl--host--init'=>'
						<p>General configuration of the extension</p>
						<p>This elements are supported:</p>
						<ul class="menu">
							<li><LINK "help--init--doNotRawUrlEncodeParameterNames">doNotRawUrlEncodeParameterNames</LINK></li>
							<li><LINK "help--init--enableCHashCache">enableCHashCache</LINK></li>
							<li><LINK "help--init--respectSimulateStaticURLs">respectSimulateStaticURLs</LINK></li>
							<li><LINK "help--init--appendMissingSlash">appendMissingSlash</LINK></li>
							<li><LINK "help--init--adminJumpToBackend">adminJumpToBackend</LINK></li>
							<li><LINK "help--init--postVarSet_failureMode">postVarSet_failureMode</LINK></li>
							<li><LINK "help--init--disableErrorLog">disableErrorLog</LINK></li>
							<li><LINK "help--init--enableUrlDecodeCache">enableUrlDecodeCache</LINK></li>
							<li><LINK "help--init--enableUrlEncodeCache">enableUrlEncodeCache</LINK></li>
							<li><LINK "help--init--emptyUrlReturnValue">emptyUrlReturnValue</LINK></li>
							<li><LINK "help--init--rootPageID">rootPageID</LINK></li>
							<li><LINK "help--init--enableDomainLookup">enableDomainLookup</LINK></li>
						</ul>',
		
		'help--init--doNotRawUrlEncodeParameterNames'=>'<p>Disable rawurlencoding of non-translated GET parameter names during encoding.</p><p>Background:</p><p>During the encoding of Speaking URLs from GET parameters any GET parameters that cannot be translated into a Speaking URL will be set as GET parameters again. During this process the parameter name will be rawurlencoded as it actually should according to the RFCs covering this topic.</p><p>This means that a parameter like "tx_myext[hello]=world" will become "tx_myext%5Bhello%5D=world" instead - which is not as nice visually but more correct technically.</p>',
		'help--init--enableCHashCache'=>'<p>If set, "cHash" GET parameters are stored in a cache table if they are the only parameters left as GET vars. This allows you to get rid of those remaining parameters that some plugins might use to enable caching of their parameter based content.</p>',
		'help--init--respectSimulateStaticURLs'=>'<p>If set, all requests where the Speaking URL path is only a single document with no path prefix (eg. "123.1.html") are ignored as Speaking URLs. This flag can enable backwards compatibility with old URLs using simulateStaticDocuments on the site.</p>',
		'help--init--appendMissingSlash'=>'<p>If set, the a trailing slash will be added internally to the path if it was not set by the user. For instance someone writes "http://the.site.url/contact" with no slash in the end. "contact" will be interpreted as the filename by realurl - and the user wanted it to be the directory. So this option fixes that problem.</p><p>Keyword: "ifNotFile"</p><p>You can specify the option as the keyword "ifNotFile". If you use that string as value the slash gets prepended only if the last part of the path doesn\'t look like a filename (based on the existence of a dot "." character).</p>',
		'help--init--adminJumpToBackend'=>'<p>If set, then the "admin" mode will not show edit icons in the frontend but rather direct the user to the backend, going directly to the page module for editing of the current page id.</p>',
		'help--init--postVarSet_failureMode'=>'<p>Keyword: "redirect_goodUpperDir". Will compose a URL from the parts successfully mapped and redirect to that.</p><p>Keyword: "ignore": A silent accept of the remaining parts.</p><p>Default (blank value) is a 404 page not found from TYPO3s frontend API.</p>',
		'help--init--disableErrorLog'=>'<p>If true, 404 errors are not written to the log table.</p>',
		'help--init--enableUrlDecodeCache'=>'<p>If true, caching of URL decoding is enabled.</p><p>The cache table is flushed when "all cache" is flushed in TYPO3. Entries for decode cache is valid for 24 hours by default.</p><p>If you set this option with tx_reaurl_advanced in a multi domain environment, you must also set rootPageID or enableDomainLookup. Without one of those two options, enableUrlDecodeCache may cause wrong path-to-id resolving.</p>',
		'help--init--enableUrlEncodeCache'=>'<p>If true, caching of URL encoding is enabled.</p><p>The cache table is flushed when "all cache" is flushed in TYPO3.</p>',
		'help--init--emptyUrlReturnValue'=>'<p>If the URL is empty it usually is meant to be a link to the frontpage.</p><p>If you set this value to a string, that will be the URL returned if the URL is otherwise empty.</p><p>If you set this value true (PHP boolean, "TRUE"), then it will return the baseURL set in TSFE.</p><p>Setting it to "./" should work as a reference to the root as well. But it is not so beautiful.</p>',
		'help--init--rootPageID'=>'<p>If set and non-zero, defines root page UID of the web site for this configuration. This setting should be set if a number of sites are hosted in a single database (single page tree) and one of alternative path-to-id methods is used (tx_realurl_advanced or other).</p><p>If a number of domains use alternative path-to-id resolving method and two pages in different sites produce the same path, realurl will not be able to determine correct page id if this setting is not set.</p><p>This option is not required if enableUrlDecodeCache is set to false.</p><p>See also "<LINK "help--init--enableDomainLookup">enableDomainLookup</LINK>".</p><p>Default value: none</p>',
		'help--init--enableDomainLookup'=>'<p>This is an alternative method of determining root page ID for multi domain environment.</p><p>If rootPageID is not set and this setting is set, realurl will find root page ID using current site name and domain record. Admin user must add domain record to the root of web site and check "Is root of web site" box in the page properties of root page of each site.</p><p>This method is for lazy people. Since it has to make a domain lookup, it is slower and less effective than rootPageID. If rootPageID is set, this setting is ignored.</p><p>This option is not required if enableUrlDecodeCache is set to false.</p><p>Default value: false.</p>',
	),
	'dk' => Array (
	),
	'de' => Array (
	),
	'no' => Array (
	),
	'it' => Array (
	),
	'fr' => Array (
	),
	'es' => Array (
	),
	'nl' => Array (
	),
	'cz' => Array (
	),
	'pl' => Array (
	),
	'si' => Array (
	),
	'fi' => Array (
	),
	'tr' => Array (
	),
	'se' => Array (
	),
	'pt' => Array (
	),
	'ru' => Array (
	),
	'ro' => Array (
	),
	'ch' => Array (
	),
	'sk' => Array (
	),
	'lt' => Array (
	),
	'is' => Array (
	),
	'hr' => Array (
	),
	'hu' => Array (
	),
	'gl' => Array (
	),
	'th' => Array (
	),
	'gr' => Array (
	),
	'hk' => Array (
	),
	'eu' => Array (
	),
	'bg' => Array (
	),
	'br' => Array (
	),
	'et' => Array (
	),
	'ar' => Array (
	),
	'he' => Array (
	),
	'ua' => Array (
	),
	'lv' => Array (
	),
	'jp' => Array (
	),
	'vn' => Array (
	),
	'ca' => Array (
	),
	'ba' => Array (
	),
	'kr' => Array (
	),
	'eo' => Array (
	),
	'my' => Array (
	),
);
?>