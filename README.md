# eKatab

Abu Khadeejah Karl Holz <salamcast |a| gmail |d| com>

eKatab is a PHP epub reader, it can read ibook files, but their layout is much more complicated and will need more research.  the epub format is much more common than the apple format. iBooks looks cool, but I don't know many people the use them.  I only have purchased 3 books and I don't have the time to invest in that obsure format at this time, I can still read them on my iPad 4.  There are links to sites included that have public domain epub files of many classics that have been used for English classes here in Canada.

- Everything is done automatically once the classes is loaded from the auto generated launcher script.
- Access to your ebooks file resources is done with the URL path_info
- All links have been fixed on setup
- Bookmark-able links with REST-like design (GET only)
- Works with ePub files purchased from the iTunes book Store (as long as they're not DRM encrypted, i have the data printed for fairplay for each resource request)
- This has had a lot of work done fixing errors and display problems, i have seperated the setup code from the main class to help improve performance.
- The jQuery Mobile markup has been moved from the main class into its own class.
- If you're an educational institution feel free to use and modify and please provide feedback
- XSLT has been replaced with using just the DOMDocument for building the configuration from the epub XML files.
- XSLT is still used to fix the HTML and clean it up before returning it to the browser.
- INI configuration file has been replaced with a launcher script that uses serialized and base64 encoded arrays to help improve performance.  The serialized array were encoded as base64 to solve the syntax errors problem I was getting in the created launcher scripts.
- Standard epub files that are well formed (not calibre conversions) are supported; advanced epub and ibooks are not yet fully supported, text can be read while images might not load properly.

### Testing

I'm providing  "RESTful_Web_Services.epub"  epub file for testing because it’s freely available under a Creative Commons license [https://creativecommons.org/licenses/by-nc-nd/3.0/] as part of O'Reilly's Open Book Project [http://www.oreilly.com/openbook/]

This is a link to the site here, incase you want to check it out your self:

http://restfulwebapis.org/rws.html

#### Other free/public domain epub resources

Here are some resources for books that you can use with this webapp, some of these sites might require a login to download, but the books are public domain and free to download.  I'm not going to include them, i don't want to make this a bigger download.  Many classics are avaliable and historical books as well.  These are great education resources for teachers and home schoolers on a budget in a select number of different languages (no Arabic though). the Archive.org epub files might not look great and could be malformed, the OCR might contain errors; some books have the scaned pages included.

- https://standardebooks.org/
- http://www.feedbooks.com/publicdomain
- https://www.gutenberg.org/ebooks/bookshelf/
- https://www.epubbooks.com/
- https://archive.org/ [*not that great quality*]

### Setup eBook Reader

from the base directory where you cloned or extracted this project, run the following command to generate launcher scripts for each epub file found in the *./Books* directory

```bash
$> php setup.php
```

the easy way to launch this epub reader without configuring a webserver, run the command from the base directory bellow.  these is no router script, so the directory needs to be declaired ( . is for the pwd).  This is ideal for personal use, but for larger sites use a real httpd server.  If you're using windows, it would be best to use the binary release from php.net (php-xslt is inclued in that release, but not xaamp or MAMP) or install php in your WSL2 setup.

```bash
$> php -S 0.0.0.0:8080 -d .
```

### Future Direction

I would like to eventually add text to speach into this webapp for accessablity reasons.  This is a perfect feature for visually impaired and dyslexic people to enjoy books that the local library might not have avalible as an audiobook yet.

## About me

I'm Abu Khadeejah Karl, a father and husband;  I'm a full card member
of IATSE Local 58 Toronto Stagehands, I was working for a large AV
company before COVID-19 killed the events/hospitality industry in
Toronto.  so now I'm looking for full time employment in something Cloud
Computing, InfoSec or Python related.  My portfolio links are bellow:

* [https://github.com/salamcast](https://github.com/salamcast)
* [https://hub.docker.com/u/binholz](https://hub.docker.com/u/binholz)
* [https://leetcode.com/salamcast/](https://leetcode.com/salamcast/)
* [https://www.kaggle.com/abukhadeejahkarl](https://www.kaggle.com/abukhadeejahkarl)

## Support my work

* LBC: bFhwBzT2r5jCuvphQBJmX9xLUZoackz1J6
* PayPal:[https://www.paypal.com/paypalme/AbuKhadeejahKarl](https://www.paypal.com/paypalme/AbuKhadeejahKarl)
