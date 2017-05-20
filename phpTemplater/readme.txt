###		phpTemplater	###
---------------------------

This module is dedicated to rendering the HTML of the page from a single XML file that
Contains the webpage theme and each pages differences from the template.

---HOW TO---
------------

	>>>ADDING THE MODULE<<<
	-----------------------
	
		In the module.cfg you want to append the json with this:
		
		{
			"MODULEName"	:	"phpTemplater",			=>		The name you will be using
																to reference the module
																
			"MODULESrc"		:	"phpTemplater/",		=>		The module root directory,
																used to load the related
																scripts
																
			"MODULEScripts"	: 	["template.php"],		=>		The related scripts
			
			"XMLSrc"		:	"pages.xml",			=>		The XML file that contains
																the page configuration
																
			"ASSET_ROOT"	:	""						=>		Where the handler should go
																look for additional scripts
																when generating the page.
		}
		
		
	>>>XML FORMAT<<<
	----------------
		
		+++PAGES+++
		-----------
		
			IF you dont know XML then I guess its safe to say that you should have one big tag
			around all the "page" tags. But of cource you know that.
			
			The "page" contains all the tag information for the page and has 2 attributes.
			
			The first is the 'label', which is required seeing as it will be your way of refering
			to it.
			
			The second is optional and is the "parent" attribute. This attribute tells the parser
			to insert this page into the parent depending on each nodes override rules.
		
				<page label='yourlabelhere' parent='myparent'><page/>
		
			Your page declaration without any child nodes would at best look like this.
	
		+++NODES+++
		-----------
		
			Within the parent tags you should define 'node' tags. These define rendering functions
			and have a few attributes.
			
			-----------------------------------------------------------------------------------------
			
			Every node has the optinal 'ID' and 'class' attribute that will reflect in the HTML.
			
			In addition to these 2 attributes there is a 3rd attribute that allows you a little
			free reign. 
			
			"meta_args" allows you to insert unprocessed values into the tag. E.g.
			
			<node type='static-input' meta_args='type="text" required' />
			
			this will render a text input box with the required attribute.
			
			-----------------------------------------------------------------------------------------
			
			Then there is 'type'. This defines what it will be rendering. 
			There are the following types:
				
				static			->		defines a static HTML tag
										argument:	any static HTML tag
										
				asset			->		defines an asset script that should be executed there
										argument:	the path of the file to be included
				
				text			->		just renders some text
										argument:	the text to render
				
				part			->		defines another structure that should be filled in.
										argument:	the parts path
				
			Now the format for the type value is a bit different.
			
				type-argument
				
			e.g.
				
				static-HTML			-> 	this will define a static HTML tag.
				
				asset-login.php		->	this will insert the login script into the render process
				
				text-Bannnaaaaaa	->	will print out 'Bannnaaaaaa'
				
				part-Navbar.xml		->	Will fetch and insert the navbar nodes into the render
	
			------------------------------------------------------------------------------------------
			
			If the page has a parent declared the nodes on the outside get to define overwrite conditions.
			Meaning that if the conditions are met they overwrite certain elements in the parent.
	
			The syntax makes use of CSS syntax.
	
			
			This being the parent 
			
				<page label='parent'>
					<node type='static-html'>
						<node type='static-div' id='test' />
					</node>
				</page>
			
			
			and this the child
			
				<page label='child' parent='parent'>
					<node type='static-p' overwrite='html/#test-rp' />
				</page>
				
			The child will overwrite the node with the id "test" and replace it with a <p> element.
	
			The format for the overwrite attribute is as follows:
				
				target_string-rule
			
			the target_string is the elements to navigate, be it class, ID or static element while
			the rule defines what the node should to to its target once it finds it.
	
	
			/\/RULES OF REPLACEMENT/\/
			--------------------------
			
				There are 4 overwrite rules
				
				pr		-> 	preappends the contents to that of the target
					
				rp		->	replaces the contents of the target completely
				
				po		->	post appends the contents to that of the target
				
				alt		->	does nothing to the contents but does alter the attributes
	
			+++EXAMPLE+++
			-------------
					
				<xml>
					<page label="index">
						<node type="static-html">
							<node type="static-head">
								
							</node>
							
							<node type="static-body">
								<node type='static-p'>
									<node type='text-This is some symple text' />
								</node>
							</node>
						</node>
					</page>
				</xml>