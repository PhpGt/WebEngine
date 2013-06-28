/**
 * Gt.js JavaScript library to complement PHP.Gt.
 * Project is hosted on Github: http://github.com/g105b/Gt.js
 *
 * v0.0.2 (Alpha 2)
 * Commit c58e1df59f540694d1f12c45709b7ee67650a11e
 *
 * There are two purposes of the Gt.js library. First is to normalise browser's
 * behaviour using a collection of shims. The shims attempt 100% ECMAScript5/6
 * coverage and DOM 4 implementation. Supports Chrome, Firefox, Opera,
 * Safari, IE8+. Shims taken from:
 * https://github.com/termi/ES5-DOM-SHIM
 *
 * Browser support is IE8+, Google Chrom[e|ium], Mozilla Firefox 3.6+,
 * Opera 11+. To support IE8, a conditional comment must be used to load
 * Gt-IE8.js.
 *
 * On top of the browser normalisation, helper functions are added to global
 * scope and to DOM objects:
 * 
 * go([namespace], [callback], [page])
 * All parameters are optional. If namespace is provided as a string, the
 * namespace will be initialised and optional callback will be executed in
 * the namespace's context. The callback function will be invoked when the DOM
 * is ready. If page is given as a string or RegExp, the callback is only
 * invoked if the current pathname matches the page provided.
 *
 * namespace(name, fn)
 * Initialises the given namespace and extends with the given function. The
 * function is not automatically invoked.
 *
 * http(url, [method], [data], [callback], [xhr])
 * Performs an asynchronous HTTP request to given url. Method is one of GET,
 * POST, PUT, DELETE, HEAD or OPTIONS. Data is an object or querystring that
 * will be added to the correct area of the request, depending on method
 * selected. Callback will be invoked in context of the browser's actual
 * XMLHttpRequest object. Response data can be accessed through this.response.
 *
 * api(name)
 * A shorthand method to creating an http call, passing the correct parameters
 * to the server for the given api, and can also be extended to provide
 * supplementary IO.
 *
 * dom(selector)
 * A shorthand to document.querySelectorAll(). Note that Gt.js extends the
 * functionality of the returned NodeList object, so that methods and properties
 * of each DOMNode can be invoked/accessed via the NodeList itself. Example:
 * dom("body > p").addClass("root"); // adds "root" to all root paragraphs.
 * 
 * template(name)
 * Any elements with data-template attributes will be extracted from the DOM
 * and are clonable using this function. The extraction of elements will be done
 * server-side if running through PHP.Gt, but this function will still work.
 *
 * tool(name)
 * Access tool-specific methods that have been provided by PHP.Gt. Documentation
 * for these methods is within the tools' code.
 *
 * For short-hand coding, querySelector() and querySelectorAll() functions are
 * shortened to qs() and qsa().
 */

