// This JavaScript was developed by GA-Experts (http://www.ga-experts.co.uk) for Google Analytics
// Please use this code to overwrite the default set of Search Engines in the GA tracking code
// Use this code at your own risk.  GA Experts takes no responsibility for errors, loss of data or any other complications arising from the use of this code
// Version 2.0 (Mar 2007) - many thanks to Brian Clifton and Tomas Remotigue for help with this
// For enquiries please contact mail@ga-experts.co.uk

// Dutch Search Engines added by Joost de Valk (http://www.joostdevalk.nl)
// For enquiries specifically about the Dutch SE's please contact joost@joostdevalk.nl

var _uOsr=new Array();	
var _uOkw=new Array();

// Google EMEA Image domains
_uOsr[_uOsr.length]="images.google.co.uk";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="images.google.es";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="images.google.pt";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="images.google.it";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="images.google.fr";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="images.google.nl";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="images.google.be";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="images.google.de";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="images.google.no";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="images.google.se";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="images.google.dk";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="images.google.fi";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="images.google.ch";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="images.google.at";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="images.google.ie";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="images.google.ru";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="images.google.pl";	_uOkw[_uOsr.length]="q";

// Other Google Image search
_uOsr[_uOsr.length]="images.google.com";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="images.google.ca";		_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="images.google.com.au";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="images.google";	_uOkw[_uOsr.length]="q";

// Blogsearch
_uOsr[_uOsr.length]="blogsearch.google";	_uOkw[_uOsr.length]="q";

// Google EMEA Domains
_uOsr[_uOsr.length]="google.co.uk";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="google.es";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="google.pt";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="google.it";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="google.fr";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="google.nl";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="google.be";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="google.de";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="google.no";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="google.se";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="google.dk";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="google.fi";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="google.ch";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="google.at";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="google.ie";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="google.ru";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="google.pl";	_uOkw[_uOsr.length]="q";

// Yahoo EMEA Domains
_uOsr[_uOsr.length]="uk.yahoo.com";	_uOkw[_uOsr.length]="p";
_uOsr[_uOsr.length]="es.yahoo.com";	_uOkw[_uOsr.length]="p";
_uOsr[_uOsr.length]="pt.yahoo.com";	_uOkw[_uOsr.length]="p";
_uOsr[_uOsr.length]="it.yahoo.com";	_uOkw[_uOsr.length]="p";
_uOsr[_uOsr.length]="fr.yahoo.com";	_uOkw[_uOsr.length]="p";
_uOsr[_uOsr.length]="nl.yahoo.com";	_uOkw[_uOsr.length]="p";
_uOsr[_uOsr.length]="be.yahoo.com";	_uOkw[_uOsr.length]="p";
_uOsr[_uOsr.length]="de.yahoo.com";	_uOkw[_uOsr.length]="p";
_uOsr[_uOsr.length]="no.yahoo.com";	_uOkw[_uOsr.length]="p";
_uOsr[_uOsr.length]="se.yahoo.com";	_uOkw[_uOsr.length]="p";
_uOsr[_uOsr.length]="dk.yahoo.com";	_uOkw[_uOsr.length]="p";
_uOsr[_uOsr.length]="fi.yahoo.com";	_uOkw[_uOsr.length]="p";
_uOsr[_uOsr.length]="ch.yahoo.com";	_uOkw[_uOsr.length]="p";
_uOsr[_uOsr.length]="at.yahoo.com";	_uOkw[_uOsr.length]="p";
_uOsr[_uOsr.length]="ie.yahoo.com";	_uOkw[_uOsr.length]="p";
_uOsr[_uOsr.length]="ru.yahoo.com";	_uOkw[_uOsr.length]="p";
_uOsr[_uOsr.length]="pl.yahoo.com";	_uOkw[_uOsr.length]="p";

// UK specific
_uOsr[_uOsr.length]="hotbot.co.uk";		_uOkw[_uOsr.length]="query";
_uOsr[_uOsr.length]="excite.co.uk";		_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="bbc";			_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="tiscali";			_uOkw[_uOsr.length]="query";
_uOsr[_uOsr.length]="uk.ask.com";		_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="blueyonder";		_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="search.aol.co.uk";		_uOkw[_uOsr.length]="query";
_uOsr[_uOsr.length]="ntlworld";			_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="tesco.net";		_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="orange.co.uk";		_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="mywebsearch.com";		_uOkw[_uOsr.length]="searchfor";
_uOsr[_uOsr.length]="uk.myway.com";		_uOkw[_uOsr.length]="searchfor";
_uOsr[_uOsr.length]="searchy.co.uk";		_uOkw[_uOsr.length]="search_term";
_uOsr[_uOsr.length]="msn.co.uk";		_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="uk.altavista.com";		_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="lycos.co.uk";		_uOkw[_uOsr.length]="query";

