function _uInfo(page) {
 var p,s="",dm="",pg=_udl.pathname+_udl.search;
 if (page && page!="") pg=_uES(page,1);
 if (_ubd.referrer.search(/search.yahoo/) != -1 && _ubd.referrer.search(/&rp=/) != -1) { 
  var ref = _ubd.referrer;
  var pkwregex = new RegExp("&rp=([^&]+)");
  var pkw = pkwregex.exec(ref);
  var kwregex = new RegExp("&p=([^&]+)");
  var kw = kwregex.exec(ref);
  var tldregex = new RegExp("search.yahoo.([^\/]+)");
  var tldmatch = tldregex.exec(ref);
  _ur = "http://search.yahoo." + tldmatch[1] + "/search?p=" + kw[1] + "%20[" +pkw[1] +"]";
 } else if (_ubd.referrer.search(/images.google/) != -1) {
  var ref = _ubd.referrer;
  var tldregex = new RegExp("images.google.([^\/]+)");
  var tldmatch = tldregex.exec(ref);
  var imgregex = new RegExp("&prev=([^&]+)");
  var refmatch = imgregex.exec(ref);
  refmatch = refmatch[1].replace(/%26/g,"&");
  refmatch = refmatch.replace(/%3F/g,"?");
  refmatch = refmatch.replace(/%3D/g,"=");
  _ur = "http://www.images.google." + tldmatch[1] + refmatch;
 } else {
  _ur=_ubd.referrer;	
 }
 if (!_ur || _ur=="") { _ur="-"; }
 else {
  dm=_ubd.domain;
  if(_utcp && _utcp!="/") dm+=_utcp;
  p=_ur.indexOf(dm);
  if ((p>=0) && (p<=8)) { _ur="0"; }
  if (_ur.indexOf("[")==0 && _ur.lastIndexOf("]")==(_ur.length-1)) { _ur="-"; }
 }
 s+="&utmn="+_uu;
 if (_ufsc) s+=_uBInfo();
 if (_uctm) s+=_uCInfo();
 if (_utitle && _ubd.title && _ubd.title!="") s+="&utmdt="+_uES(_ubd.title);
 if (_udl.hostname && _udl.hostname!="") s+="&utmhn="+_uES(_udl.hostname);
 s+="&utmr="+_ur;
 s+="&utmp="+pg;
 if ((_userv==0 || _userv==2) && _uSP()) {
  var i=new Image(1,1);
  i.src=_ugifpath+"?"+"utmwv="+_uwv+s;
  i.onload=function() {_uVoid();}
 }
 if ((_userv==1 || _userv==2) && _uSP()) {
  var i2=new Image(1,1);
  i2.src=_ugifpath2+"?"+"utmwv="+_uwv+s+"&utmac="+_uacct+"&utmcc="+_uGCS();
  i2.onload=function() { _uVoid(); }
 }
 return;
}