/* ES6/DOM4 polyfill | @version 0.7 final | MIT License | github.com/termi */
;(function() {"use strict";
var g=void 0,h=!0,i=null,l=!1,m=window,n,q=Object.prototype,aa=Function.prototype.apply,r=Array.prototype.slice,s=String.prototype.split,ca=Array.prototype.splice,t,da,ea,u=Function.prototype.bind||function(a,b){var c=this,d=r.call(arguments,1);return function(){return aa.call(c,a,d.concat(r.call(arguments)))}},v=u.call(Function.prototype.call,q.hasOwnProperty);function w(a,b,c){return aa.call(a,b,r.call(arguments,2))} function x(a,b){for(var c=1;c<arguments.length;c++){var d=arguments[c],e;for(e in d)v(d,e)&&!v(a,e)&&(a[e]=d[e])}return a}var fa="a"!=Object("a")[0]||!(0 in Object("a"));function y(a,b){if(a==i&&!b)throw new TypeError;return fa&&"string"==typeof a&&a?s.call(a,""):Object(a)}var ga=q.toString;function ha(a){var b=Object.create(DOMException.prototype);b.code=DOMException[a];b.message=a+": DOM Exception "+b.code;throw b;}function ia(){return l}function ja(a){return a} var ka="every filter forEach indexOf join lastIndexOf map reduce reduceRight reverse slice some toString".split(" "),z=m.Element&&m.Element.prototype||{},A=u.call(document.__orig__createElement__||document.createElement,document),B=A("p"),C,la=/\s+$/g,ma=/\s+/g,D=1,E,F,na,oa=/^(\w+)?((?:\.(?:[\w\-]+))+)?$|^#([\w\-]+$)/,pa=/\s*([,>+~ ])\s*/g,qa,G,H,I,K,L,ra,M,N,sa,ta,O,ua,P,Q="\t\n\x0B\f\r \u00a0\u1680\u180e\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u202f\u205f\u3000\u2028\u2029\ufeff", va,wa,xa,ya,za,Aa,S,T,Ba,U,V,W=m.Date,X,Ca,Y,Z,Da,Ea,$;n=(n=/msie (\d+)/i.exec(navigator.userAgent)||[])&&+n[1]||g;m.HTMLDocument||(m.HTMLDocument=m.Document);m.Document||(m.Document=m.HTMLDocument);m.DocumentFragment||(m.DocumentFragment=m.HTMLDocument);Object.append=x;Object.extend||(Object.extend=function(a,b){for(var c=1;c<arguments.length;c++){var d=arguments[c],e;for(e in d)v(d,e)&&(a[e]=d[e])}return a});na=function(a,b){a[b]=Object.getOwnPropertyDescriptor(this,b);return a}; Object.inherits=function(a,b){a.prototype=Object.create((a.superclass=b).prototype,(a.prototype&&Object.getOwnPropertyNames(a.prototype)||[]).reduce(na.bind(a.prototype),{constructor:{value:a,enumerable:l}}))}; Function.prototype.bind||(Function.prototype.bind=function(a,b){function c(){return aa.call(d,this instanceof c?this:a,e.concat(r.call(arguments)))}if("function"!=typeof this&&(!n||0!==P.call(this+"").indexOf("function")))throw new TypeError("Function.prototype.bind called on incompatible "+this);var d=this,e=r.call(arguments,1);d.prototype&&(c.prototype=Object.create(d.prototype));return c}); var Ha="toString toLocaleString valueOf hasOwnProperty isPrototypeOf propertyIsEnumerable constructor".split(" "),Ia=!{toString:i}.propertyIsEnumerable(Ha[0]),Ja=Ha.length; x(Object,{keys:function(a){if("object"!=typeof a&&"function"!=typeof a||a===i)throw new TypeError("Object.keys called on a non-object");var b=[],c;for(c in a)v(a,c)&&b.push(c);if(Ia)for(c=0;c<Ja;c++)v(a,Ha[c])&&b.push(Ha[c]);return b},getOwnPropertyNames:function(a){return Object.keys(a)},seal:ja,freeze:ja,preventExtensions:ja,isSealed:ia,isFrozen:ia,isExtensible:function(a){if(Object(a)!==a)throw new TypeError;for(var b="";v(a,b);)b+="?";a[b]=h;var c=v(a,b);delete a[b];return c},getPrototypeOf:function(a){return a.__proto__|| (a.constructor?a.constructor.prototype:q)},create:function(a,b){var c;if(a===i)c={__proto__:i};else{if("object"!=typeof a)throw new TypeError("typeof prototype["+typeof a+"] != 'object'");c=function(){};c.prototype=a;c=new c;c.__proto__=a}b&&Object.defineProperties(c,b);return c},is:function(a,b){return a===b?0===a?1/a===1/b:h:a!==a&&b!==b},isnt:function(a,b){return!Object.is(a,b)}}); if(Object.defineProperty&&(xa=function(a){try{return Object.defineProperty(a,"sentinel",{}),"sentinel"in a}catch(b){return l}},za=xa({}),Aa=xa(B),!za||!Aa))S=Object.defineProperty,T=Object.defineProperties; if(!Object.defineProperty||S)Object.defineProperty=function(a,b,c){if("object"!=typeof a&&"function"!=typeof a||a===i)throw new TypeError("Object.defineProperty called on non-object: "+a);if("object"!=typeof c&&"function"!=typeof c||c===i)throw new TypeError("Property description must be an object: "+c);if(S)try{return S.call(Object,a,b,c)}catch(d){if(-2146823252===d.number){c.enumerable=l;try{return S.call(Object,a,b,c)}catch(e){}}}if(c.value!==g)if(a.__defineGetter__&&(a.__lookupGetter__(b)||a.__lookupSetter__(b))){var f= a.__proto__;a.__proto__=q;delete a[b];a[b]=c.value;a.__proto__=f}else a[b]=c.value;else if(a.__defineGetter__)c.get!==g&&a.__defineGetter__(b,c.get),c.set!==g&&a.__defineSetter__(b,c.set);else if(Object.defineProperty.ielt8)c.get!==g&&(a["get"+b]=c.get),c.set!==g&&(a["set"+b]=c.set);else throw new TypeError("getters & setters not supported");return a};z.ie&&8>n&&(z.ielt8=Object.defineProperty.ielt8=h); if(!Object.defineProperties||T)Object.defineProperties=function(a,b){if(T)try{return T.call(Object,a,b)}catch(c){}for(var d in b)v(b,d)&&Object.defineProperty(a,d,b[d]);return a};if(Object.getOwnPropertyDescriptor){ya=function(a){try{return a.sentinel2=0,0===Object.getOwnPropertyDescriptor(a,"sentinel2").value}catch(b){return l}};Ba=ya({});if((U=ya(B))&&document.__proto__)try{U=!!Object.getOwnPropertyDescriptor(document.__proto__,"firstChild")}catch(Ka){U=l}if(!U||!Ba)V=Object.getOwnPropertyDescriptor} if(!Object.getOwnPropertyDescriptor||V)Object.getOwnPropertyDescriptor=function(a,b){if("object"!=typeof a&&"function"!=typeof a||a===i)throw new TypeError("Object.getOwnPropertyDescriptor called on a non-object: "+a);if(V)try{return V.call(Object,a,b)}catch(c){}if(v(a,b)){var d={enumerable:h,configurable:h},e,f;if(a.__defineGetter__){var k=a.__proto__;a.__proto__=q;e=a.__lookupGetter__(b);f=a.__lookupSetter__(b);a.__proto__=k}else Object.defineProperty.ielt8&&(e=a["get"+b],f=a["set"+b]);if(e||f)return e&& (d.get=e),f&&(d.set=f),d;d.value=a[b];return d}};if(!Object.d||V){var La=Object.d;Object.d=function(a,b){if(La)try{return La.call(Object,a,b)}catch(c){}for(var d=Object.getOwnPropertyDescriptor(a,b),e=a;!d&&(e=Object.getPrototypeOf(e));)d=Object.getOwnPropertyDescriptor(e,b);return d}}2!=[1,2].splice(0).length&&(Array.prototype.splice=function(a,b){return a===g&&b===g?[]:ca.apply(this,[a===g?0:a,b===g?this.length-a:b].concat(r.call(arguments,2)))}); ea=function(a){if(a instanceof Array||Array.isArray(a))return a;if(a.m)return a.m();var a=y(a,h),b=a.length>>>0,c;try{c=r.call(a)}catch(d){}if(c&&c.length===b)return c;c=[];for(var e=0;e<b;e++)e in a&&(c[e]=a[e]);return c};x(Array,{isArray:function(a){return"[object Array]"==ga.call(a)},from:ea,of:function(a){return r.call(arguments)}}); x(Array.prototype,{reduce:function(a,b){var c=y(this),d=c.length>>>0,e=0;if(0===d&&1>=arguments.length)throw new TypeError("Array length is 0 and no second argument");for(b===g&&(b=(++e,c[0]));e<d;++e)e in c&&(b=w(a,g,b,c[e],e,c));return b},reduceRight:function(a,b){var c=y(this),d=c.length>>>0;if(0===d&&1>=arguments.length)throw new TypeError("Array length is 0 and no second argument");--d;for(b===g&&(b=(--d,c[d+1]));0<=d;--d)d in c&&(b=w(a,g,b,c[d],d,c));return b},forEach:function(a,b){for(var c= y(this),d=c.length>>>0,e=-1;++e<d;)e in c&&w(a,b,c[e],e,c)},indexOf:function(a,b){var c=y(this),d=c.length>>>0;if(!d||(b=~~b)>=d)return-1;for(b=(d+b)%d;b<d&&(!(b in c)||c[b]!==a);b++);return b^d?b:-1},lastIndexOf:function(a,b){var c=y(this),d=c.length>>>0,e;if(!d)return-1;e=d-1;b!==g&&(e=Math.min(e,Number.toInteger(b)));for(e=0<=e?e:d-Math.abs(e);0<=e;e--)if(e in c&&c[e]===a)return e;return-1},every:function(a,b,c){c===g&&(c=h);var d=c;O(this,function(e,f){d==c&&(d=!!w(a,b,e,f,this))},this);return d}, some:function(a,b){return ua.call(this,a,b,l)},filter:function(a,b){for(var c=y(this),d=c.length>>>0,e=[],f,k=0;k<d;k++)k in c&&(f=c[k],w(a,b,f,k,c)&&e.push(f));return e},map:function(a,b){for(var c=y(this),d=c.length>>>0,e=[],f=0;f<d;f++)f in c&&(e[f]=w(a,b,c[f],f,this));return e},contains:function(a){return!!~this.indexOf(a)}});O=u.call(Function.prototype.call,Array.prototype.forEach);da=Array.prototype.map;ua=Array.prototype.every; if(!String.prototype.trim||Q.trim())Q="["+Q+"]",va=RegExp("^"+Q+Q+"*"),wa=RegExp(Q+Q+"*$"),String.prototype.trim=function(){return String(this).replace(va,"").replace(wa,"")};P=String.prototype.trim;"0".split(g,0).length&&(String.prototype.split=function(a,b){return a===g&&0===b?[]:s.call(this,a,b)});t=function(a,b){return!!~this.indexOf(a,b|0)}; x(String.prototype,{repeat:function(a){if(0>a)return"";for(var b="",c=this;a;)if(a&1&&(b+=c),a>>=1)c+=c;return b},startsWith:function(a,b){return this.indexOf(a,b|=0)===b},endsWith:function(a,b){return this.lastIndexOf(a,b)===(0<=b?b|0:this.length-1)},contains:t,toArray:function(){return s.call(this,"")},reverse:function(){return Array.prototype.reverse.call(s.call(this+"","")).join("")}}); x(Number,{isFinite:function(a){return"number"===typeof a&&m.i(a)},isInteger:function(a){return Number.isFinite(a)&&-9007199254740992<=a&&9007199254740992>=a&&Math.floor(a)===a},isNaN:function(a){return Object.is(a,NaN)},toInteger:function(a){a=+a;return Number.isNaN(a)?0:0===a||!m.i(a)?a:(0>a?-1:1)*Math.floor(Math.abs(a))}});G=function(a,b){var c=document.createEvent("Events"),b=b||{};c.initEvent(a,b.bubbles||l,b.cancelable||l);"isTrusted"in c||(c.j=l);return c}; try{I=Event.prototype,new Event("click")}catch(Ma){m.Event=G,I?G.prototype=I:I=G.prototype}H=function(a,b){var c,d;try{c=document.createEvent("CustomEvent")}catch(e){c=document.createEvent("Event")}b=b||{};d=b.detail!==g?b.detail:i;(c.initCustomEvent||(c.detail=d,c.initEvent)).call(c,a,b.bubbles||l,b.cancelable||l,d);"isTrusted"in c||(c.j=l);return c};try{K=(m.CustomEvent||Event).prototype,new CustomEvent("magic")}catch(Na){if(m.CustomEvent=H,K||I)H.prototype=K||I} try{E=document.createEvent("Event")}catch(Oa){E={}}"defaultPrevented"in E||(Object.defineProperty(I,"defaultPrevented",{value:l}),ra=I.preventDefault,I.preventDefault=function(){this.defaultPrevented=h;ra.apply(this,arguments)}); "stopImmediatePropagation"in E||(L=function(a){var b=this.f,c=this.g;if("function"!==typeof b)if("handleEvent"in b)c=b,b=b.handleEvent;else return;if(a.timeStamp&&a.__stopNow===a.timeStamp)a.stopPropagation();else return b.apply(c,arguments)},I.stopImmediatePropagation=function(){this.__stopNow=this.timeStamp||(this.timeStamp=(new Date).getTime())}); if("addEventListener"in B&&!B.addEventListener.__shim__){E=0;try{F=function(){E++},B.addEventListener("click",F),B.addEventListener("click",F),B.click?B.click():B.dispatchEvent(new G("click"))}catch(Pa){}if(0==E||2==E||L){var Qa=2==E;O([m.HTMLDocument&&m.HTMLDocument.prototype||m.document,m.Window&&m.Window.prototype||m,z],function(a){if(a){var b=a.addEventListener,c=a.removeEventListener;b&&(a.addEventListener=function(a,c,f){var k,j,f=f||l;if(Qa||L){j="_e_8vj"+(f?"-":"")+(c.__UUID__||(c.__UUID__= ++D))+a;if(!(k=this._))k=this._={};if(j in k)return;c=L?k[j]=u.call(L,{f:c,g:this}):k[j]=g;c}return b.call(this,a,c,f)},c&&(a.removeEventListener=function(a,b,f){var k,j,f=f||l;if(Qa||L)if((k=this._)&&k[j="_e_8vj"+(f?"-":"")+b.__UUID__+a])b=k[j],delete k[j];return c.call(this,a,b,f)}))}})}}M=function(a,b,c){this.e=a;this.a=b;this.c=c;this.length=0;this.value="";this.b("1")}; N=function(a,b){var c=b||"",d=!!a.length;if(d){for(;0<a.length;)delete a[--a.length];a.value=""}c&&((c=P.call(c))&&s.call(c,ma).forEach(N.add,a),a.value=b);d&&a.a&&a.a.call(a.c,a.value)};N.add=function(a){this[this.length++]=a};N.k=function(a,b,c,d){return c&&a.length+c<d.length?" ":""}; x(M.prototype,{b:function(a){var b=this.e.call(this.c);b!=this.value&&N(this,b);a===g&&ha("WRONG_ARGUMENTS_ERR");""===a&&ha("SYNTAX_ERR");t.call(a+""," ")&&ha("INVALID_CHARACTER_ERR")},add:function(){var a=arguments,b=0,c=a.length,d,e=this.value,f;do d=a[b],f=this.contains(d),f||(e+=(0<b||e&&!e.match(la)?" ":"")+d,this[this.length++]=d);while(++b<c);this.value=e;this.a&&this.a.call(this.c,this.value)},remove:function(){var a=arguments,b=0,c=a.length,d,e=this.value,f=s.call(this.value," ");do d=a[b], this.b(d),e=e.replace(RegExp("((?:\\ +|^)"+d+"(?:\\ +|$))","g"),N.k);while(++b<c);for(a=this.length-1;0<a;--a)if(!(this[a]=f[a]))this.length--,delete this[a];this.value=e;this.a&&this.a.call(this.c,this.value)},contains:function(a){this.b(a);return t.call(" "+this.value+" "," "+a+" ")},item:function(a){this.b("1");return this[a]||i},toggle:function(a,b){var c=this.contains(a),d=c?b!==h&&"remove":b!==l&&"add";if(d)this[d](a);return c}});M.prototype.toString=function(){return this.value||""}; m.DOMStringCollection=M;I.AT_TARGET||(I.AT_TARGET=2,I.BUBBLING_PHASE=3,I.CAPTURING_PHASE=1);Event.AT_TARGET||(Event.AT_TARGET=2,Event.BUBBLING_PHASE=3,Event.CAPTURING_PHASE=1);try{m.getComputedStyle(B)}catch(Ra){m.getComputedStyle=u.call(function(a,b){return this.call(m,a,b||i)},m.getComputedStyle)}E=!("classList"in B)?g:(B.classList.add(1,2),B.classList.contains(2))&&h||l; if(!E)if(E===g)ta=function(a){this.className=a},sa=function(){return this.className},Object.defineProperty(z,"classList",{get:function(){if(this.tagName){var a=this._||(this._={});a._ccl_||(a._ccl_=new M(sa,ta,this));return a._ccl_}}});else if(E===l&&(E=m.DOMTokenList)&&(E=E.prototype)){var Sa=E.add,Ta=E.remove;E.add=function(){O(arguments,Sa,this)};E.remove=function(){O(arguments,Ta,this)};E.toggle=M.prototype.toggle} "parentElement"in B||Object.defineProperty(z,"parentElement",{get:function(){var a=this.parentNode;return a&&1===a.nodeType?a:i}});"contains"in B||(m.Node.prototype.contains=function(a){return!!(this.compareDocumentPosition(a)&16)}); "insertAdjacentHTML"in B||(m.HTMLElement.prototype.insertAdjacentHTML=function(a,b){var c=this.ownerDocument.createElement("_"),d={beforebegin:"before",afterbegin:"prepend",beforeend:"append",afterend:"after"},e;c.innerHTML=b;(c=c.childNodes)&&(c.length&&(e=this[d[a]]))&&e.apply(this,c)});if(document.importNode&&!document.importNode.shim)try{document.importNode(B)}catch(Ua){var Va=document.importNode;delete document.importNode;document.importNode=function(a,b){b===g&&(b=h);return Va.call(this,a,b)}} try{B.cloneNode()}catch(Wa){[Node.prototype,Comment&&Comment.prototype,z,ProcessingInstruction&&ProcessingInstruction.prototype,Document.prototype,DocumentType&&DocumentType.prototype,DocumentFragment.prototype].forEach(function(a){if(a){var b=a.cloneNode;delete a.cloneNode;a.cloneNode=function(a){a===g&&(a=h);return b.call(this,a)}}})} z.matchesSelector||(z.matchesSelector=z.webkitMatchesSelector||z.mozMatchesSelector||z.msMatchesSelector||z.oMatchesSelector||function(a,b){if(!a)return l;if("*"===a)return h;var c,d,e,f=0,k,j,p;b?"length"in b?c=b[0]:(c=b,b=g):c=this;do if(p=l,c===document.documentElement?p=":root"===a:c===document.body&&(p="BODY"===a.toUpperCase()),!p)if(a=P.call(a.replace(pa,"$1")),k=a.match(oa))switch(a.charAt(0)){case "#":p=c.id===a.slice(1);break;default:if((p=!k[1]||!("tagName"in c)||c.tagName.toUpperCase()=== k[1].toUpperCase())&&k[2]){e=-1;j=k[2].slice(1).split(".");for(k=" "+c.className+" ";j[++e]&&p;)p=t.call(k," "+j[e]+" ")}}else{if(!/([,>+~ ])/.test(a)&&(d=c.parentNode)&&d.querySelector)p=d.querySelector(a)===c;if(!p&&(d=c.ownerDocument)){j=d.querySelectorAll(a);for(e=-1;!p&&j[++e];)p=j[e]===c}}while(p&&b&&(c=b[++f]));return b&&"length"in b?p&&b.length==f:p});document.documentElement.matchesSelector||(document.documentElement.matchesSelector=z.matchesSelector); "matches"in z||(z.matches=document.documentElement.matches=z.matchesSelector); B.prepend||(C=function(a){var b=i,c=0,d=a.length,a=da.call(a,C.l);if(1===d)return a[0];b=document.createDocumentFragment();a=Array.from(a);for(c=0;c<d;++c)b.appendChild(a[c]);return b},C.l=function(a){return"string"===typeof a?document.createTextNode(a):a},z.after||(z.after=function(){this.parentNode&&this.parentNode.insertBefore(C(arguments),this.nextSibling)}),z.before||(z.before=function(){this.parentNode&&this.parentNode.insertBefore(C(arguments),this)}),z.append||(z.append=function(){this.appendChild(C(arguments))}), z.prepend||(z.prepend=function(){this.insertBefore(C(arguments),this.firstChild)}),z.replace||(z.replace=function(){this.parentNode&&this.parentNode.replaceChild(C(arguments),this)}),z.remove||(z.remove=function(){this.parentNode&&this.parentNode.removeChild(this)}),"prepend"in document||(document.prepend=m.Document.prototype.prepend=m.DocumentFragment.prototype.prepend=function(){z.prepend.apply(this.documentElement,arguments)},document.append=m.Document.prototype.append=m.DocumentFragment.prototype.append= function(){z.append.apply(this.documentElement,arguments)})); "find"in document||(qa=/(\:scope)(?=[ >~+])/,document.find=m.Document.prototype.find=m.DocumentFragment.prototype.find=function(a,b){b&&("length"in b||(b=[b]))||(b=[this]);var c,d=0,e=b.length,f;do f=b[d],a=a.replace(qa,9==f.nodeType?":root":function(){return"#"+(f.id||(f.id="find"+ ++D))}),c=f.querySelector(a);while(!c&&++d<e);return c||i},document.findAll=m.Document.prototype.findAll=m.DocumentFragment.prototype.findAll=function(a,b){b&&("length"in b||(b=[b]))||(b=[this]);var c=[],d=0,e=b.length, f,k,j,p,ba,R={},J;do{f=b[d];a=a.replace(qa,9==f.nodeType?":root":function(){return"#"+(f.id||(f.id="find"+ ++D))});k=f.querySelectorAll(a);p=0;for(ba=k.length;p<ba;++p)if(j=k[p],J=l,j=j.__UUID__||(J=h,j.__UUID__=++D),J||!(j in R))R[j]=g,c.push(k[p])}while(++d<e);return c});"find"in z||(z.find=z.querySelector,z.findAll=z.querySelectorAll); "labels"in A("input")||Object.defineProperty(z,"labels",{enumerable:h,get:function(){if(t.call(" INPUT BUTTON KEYGEN METER OUTPUT PROGRESS TEXTAREA SELECT "," "+this.nodeName.toUpperCase()+" ")){for(var a=this,b=this.id?ea(document.querySelectorAll("label[for='"+this.id+"']")):[],c=b.length-1;(a=a.parentNode)&&(!a.h||a.h===this);)if("LABEL"===a.nodeName.toUpperCase()){for(;b[c]&&b[c].compareDocumentPosition(a)&2;)c--;ca.call(b,c+1,0,a)}return b}}}); "control"in A("label")||(Da=function(a,b){for(var c=0,d=a.length;c<d;c++){var e=a[c],f=b(e);if(f||e.childNodes&&0<e.childNodes.length&&(f=Da(e.childNodes,b)))return f}},Object.defineProperty(m.HTMLLabelElement&&m.HTMLLabelElement.prototype||z,"control",{enumerable:h,get:function(){return"LABEL"!==this.nodeName.toUpperCase()?g:this.hasAttribute("for")?document.getElementById(this.htmlFor):Da(this.childNodes,function(a){if(t.call(" INPUT BUTTON KEYGEN METER OUTPUT PROGRESS TEXTAREA SELECT "," "+a.nodeName.toUpperCase()+ " "))return a})||i}})); "reversed"in A("ol")||(Ea=function(a){var b=a.getAttribute("reversed"),c=a._,d,e;c||(c=a._={});"olreversed"in c&&c.olreversed==(b!==i)||(d=a.children,e=a.getAttribute("start"),e!==i&&(e=Number(e),isNaN(e)&&(e=i)),b!==i?(c.olreversed=h,e===i&&(e=d.length),O(d,function(a){a.value=e--})):(c.olreversed=l,d[0]&&(d[0].value=e||0),O(d,function(a){a.removeAttribute("value")})))},Object.defineProperty(m.HTMLOListElement&&m.HTMLOListElement.prototype||z,"reversed",{get:function(){return"OL"!==(this.nodeName|| "").toUpperCase()?g:this.getAttribute("reversed")!==i},set:function(a){if("OL"===(this.nodeName||"").toUpperCase())return this[(a?"set":"remove")+"Attribute"]("reversed",""),Ea(this),a}}),$=function(){document.removeEventListener("DOMContentLoaded",$,l);$=g;O(document.getElementsByTagName("ol"),Ea)},"complete"==document.readyState?$():document.addEventListener("DOMContentLoaded",$,l)); [document.getElementsByClassName&&document.getElementsByClassName("")||{},document.querySelectorAll&&document.querySelectorAll("#z")||{}].forEach(function(a){(a=a.__proto__||a.constructor.prototype)&&a!==Array.prototype&&ka.forEach(function(b){a[b]||(a[b]=Array.prototype[b])})});E=document.createElement("form");E.innerHTML="<input type=radio name=t value=1><input type=radio checked name=t value=2>"; E.t&&2!==E.t.value&&(E=(E=E.t)&&(E=E.constructor)&&E.prototype||(E=m.NodeList)&&E.prototype)&&E!==Object.prototype&&Object.defineProperty(E,"value",{get:function(){if(this[0]&&"form"in this[0])for(var a=this.length,b;b=this[--a];)if(b.checked)return b.value},set:function(a){if(this[0]&&"form"in this[0])for(var b=this.length,c;c=this[--b];)if(c.checked)return c.value=a,c.value},configurable:h}); if(!W.prototype.toISOString||t.call((new W(-621987552E5)).toISOString(),"-000001")||"1969-12-31T23:59:59.999Z"!==(new W(-1)).toISOString())W.prototype.toISOString=function(){var a,b,c,d;if(!isFinite(this))throw new RangeError("Date.prototype.toISOString called on non-finite value.");d=this.getUTCFullYear();a=this.getUTCMonth();d+=~~(a/12);a=[(a%12+12)%12+1,this.getUTCDate(),this.getUTCHours(),this.getUTCMinutes(),this.getUTCSeconds()];d=(0>d?"-":9999<d?"+":"")+("00000"+Math.abs(d)).slice(0<=d&&9999>= d?-4:-6);for(b=a.length;b--;)c=a[b],10>c&&(a[b]="0"+c);return d+"-"+a.slice(0,2).join("-")+"T"+a.slice(2).join(":")+"."+("000"+this.getUTCMilliseconds()).slice(-3)+"Z"};W.now||(W.now=function(){return(new W).getTime()});var Xa;if(!(Xa=!W.prototype.toJSON)){var Ya;if(!(Ya=t.call((new W(-621987552E5)).toJSON(),"-000001"))){var Za;a:{try{Za=W.prototype.toJSON.call({toISOString:function(){return-1}});break a}catch($a){}Za=g}Ya=~Za}Xa=Ya}Xa&&(W.prototype.toJSON=function(){return w(this.toISOString,this)}); X=function(a,b,c,d,e,f,k){var j=arguments.length;return this instanceof W?(j=1==j&&String(a)===a?new W(Date.parse(a)):7<=j?new W(a,b,c,d,e,f,k):6<=j?new W(a,b,c,d,e,f):5<=j?new W(a,b,c,d,e):4<=j?new W(a,b,c,d):3<=j?new W(a,b,c):2<=j?new W(a,b):1<=j?new W(a):new W,j.constructor=X,j):W.apply(this,arguments)};Ca=RegExp("^(\\d{4}|[+-]\\d{6})(?:-(\\d{2})(?:-(\\d{2})(?:T(\\d{2}):(\\d{2})(?::(\\d{2})(?:\\.(\\d{3}))?)?(Z|(?:([-+])(\\d{2}):(\\d{2})))?)?)?)?$"); Y=[0,31,59,90,120,151,181,212,243,273,304,334,365];Z=function(a){return Math.ceil(a/4)-Math.ceil(a/100)+Math.ceil(a/400)};for(E in W)X[E]=W[E];X.now=W.now;X.UTC=W.UTC;X.prototype=W.prototype;X.prototype.constructor=X; X.parse=function(a){var b=Ca.exec(a);if(b){var c=Number(b[1]),d=Number(b[2]||1),e=Number(b[3]||1),f=Number(b[4]||0),k=Number(b[5]||0),j=Number(b[6]||0),p=Number(b[7]||0),ba=b[8]?0:Number(new Date(1970,0)),R="-"===b[9]?1:-1,J=Number(b[10]||0),b=Number(b[11]||0),Fa=Z(c),Ga=Z(c+1);if(f<(0<k||0<j||0<p?24:25)&&60>k&&60>j&&1E3>p&&24>J&&60>b&&0<d&&13>d&&0<e&&e<1+Y[d]-Y[d-1]+(2===d?Ga-Fa:0))if(c=365*(c-1970)+(2<d?Ga:Fa)-Z(1970)+Y[d-1]+e-1,c=1E3*(60*(60*(24*c+f+J*R)+k+b*R)+j)+p+ba,-864E13<=c&&864E13>=c)return c; return NaN}return W.parse.apply(this,arguments)};m.Date=X;if((E=m._)&&E.ielt9shims)E.ielt9shims.forEach(w),m._=E.orig_;x=E=B=ka=A=G=H=I=K=F=z=X=ia=za=Aa=g; })();

/* GT.js core library. | @version 0.2 | Apache2 License | github.com/g105b */
;(function() {

var 
// List of callbacks to invoke on DOM ready event. Added to the list using
// public go() function.
_goQueue = [],
// Map of elements that have been extracted from the DOM due to having a
// data-template attribute. 
_templateMap = {},
// Functions that are added to object prototypes. Prior to these being added,
// browsers' objects are all normalised to support ES5 and some ES6 standards.
// These functions are not shims, but useful helpers intended to speed up
// development.
_helpers = {
	"Node": {
		/**
		 * There is no insertAfter function in JavaScript, but it can be
		 * achieved by using insertBefore and nextSibling, which is wrapped in
		 * this function.
		 * @param  {Node} newEl The element to insert.
		 * @param  {Node} refEl The reference element to insert after.
		 * @return {Node}       The inserted node.
		 */
		"insertAfter": function(newEl, refEl) {
			return this.insertBefore(newEl, refEl.nextSibling);
		},
		/**
		 * Reverse-searches up the DOM tree for a given selector, returns the
		 * first match. The match is a usual CSS selector, so to select an
		 * element further up the tree, use a more specific CSS selector.
		 * @param  {string} selector CSS selector to match upon.
		 * @return {HTMLElement}          The first matching element, or null.
		 */
		"parent": function(selector) {
			var currentElement = this;
			do {
				currentElement = currentElement.parentElement;
				if(currentElement.matches(selector)) {
					return currentElement;
				}
			}while(currentElement);
			
			return null;
		},
		/**
		 * Short-hand function to querySelector.
		 */
		"qs": function(selector, context) {
			return this.querySelector(selector, context);
		},
		/**
		 * Short-hand function to querySelectorAll.
		 */
		"qsa": function(selector, context) {
			return this.querySelectorAll(selector, context);
		},
		/**
		 * Removes the element from the DOM.
		 * @return HTMLElement A reference to the removed item.
		 */
		"remove": function() {
			return this.parentNode.removeChild(this);
		},
		/**
		 * Replaces the current element with the provided element.
		 * @return HTMLElement The replaced node.
		 */
		"replace": function(element) {
			return this.parentNode.replaceChild(element, this);
		},
		/**
		 * Removes all child nodes from the HTMLElement.
		 * @return HTMLElement A reference to the parent item.
		 */
		"removeAllChildren": function() {
			var childrenLength = this.children.length,
				i = 0,
				child;
			for(; i < childrenLength; i++) {
				child = this.children[i];
				child.parentNode.removeChild(child);
			}
			return this;
		}
	},
},

/**
 * NodeList helpers are Node methods that are exposed on the NodeList 
 * prototype. When called, they will either be invoked on the first Node or 
 * on all nodes within the list (depending on 'first' or 'all' within this 
 * object).
 *
 * List of properties/methods from: 
 * https://developer.mozilla.org/en-US/docs/DOM/element
 */
_nodeListHelpers = {
	"properties": {
		"first": [
			"attributes",
			"baseURI",
			"baseURIObject",
			"childElementCount",
			"childNodes",
			"children",
			"classList",
			"className",
			"clientHeight",
			"clientLeft",
			"clientTop",
			"clientWidth",
			"contentEditable",
			"dataset",
			"dir",
			"firstChild",
			"firstElementChild",
			"id",
			"innerHTML",
			"isContentEditable",
			"lang",
			"lastChild",
			"lastElementChild",
			"localName",
			"name",
			"namespaceURI",
			"nextSibling",
			"nextElementSibling",
			"nodeName",
			"nodePrincipal",
			"nodeType",
			"nodeValue",
			"offsetHeight",
			"offsetLeft",
			"offsetParent",
			"offsetTop",
			"offsetWidth",
			"outerHTML",
			"ownerDocument",
			"parentNode",
			"prefix",
			"previousSibling",
			"previousElementSibling",
			"scrollHeight",
			"scrollLeft",
			"scrollTop",
			"scrollWidth",
			"spellcheck",
			"style",
			"tabIndex",
			"tagName",
			"textContent",
			"title"
		],
		"all": [
		]
	},
	"methods": {
		"first": [
			"appendChild",
			"blur",
			"click",
			"cloneNode",
			"compareDocumentPosition",
			"focus",
			"getAttribute",
			"getAttributeNS",
			"getAttributeNode",
			"getAttributeNodeNS",
			"getBoundingClientRect",
			"getClientRects",
			"getElementsByClassName",
			"getElementsByTagName",
			"getElementsByTagNameNS",
			"getUserData",
			"hasAttribute",
			"hasAttributeNS",
			"hasAttributes",
			"hasChildNodes",
			"insertBefore",
			"isDefaultNamespace",
			"isEqualNode",
			"isSameNode",
			"isSupported",
			"lookupPrefix",
			"matchesSelector",
			"webkitMatchesSelector",
			"mozMatchesSelector",
			"requestFullScreen",
			"mozRequestFullScreen",
			"webkitRequestFullScreen",
			"parent",
			"querySelector",
			"querySelectorAll",
			"qs",
			"qsa",
			"removeChild",
			"replaceChild",
			"scrollIntoView",
			"setAttribute",
			"setAttributeNS",
			"setAttributeNode",
			"setAttributeNodeNS",
			"setCapture",
			"setUserData",
			"insertAdjacentHTML"
		],
		"all": [
			"addEventListener",
			"dispatchEvent",
			"normalise",
			"remove",
			"removeAttribute",
			"removeAttributeNS",
			"removeAttributeNode",
			"removeEventListener",
			"replace",
			"reset"
		]
	}
},

/**
 * Adds the above list of methods and properties to the NodeList prototype.
 */
// TODO: MAKE THE PROPERTIES WORK - they are called as methods!
_addNodeListHelpers = function() {
	var addLen, i, fnName, helperType;
	for(helperType in _nodeListHelpers) {
		if(!_nodeListHelpers.hasOwnProperty(helperType)) {
			continue;
		}
		addLen = _nodeListHelpers[helperType].first.length;
		for(i = 0; i < addLen; i++) {
			fnName = _nodeListHelpers[helperType].first[i];
			(function(c_fnName) {
				if(helperType === "properties") {
					Object.defineProperty(NodeList.prototype, c_fnName, {
						"get": function() {
							return this.item(0) ? this.item(0)[c_fnName] : null;
						},
						"set": function(val) {
							return this[c_fnName] = val;
						}
					})
				}
				else if(helperType === "methods") {
					NodeList.prototype[c_fnName] = function() {
						return this.item(0)[c_fnName].apply(
							this.item(0), 
							arguments);
					}
				}
			})(fnName);
		}

		addLen = _nodeListHelpers[helperType].all.length;
		for(i = 0; i < addLen; i++) {
			fnName = _nodeListHelpers[helperType].all[i];
			(function(c_fnName) {
				NodeList.prototype[c_fnName] = function() {
					var elLen = this.length,
						el_i = 0,
						result, currentResult, el;
					for(; el_i < elLen; el_i++) {
						el = this.item(el_i);
						currentResult = el[c_fnName].apply(el, arguments);
						result = result || currentResult;
					}

					return result;
				}
			})(fnName);
		}
	}
},

/**
 * Stores configuration options for quick access.
 */
Gt = {
	"version": 			"0.0.1",
},

/**
 * Initialises a namespace with root node attached to the global window object.
 * Used for keeping code out of global scope or extending functions through
 * multiple files.
 * @param  {string}   name Full namespace, separated by dots.
 * @param  {Function} fn   The function to attach to the created namespace.
 * @return {Object}        A reference to the namespace.
 */
namespace = function(name, fn) {
	var nsArray = name.split("."),
		nsArrayLen = nsArray.length,
		i = 0,
		currentRef = window,
		fn = fn;
	for(; i < nsArrayLen; i++) {
		if(!currentRef[nsArray[i]]) {
			if(fn
			&& i === nsArrayLen - 1) {
				currentRef[nsArray[i]] = fn;
			}
			else {
				currentRef[nsArray[i]] = {};
			}
		}
		currentRef = currentRef[nsArray[i]];
	}

	return currentRef;
},

api = function(name, method, properties, callback) {
	throw "Not implemented API yet";
	return apiObj;
},

/**
 * Shorthand function for document.querySelectorAll, takes optional second
 * parameter that switches context from document, for performing qsa on 
 * sub-nodes.
 * @param  {string} selector CSS selector to perform on context
 * @param  {HTMLElement} context  Optional. By default, document is used.
 * @return {NodeList}          NodeList of selected elements.
 */
dom = function(selector, context) { 
	if(!context) {
		context = document;
	}
	return context.querySelectorAll.call(context, selector);
},

/**
 * Obtains a deep clone of a templated element.
 * @param  {String} name The template's name, obtained from the data-template
 * attribute on the original element.
 * @return {HTMLElement} The cloned element.
 */
template = function(name) {
	if(_templateMap.hasOwnProperty(name)) {
		return _templateMap[name].cloneNode(true);
	}
},

/**
 * An accessor for the list of tools registered by the current application.
 * @param  {string} name The name of the tool to access.
 * @return {object}      The tool's object namespace.
 */
tool = function(name) {
	if(window.Tool
	&& window.Tool[name]) {
		return window.Tool[name];
	}
	else {
		throw "No tool code found for " + name;
	}
},

/**
 * Provides shorthand to XMLHttpRequest functionality. 
 * @param  {string} url 		The URL to request.
 * @param  {string} method 		Optional. The HTTP method to use. Defaults to
 * GET. Possible values: GET, POST, PUT, DELETE, HEAD.
 * @param  {object} properties 	Key-value-pairs to send in the request. The
 * values will be placed in the URL or body where necessary for the given 
 * method, or merged with existing query parameters if required.
 * @param  {function} callback	A function to invoke when response is made. The
 * function will be invoked in the context of the XMLHttpRequest object, so
 * `this` will can be used to get status code, etc.
 * @param {XMLHttpRequest} xhr An existing XMLHttpRequest object to use.
 * @return {XMLHttpRequest}     The actual XMLHttpRequest object used in the 
 * request.
 */
http = function(url /*,[method],[properties],[callback],[xhr],[responseType]*/){
	var method, responseType, properties, callback, xhr, arg_i, data,
		queryString, qsChar, qsArray, qsArrayEl, qsProperties, prop,
		formData;

	for(arg_i = 1; arg_i < arguments.length; arg_i++) {
		if(typeof arguments[arg_i] == "string") {
			arguments[arg_i] = arguments[arg_i].toLowerCase();
			if(arguments[arg_i] == "text"
			|| arguments[arg_i] == "arraybuffer"
			|| arguments[arg_i] == "blob"
			|| arguments[arg_i] == "document"
			|| arguments[arg_i] == "json") {
				responseType = arguments[arg_i];
			}
			else {
				method = arguments[arg_i];
			}
		}
		else if(typeof arguments[arg_i] == "object") {
			if(arguments[arg_i] instanceof XMLHttpRequest) {
				xhr = arguments[arg_i]
			}
			else {
				properties = arguments[arg_i];				
			}
		}
		else if(typeof arguments[arg_i] == "function") {
			callback = arguments[arg_i];
		}
		else {
			throw new TypeError("http method passed invalid parameters");
		}
	}

	if(!method) {
		method = "get";
	}
	if(!properties) {
		properties = {};
	}
	if(!xhr) {
		xhr = new XMLHttpRequest();
	}

	qsProperties = {};
	if(method == "get" || method == "delete") {
		// Properties must be in the query string. 
		qsChar = "?";
		if(url.indexOf("?") >= 0) {
			qsChar = "&";
		}
		for(prop in properties) {
			if(!properties.hasOwnProperty(prop)) {
				continue;
			}
			// Append to the querystring.
			url += [
				qsChar,			// Query string character (? or &).
				prop,			// Key.
				"=",			// =
				properties[prop]// Value.
			].join("");			// Build string.
			qsChar = "&";
		}

		// Reset the properties object, so it isn't added to the
		// request body.
		properties = {};
	}

	if(callback) {
		xhr.addEventListener("readystatechange", function() {
			if(this.readyState === 4) {
				callback.call(xhr);
			}
		});
	}

	xhr.open(method, url, true);
	xhr.setRequestHeader("Gtjs", Gt.version);
	if(responseType) {
		xhr.responseType = responseType;
	}
	if(method == "post" || method == "put") {
		formData = new FormData();
		for(prop in properties) {
			if(!properties.hasOwnProperty(prop)) {
				continue;
			}
			formData.append(prop, properties[prop]);
		}
		
		xhr.send(formData);
	}
	else {
		xhr.send();
	}
	return xhr;
},

/**
 * Extracts all elements from the DOM that have the data-template attribute,
 * or elements that are children of the PhpGt_Template_Element element (added 
 * by PHP.Gt projects). Elements are stored in the _templateMap	object.
 */
_templateLoad = function() {
	var templateContainer = document.getElementById("PhpGt_Template_Elements"),
		elementList,
		elementListLen,
		i, el;

	if(!templateContainer) {
		templateContainer = document.createElement("div");
		elementList = document.querySelectorAll("[data-template]");
		elementListLen = elementList.length;
		for(i = 0; i < elementListLen; i++) {
			el = elementList[i];
			el.parentNode.removeChild(el);
			templateContainer.appendChild(el);
		}
	}

	// templateContainer now contains all data-template elements, whether
	// running PHP.Gt or using GT.js standalone.
	elementList = templateContainer.children;
	elementListLen = elementList.length;
	while(el = elementList[0]) {
		_templateMap[el.getAttribute("data-template")] = el.cloneNode(true);
		el.parentNode.removeChild(el);
	}

	if(templateContainer.parentNode) {
		templateContainer.parentNode.removeChild(templateContainer);
	}
},

/**
 * Extends the browser's native objects (that are already normalised) to have
 * helper functions defined in the object map above.
 */
_attachHelpers = function() {
	var objName, objToExtend, funcName;
	for(objName in _helpers) {
		if(!_helpers.hasOwnProperty(objName)) {
			continue;
		}
		if(!window[objName]) {
			console.log("GT.js error trying to extend " + objName);
			continue;
		}
		objToExtend = window[objName];

		for(funcName in _helpers[objName]) {
			if(!_helpers[objName].hasOwnProperty(funcName)) {
				continue;
			}

			objToExtend.prototype[funcName] = _helpers[objName][funcName];
		}
	}
},

/**
 * Creates an event listener on the DOMContentLoaded event, invokes all
 * callbacks in the _goQueue array.
 */
_goListen = function() {
	document.addEventListener("DOMContentLoaded", function() {
		document.removeEventListener(
			"DOMContentLoaded", 
			arguments.callee,
			false
		);
		_goInvoke();

	}, false);
},

/**
 * Iterates over all callbacks in the go queue and invokes them in order. An
 * optional page can be supplied that will only invoke matching pages. This
 * adds the functionality of calling window.go() at any time to invoke a go
 * script for another page (such as for an ajax response).
 * 
 * @param {string|RegExp} page Optional. The page to invoke go queue upon.
 * @return {int} Number of callbacks invoked.
 */
_goInvoke = function(pageContext) {
	var page = pageContext || window.location.pathname,
		goQueueLen = _goQueue.length,
		i = 0, count = 0;
	for(; i < goQueueLen; i++) {
		if(_goQueue[i].page) {
			if(_goQueue[i].page instanceof RegExp) {
				if(!_goQueue[i].page.test(page)) {
					continue;
				}
			}
			else if(_goQueue[i].page !== page) {
				continue;
			}
		}
		else if(pageContext) {
			// If a page has been supplied, but the current go queue index
			// has no page associated, skip it.
			continue;
		}

		_goQueue[i].callback.call(_goQueue[i].namespace);
		count ++;
	}

	return count;
},

/**
 * Used to call a function after the page loads, with an optional context of a
 * namespace, and with an optional invoke conditional on the current pathname.
 * If only one argument is given as a string, the go queue will be invoked as 
 * if the browser has just reached the page with the string as the URL.
 * 
 * @param  {String} ns			Optional. The name of a namespace to invoke the 
 * given function in context of - existing or requiring initialisation.
 * @param  {Function} cb		The only required parameter. Reference to the 
 * function to invoke. Without a given namespace, it will be invoked in
 * anonymous context.
 * @param {String|RegExp} page	Either a string or regular expression matching 
 * pathnames to only invoke the function on.
 * @return {Bool}				True if page matches pathname, else false.
 */
go = function(cbOrNs, pageOrCb /*, page */) {
	var pathname = window.location.pathname,
		cb, ns = null, page = null;

	// Passing a single string will invoke the go queue for the given page.
	if(arguments.length === 1) {
		if(typeof(arguments[0]) === "string") {
			return _goInvoke(arguments[0]);
		}
	}

	if(typeof(cbOrNs) === "string") {
		ns = cbOrNs;
		cb = pageOrCb;
	}
	else {
		ns = null;
		cb = cbOrNs;
	}

	if(ns) {
		ns = namespace(ns);
	}

	if(pageOrCb instanceof RegExp
	|| typeof(pageOrCb) === "string") {
		page = pageOrCb;
	}

	if(arguments.length > 2) {
		page = arguments[2];
	}

	if(!ns) {
		ns = window;
	}

	_goQueue.push({
		"namespace": ns,
		"callback": cb,
		"page": page,
	});
	return true;
};

// Expose required functions globally.
window.Gt = Gt;
window.go = go;
window.namespace = namespace;
window.api = api;
window.dom = dom;
window.template = template;
window.tool = tool;
window.http = http;

// Invoke functions to start.
go(_templateLoad);
_addNodeListHelpers();
_attachHelpers();
_goListen();

})();