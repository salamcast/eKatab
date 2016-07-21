@author Karl Holz <newaeon|a|mac|d|com>
@package RESTphulSrv
@licence <in header REST.class.php>

//#############################################################################################
 RESTphulSrv - RESTful API made easy with PHP5
//#############################################################################################
This is an Abstract PHP5 class, so it will need to be extended with your new class.  

This script is to help you from reinventing the wheel everytime you want to write a simple RESTful Web Service with PHP, 
This Abstract Class will aid you in cutting down dev time without the bloat of large frame works or cms solutions.

I'm building this class because I find my self repeating many of the same things, checking the same variables over and over.


I have have added support for:
- transforming XML/XHTML with XSLT 
- processing HTTP GET/POST/PUT/DELETE/HEAD/OPTIONS Methods
- process Basic HTTP Auth
- generate uuid with a prefix
- generate debugging output with the HTTP Method and timestamp for the filename
- many of the default class values are generated from the _SERVER variable values, they can be overriden
- ini based configuration support for each path_info based link
- 

Please Refer to the RESTfulBugger.php to see what this class does.  

You can also view the PHP documentation for more details on this object