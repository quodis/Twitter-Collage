if(!window.console||!window.console.log){window.console={};window.console.log=function(a){};window.console.dir=function(a){}}function f_scrollLeft(){return f_filterResults(window.pageXOffset?window.pageXOffset:0,document.documentElement?document.documentElement.scrollLeft:0,document.body?document.body.scrollLeft:0)}function f_scrollTop(){return f_filterResults(window.pageYOffset?window.pageYOffset:0,document.documentElement?document.documentElement.scrollTop:0,document.body?document.body.scrollTop:0)}function f_clientWidth(){return f_filterResults(window.innerWidth?window.innerWidth:0,document.documentElement?document.documentElement.clientWidth:0,document.body?document.body.clientWidth:0)}function f_clientHeight(){return f_filterResults(window.innerHeight?window.innerHeight:0,document.documentElement?document.documentElement.clientHeight:0,document.body?document.body.clientHeight:0)}function f_filterResults(d,b,a){var c=d?d:0;if(b&&(!c||(c>b))){c=b}return a&&(!c||(c>a))?a:c}function getTinyUrl(c,d){var a="http://json-tinyurl.appspot.com/?url=";var b=a+encodeURIComponent(c)+"&callback=?";$.getJSON(b,function(e){d&&d(e.tinyurl)})}function objectLength(c){var b=0,a;for(a in c){if(c.hasOwnProperty(a)){b+=1}}return b}Array.prototype.shuffle=function(){for(var c,b,a=this.length;a;c=parseInt(Math.random()*a,10),b=this[--a],this[a]=this[c],this[c]=b){}};function number_format(f,c,h,e){f=(f+"").replace(/[^0-9+\-Ee.]/g,"");var b=!isFinite(+f)?0:+f,a=!isFinite(+c)?0:Math.abs(c),j=(typeof e==="undefined")?",":e,d=(typeof h==="undefined")?".":h,i="",g=function(o,m){var l=Math.pow(10,m);return""+Math.round(o*l)/l};i=(a?g(b,a):""+Math.round(b)).split(".");if(i[0].length>3){i[0]=i[0].replace(/\B(?=(?:\d{3})+(?!\d))/g,j)}if((i[1]||"").length<a){i[1]=i[1]||"";i[1]+=new Array(a-i[1].length+1).join("0")}return i.join(d)}function date(k,i){var h=this,j,g,c=/\\?([a-z])/gi,b,d=function(m,f){if((m=m+"").length<f){return new Array((++f)-m.length).join("0")+m}else{return m}},e=["Sun","Mon","Tues","Wednes","Thurs","Fri","Satur","January","February","March","April","May","June","July","August","September","October","November","December"],l={1:"st",2:"nd",3:"rd",21:"st",22:"nd",23:"rd",31:"st"};b=function(f,m){return g[f]?g[f]():m};g={d:function(){return d(g.j(),2)},D:function(){return g.l().slice(0,3)},j:function(){return j.getDate()},l:function(){return e[g.w()]+"day"},N:function(){return g.w()||7},S:function(){return l[g.j()]||"th"},w:function(){return j.getDay()},z:function(){var m=new Date(g.Y(),g.n()-1,g.j()),f=new Date(g.Y(),0,1);return Math.round((m-f)/86400000)+1},W:function(){var m=new Date(g.Y(),g.n()-1,g.j()-g.N()+3),f=new Date(m.getFullYear(),0,4);return 1+Math.round((m-f)/86400000/7)},F:function(){return e[6+g.n()]},m:function(){return d(g.n(),2)},M:function(){return g.F().slice(0,3)},n:function(){return j.getMonth()+1},t:function(){return(new Date(g.Y(),g.n(),0)).getDate()},L:function(){return new Date(g.Y(),1,29).getMonth()===1|0},o:function(){var o=g.n(),f=g.W(),m=g.Y();return m+(o===12&&f<9?-1:o===1&&f>9)},Y:function(){return j.getFullYear()},y:function(){return(g.Y()+"").slice(-2)},a:function(){return j.getHours()>11?"pm":"am"},A:function(){return g.a().toUpperCase()},B:function(){var m=j.getUTCHours()*3600,f=j.getUTCMinutes()*60,n=j.getUTCSeconds();return d(Math.floor((m+f+n+3600)/86.4)%1000,3)},g:function(){return g.G()%12||12},G:function(){return j.getHours()},h:function(){return d(g.g(),2)},H:function(){return d(g.G(),2)},i:function(){return d(j.getMinutes(),2)},s:function(){return d(j.getSeconds(),2)},u:function(){return d(j.getMilliseconds()*1000,6)},e:function(){throw"Not supported (see source code of date() for timezone on how to add support)"},I:function(){var m=new Date(g.Y(),0),o=Date.UTC(g.Y(),0),f=new Date(g.Y(),6),n=Date.UTC(g.Y(),6);return 0+((m-o)!==(f-n))},O:function(){var f=j.getTimezoneOffset();return(f>0?"-":"+")+d(Math.abs(f/60*100),4)},P:function(){var f=g.O();return(f.substr(0,3)+":"+f.substr(3,2))},T:function(){return"UTC"},Z:function(){return -j.getTimezoneOffset()*60},c:function(){return"Y-m-d\\Th:i:sP".replace(c,b)},r:function(){return"D, d M Y H:i:s O".replace(c,b)},U:function(){return j.getTime()/1000|0}};var a=function(m,f){h=this;j=((typeof f==="undefined")?new Date():(f instanceof Date)?new Date(f):new Date(f*1000));return m.replace(c,b)};return a(k,i)}var party=party||{};(function(){var z,K,d,c=200,h,o=0,D=0,v={},y=[],q=[],H=[],B=0,u,s={},L=null,I=["#ACE8F1","#2D4891","#F7DC4B","#C52F14"],g={canvas:null,current:0,increment:0},S={input:null,original_caption:null},U={active_bubble_pos:0,keep_bubble_open:false,last_id:0,mosaic_offset:{},initial_tiles_per_frame_incremental:1,draw_new_tiles_every:0,draw_new_tiles_every_counter:0,total_tiles:0,last_tile_drawn_pos:-1},R={high:{initial_frames_per_second:8,initial_tiles_per_frame:15,new_tiles_per_second:8,pause_after:12},medium:{initial_frames_per_second:4,initial_tiles_per_frame:30,new_tiles_per_second:6,pause_after:10},low:{initial_frames_per_second:1,initial_tiles_per_frame:200,new_tiles_per_second:1,pause_after:6}};function n(V){return V.replace(/(ftp|http|https|file):\/\/([\S]+(\b|$))/gim,'<a href="$&" class="my_link" target="_blank">$2</a>').replace(/([^\/])(www[\S]+(\b|$))/gim,'$1<a href="http://$2" class="my_link" target="_blank">$2</a>').replace(/(^|\s)@(\w+)/g,'$1<a href="http://twitter.com/$2" class="my_link" target="_blank">@$2</a>').replace(/(^|\s)#(\S+)/g,'$1<a href="http://search.twitter.com/search?q=%23$2" class="my_link" target="_blank">#$2</a>')}function N(Y){var V,W,X;if(!Y){return null}V=Y.p;W=party.mosaic.index[V];if(!W){return null}X=document.createElement("div");X.setAttribute("id",V);X.style.backgroundImage="url("+party.store_url+"/mosaic.jpg)";X.style.backgroundPosition="-"+(W[0]*12)+"px -"+(W[1]*12)+"px";X.style.left=(W[0]*12)+"px";X.style.top=(W[1]*12)+"px";return X}function f(){var V,W;for(V=0;V<B;V+=1){y.push(V)}y.shuffle();W=parseInt(B/party.performance.initial_tiles_per_frame,10);g.increment=parseInt(U.total_tiles/W,10);z=window.setInterval(F,(1000/party.performance.initial_frames_per_second))}function F(){var Y=[],W=0,V=0,aa=null,Z=false,X;V=(o+party.performance.initial_tiles_per_frame);for(W=o;W<V;W+=1){X=y[W];aa=N(v[X]);if(aa){Y.push(aa);Z=true}}o=W;if(Z){party.canvas.append("",Y);if(g.current<U.total_tiles){g.current+=g.increment;T()}}else{window.clearInterval(z);party.canvas.css("background","none");g.current=parseInt(U.total_tiles,10);T();p();r()}}function T(){g.canvas.text(number_format(g.current,0,party.l10n.dec_point,party.l10n.thousands_sep))}function G(){var X=$.makeArray($("#loading li")),V=0,aa,W=5,Y=0,ab=$("#loading"),Z;X.shuffle();aa=function(){$(X[V]).hide();V+=1;if(V>=X.length){V=0}$(X[V]).show()};Z=function(){ab.css("background-position",-(Y*240)+"px 0px");Y+=1;if(Y>=W){Y=0}};aa();K=window.setInterval(aa,(party.loading_message_seconds*1000));Z();d=window.setInterval(Z,c)}function Q(){window.clearInterval(K);window.clearInterval(d);$("#loading").remove()}function m(V,W){L.css({left:(V*12)+"px",top:(W*12)+"px",display:"block"})}function P(){var V;party.performance=party.performance_settings.high;if($.browser.msie){party.performance=party.performance_settings.medium}else{if($.browser.mozilla){if(window.navigator.userAgent.search("Firefox/4")!=-1){$("#download").remove()}}}g.canvas=$("#twitter-counter dd span");L=$("#tile-hover");party.canvas=$("#mosaic");V=$("#bubble");party.bubble={container:V,username_a:V.find("h1 a"),avatar_a:V.find("a.twitter-avatar"),avatar_img:V.find("a.twitter-avatar > img"),time:V.find("time"),time_a:V.find("time > a"),p:V.find("p")};U.mosaic_offset=party.canvas.offset();i();x();party.canvas.bind("mousemove",function(X){var W,aa,Z,Y=party.canvas.offset();window.clearTimeout(party.mousemoveTimer);window.clearTimeout(party.auto_bubble_timer);if(U.keep_bubble_open){return}W=Math.ceil((X.clientX+f_scrollLeft()-Y.left)/12)-1;aa=Math.ceil((X.clientY+f_scrollTop()-Y.top)/12)-1;if(W<0||aa<0){return}Z=party.mosaic.grid[W][aa];if(Z){m(W,aa)}else{L.hide()}party.mousemoveTimer=window.setTimeout(function(){if(Z){k();if(U.active_bubble_pos!=Z.i){J(Z.i)}}else{p()}},50)});party.canvas.bind("mouseleave",function(){p()});party.bubble.container.bind("mouseenter",function(){var W=party.mosaic.index[U.active_bubble_pos];if(!W){return}m(W[0],W[1]);U.keep_bubble_open=true;k()});party.bubble.container.bind("mouseleave",function(){U.keep_bubble_open=false});party.init=function(){return party}}function t(){S.input_dom.attr("disabled","").removeClass("disabled");$("#search-box button").attr("disabled","").removeClass("disabled")}function i(){S.input_dom=$("#search-input");S.original_caption=S.input_dom.val();S.input_dom.focus(function(){if($(this).val()===S.original_caption){$(this).val("")}});S.input_dom.blur(function(){if($(this).val()==""){$(this).val(S.original_caption)}});$("#search-box").submit(function(){var V=S.input_dom.val();if(V==""){return false}$("#search-box button").addClass("loading");$.ajax({url:"/tiles-by-username.php",type:"GET",dataType:"json",data:{user_name:V},success:M});return false})}function M(V){var X,W;$("#search-box button").removeClass("loading");if(V.payload.total==0){$("#search-box .error").fadeIn("fast");window.setTimeout(function(){$("#search-box .error").fadeOut("fast")},3*1000);return}X=V.payload.tiles[0];W=X.p;$.extend(v[W],X);k();U.keep_bubble_open=true;J(W);V=null}function O(){var V;V=q[D];if(!V){D=0;return}D+=1;J(V.position)}function p(){if(!party.auto_bubble_timer&&!U.keep_bubble_open){O();party.auto_bubble_timer=window.setInterval(O,party.auto_bubble_seconds*1000)}}function k(){if(party.auto_bubble_timer){window.clearInterval(party.auto_bubble_timer);party.auto_bubble_timer=null}}function J(ab){var ae,ac,aa,ad=party.bubble,Z,V,X,Y,af,W;aa=v[ab];if(!aa||!ad){return}X=party.mosaic.index[ab];if(!X){return}ae=X[0];ac=X[1];Y=party.mosaic.grid[ae][ac];if(!Y){return}U.active_bubble_pos=ab;if(ac>24){if(ae>24){Z="bottom-right";V={top:"",right:(580-(ae*12))+"px",bottom:(567-(ac*12))+"px",left:""}}else{Z="bottom-left";V={top:"",right:"",bottom:(567-(ac*12))+"px",left:((ae*12)+16)+"px"}}}else{if(ae>24){Z="top-right";V={top:((ac*12)+16)+"px",right:(580-(ae*12))+"px",bottom:"",left:""}}else{Z="top-left";V={top:((ac*12)+16)+"px",right:"",left:((ae*12)+17)+"px",bottom:""}}}ad.avatar_img.attr("src","assets/images/layout/avatar-loading.gif");ad.container.hide();af=date(party.l10n.date_format,aa.c);ad.username_a.text(aa.u).attr("href","http://twitter.com/"+aa.u);ad.avatar_a.attr("title",aa.u).attr("href","http://twitter.com/"+aa.u);ad.p.html(n(aa.n));ad.time_a.attr("href","http://twitter.com/"+aa.u+"/status/"+aa.w).text(af);ad.time.attr("datetime",af);ad.container.css(V).removeClass().addClass("bubble "+Z+" color-"+Y.r);W=new Image();$(W).load(function(){if(U.active_bubble_pos!=aa.p){return}ad.avatar_img.attr("src",aa.m);W=null}).attr("src",aa.m);m(ae,ac);ad.container.show()}function a(){U.active_bubble_pos=0;U.keep_bubble_open=false;party.bubble.container.hide();L.hide()}function e(){window.location=window.location}function x(){$.ajax({url:party.store_url+"/mosaic.json",type:"GET",dataType:"jsonp",jsonp:false})}function l(W){Q();if(W.last_id>U.last_id){U.last_id=W.last_id}v=W.tiles;var V;for(V in v){if(v[V].p){q.push({id:parseInt(v[V].i,10),position:parseInt(v[V].p,10)})}}B=q.length;q.sort(function(Y,X){return X.id-Y.id});q=q.slice(0,199);U.total_tiles=W.total_tiles;t();f();w();W=null}function r(){u=window.setInterval(C,(1000/party.performance.new_tiles_per_second))}function C(){var ab,aa,V,W,Y,X,Z;if(U.draw_new_tiles_every_counter>=U.draw_new_tiles_every){aa=H[0];U.draw_new_tiles_every_counter=0}U.draw_new_tiles_every_counter+=1;if(aa){ab=parseInt(aa.p,10);if(!v[ab]){H.shift();return}Y={"background-image":"url(data:image/gif;base64,"+aa.d+")","background-position":"0px 0px"};aa.base64_only=true;$.extend(v[ab],aa);q.shift();q.push({id:parseInt(aa.i,10),position:ab});H.shift();g.current+=1;T()}else{ab=Math.floor(Math.random()*B);V=party.mosaic.index[ab];W=party.mosaic.grid[V[0]][V[1]];Y={"background-image":"none","background-color":I[W.r]}}if(U.last_tile_drawn_pos>-1){X=v[U.last_tile_drawn_pos];Z=$("#"+U.last_tile_drawn_pos);if(X.base64_only){Z.css({"background-image":"url(data:image/gif;base64,"+X.d+")","background-position":"0px 0px"})}else{Z.css({"background-image":"url("+party.store_url+"/mosaic.jpg)","background-position":"-"+Z.css("left")+" -"+Z.css("top")})}}U.last_tile_drawn_pos=ab;$("#"+ab).css(Y)}function w(){E();h=window.setInterval(E,(party.polling_timer_seconds*1000));if(window.location.href.indexOf("keepgoing")<0){window.setTimeout(b,party.performance.pause_after*60*1000)}}function E(){$.ajax({url:"/poll.php",dataType:"json",data:{last_id:U.last_id},success:function(V){if(V.payload.last_id>U.last_id){U.last_id=V.payload.last_id}U.total_tiles=V.payload.total_tiles;H=H.concat(V.payload.tiles.reverse());U.draw_new_tiles_every=Math.round((party.performance.new_tiles_per_second*party.polling_timer_seconds)/H.length);V=null}})}function A(){return U.last_id}function b(){window.clearInterval(u);window.clearInterval(h);k()}function j(){r();w();p()}$.extend(party,{loading_message_seconds:2,polling_timer_seconds:180,auto_bubble_seconds:6,grid:[],index:[],init:P,getLastId:A,pause:b,resume:j,showBubble:J,performance:s,performance_settings:R,state:U,new_tiles:H,loadingShow:G,processMosaic:l})}());(function(){var c=["assets/images/layout/bubbles.png"];for(var b=c.length;b--;){var a=new Image();a.src=c[b]}})();$(document).ready(function(){var c=0,a=0,b;$("#flang").change(function(){window.location="/"+$(this).val()});$("#twitter-counter > dl > dt > a").click(function(){var e=550,g=500,d=(window.screen.width-e)/2,f=(window.screen.height-g)/2;window.open($(this).attr("href"),"tweet","left="+d+",top="+f+",width="+e+",height="+g+",toolbar=0,resizable=1");return false});c=parseInt($("#brand em").width(),10)+20;a=parseInt($("#brand p").width(),10);$("#brand em").before('<span style="left:0; width:'+(a-c)/2+'px" />').fadeIn("slow");$("#brand em").after('<span style="right:0; width:'+(a-c)/2+'px" />').fadeIn("slow");party.loadingShow();b=$('<img src="'+party.store_url+'/mosaic.jpg">');b.load(party.init)});