// NL specific
_uOsr[_uOsr.length]="chello.nl";			_uOkw[_uOsr.length]="q1";
_uOsr[_uOsr.length]="home.nl";				_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="planet.nl";			_uOkw[_uOsr.length]="googleq=q";
_uOsr[_uOsr.length]="search.ilse.nl";			_uOkw[_uOsr.length]="search_for";
_uOsr[_uOsr.length]="search-dyn.tiscali.nl";		_uOkw[_uOsr.length]="key";
_uOsr[_uOsr.length]="startgoogle.startpagina.nl";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="vinden.nl";			_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="vindex.nl";			_uOkw[_uOsr.length]="search_for";
_uOsr[_uOsr.length]="zoeken.nl";			_uOkw[_uOsr.length]="query";
_uOsr[_uOsr.length]="zoeken.track.nl";			_uOkw[_uOsr.length]="qr";
_uOsr[_uOsr.length]="zoeknu.nl";			_uOkw[_uOsr.length]="Keywords";

// Extras
_uOsr[_uOsr.length]="alltheweb";		_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="ananzi";			_uOkw[_uOsr.length]="qt";
_uOsr[_uOsr.length]="anzwers";			_uOkw[_uOsr.length]="search";
_uOsr[_uOsr.length]="araby.com";		_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="dogpile";			_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="elmundo.es";		_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="ezilon.com";		_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="hotbot";			_uOkw[_uOsr.length]="query";
_uOsr[_uOsr.length]="indiatimes.com";		_uOkw[_uOsr.length]="query";
_uOsr[_uOsr.length]="iafrica.funnel.co.za";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="mywebsearch.com";		_uOkw[_uOsr.length]="searchfor";
_uOsr[_uOsr.length]="rambler.ru";		_uOkw[_uOsr.length]="words";
_uOsr[_uOsr.length]="search.aol.com";		_uOkw[_uOsr.length]="encquery";
_uOsr[_uOsr.length]="search.indiatimes.com";	_uOkw[_uOsr.length]="query";
_uOsr[_uOsr.length]="searcheurope.com";		_uOkw[_uOsr.length]="query";
_uOsr[_uOsr.length]="suche.web.de";		_uOkw[_uOsr.length]="su";
_uOsr[_uOsr.length]="terra.es";			_uOkw[_uOsr.length]="query";
_uOsr[_uOsr.length]="voila.fr";			_uOkw[_uOsr.length]="kw";

// Default GA (sorted)
_uOsr[_uOsr.length]="about";		_uOkw[_uOsr.length]="terms";
_uOsr[_uOsr.length]="alice";		_uOkw[_uOsr.length]="qs";
_uOsr[_uOsr.length]="alltheweb";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="altavista";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="aol";		_uOkw[_uOsr.length]="encquery";
_uOsr[_uOsr.length]="aol";		_uOkw[_uOsr.length]="query";
_uOsr[_uOsr.length]="ask";		_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="baidu";		_uOkw[_uOsr.length]="wd";
_uOsr[_uOsr.length]="cnn";		_uOkw[_uOsr.length]="query";
_uOsr[_uOsr.length]="gigablast";	_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="google";		_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="live.com";		_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="looksmart";	_uOkw[_uOsr.length]="qt";
_uOsr[_uOsr.length]="lycos";		_uOkw[_uOsr.length]="query";
_uOsr[_uOsr.length]="mamma";		_uOkw[_uOsr.length]="query";
_uOsr[_uOsr.length]="msn";		_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="najdi";		_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="netscape";		_uOkw[_uOsr.length]="s";
_uOsr[_uOsr.length]="search";		_uOkw[_uOsr.length]="q";
_uOsr[_uOsr.length]="seznam";		_uOkw[_uOsr.length]="w";
_uOsr[_uOsr.length]="virgilio.it";	_uOkw[_uOsr.length]="qs";
_uOsr[_uOsr.length]="voila.fr";		_uOkw[_uOsr.length]="kw";
_uOsr[_uOsr.length]="yahoo";		_uOkw[_uOsr.length]="p";
_uOsr[_uOsr.length]="yandex.ru";	_uOkw[_uOsr.length]="text";