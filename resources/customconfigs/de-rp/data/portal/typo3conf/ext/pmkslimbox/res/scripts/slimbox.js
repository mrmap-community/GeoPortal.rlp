/*
	Slimbox v1.69 - The ultimate lightweight Lightbox clone
	(c) 2007-2009 Christophe Beyls <http://www.digitalia.be>
	MIT-style license.

	Additions by Peter Klein
	-	Added TYPO3 specific Print & Save options.

*/
var Slimbox=(function(){var J=window,p=Browser.Engine.trident4,y,i,K=-1,q,A,I,z,C,R,w,o={},x=new Image(),P=new Image(),N,b,j,s,O,g,L,c,E,Q,v,d,B,k,e,G;J.addEvent("domready",function(){$(document.body).adopt($$(N=new Element("div",{id:"lbOverlay",events:{click:H}}),b=new Element("div",{id:"lbCenter"}),L=new Element("div",{id:"lbBottomContainer"})).setStyle("display","none"));j=new Element("div",{id:"lbImage"}).injectInside(b).adopt(s=new Element("div",{styles:{position:"relative"}}).adopt(O=new Element("a",{id:"lbPrevLink",href:"#",events:{click:F}}),g=new Element("a",{id:"lbNextLink",href:"#",events:{click:h}})));c=new Element("div",{id:"lbBottom"}).injectInside(L).adopt(new Element("a",{id:"lbCloseLink",href:"#",events:{click:H}}),v=new Element("a",{id:"lbPrintLink",href:"#"}).addEvent("click",M),d=new Element("a",{id:"lbSaveLink",href:"#"}).addEvent("click",u),E=new Element("div",{id:"lbCaption"}),Q=new Element("div",{id:"lbNumber"}),new Element("div",{styles:{clear:"both"}}))});function D(){var S=J.getScroll(),T=J.getSize();$$(b,L).setStyle("left",S.x+(T.x/2));if(z){N.setStyles({left:S.x,top:S.y,width:T.x,height:T.y})}}function n(S){["object",p?"select":"embed"].forEach(function(U){Array.forEach(document.getElementsByTagName(U),function(V){if(S){V._slimbox=V.style.visibility}V.style.visibility=S?"hidden":V._slimbox})});N.style.display=S?"":"none";var T=S?"addEvent":"removeEvent";J[T]("scroll",D)[T]("resize",D);document[T]("keydown",r)}function r(T){var S=T.code;return y.closeKeys.contains(S)?H():y.nextKeys.contains(S)?h():y.previousKeys.contains(S)?F():false}function F(){return a(A)}function h(){return a(I)}function a(S){if(S>=0){K=S;q=i[S][0];A=(K||(y.loop?i.length:0))-1;I=((K+1)%i.length)||(y.loop?0:-1);t();b.className="lbLoading";o=new Image();o.onload=m;o.src=q}return false}function m(){b.className="";e.set(0);j.setStyles({backgroundImage:"url("+q+")",display:""});s.setStyle("width",o.width);$$(s,O,g).setStyle("height",o.height);E.set("html",i[K][1]||"");Q.set("html",(((i.length>1)&&y.counterText)||"").replace(/{x}/,K+1).replace(/{y}/,i.length));if(A>=0){x.src=i[A][0]}if(I>=0){P.src=i[I][0]}R=j.offsetWidth;w=j.offsetHeight;var T=Math.max(0,C-(w/2)),S;if(b.offsetHeight!=w){k.start({height:w,top:T})}if(b.offsetWidth!=R){k.start({width:R,marginLeft:-R/2})}S=function(){L.setStyles({width:R,top:T+w,marginLeft:-R/2,visibility:"hidden",display:""});e.start(1)};if(k.check(S)){S()}}function l(){if(A>=0){O.style.display=""}if(I>=0){g.style.display=""}G.set(-c.offsetHeight).start(0);L.style.visibility=""}function t(){o.onload=$empty;o.src=x.src=P.src=q;k.cancel();e.cancel();G.cancel();$$(O,g,j,L).setStyle("display","none")}function H(){if(K>=0){t();K=A=I=-1;b.style.display="none";B.cancel().chain(n).start(0)}return false}function M(){return f("print")}function u(){return f("save")}function f(T){if(y.psScriptPath){var S=window.open(y.psScriptPath+"?mode="+T+"&image="+i[K][0],"printsave","left=0,top=0,width="+(parseInt(j.style.width))+",height="+(parseInt(j.style.height))+",toolbar=0,resizable=1");return false}return true}Element.implement({slimbox:function(S,T){$$(this).slimbox(S,T);return this}});Elements.implement({slimbox:function(S,V,U){V=V||function(W){return[W.href,W.title]};U=U||function(){return true};var T=this;T.removeEvents("click").addEvent("click",function(){var W=T.filter(U,this);return Slimbox.open(W.map(V),W.indexOf(this),S)});return T}});return{open:function(U,T,S){y=$extend({loop:false,overlayOpacity:0.8,overlayFadeDuration:400,resizeDuration:400,resizeTransition:false,initialWidth:250,initialHeight:250,psScriptPath:"",enablePrintButton:0,enableSaveButton:0,imageFadeDuration:400,captionAnimationDuration:400,counterText:"Image {x} of {y}",closeKeys:[27,88,67],previousKeys:[37,80],nextKeys:[39,78]},S||{});B=new Fx.Tween(N,{property:"opacity",duration:y.overlayFadeDuration});k=new Fx.Morph(b,$extend({duration:y.resizeDuration,link:"chain"},y.resizeTransition?{transition:y.resizeTransition}:{}));e=new Fx.Tween(j,{property:"opacity",duration:y.imageFadeDuration,onComplete:l});G=new Fx.Tween(c,{property:"margin-top",duration:y.captionAnimationDuration});if(typeof U=="string"){U=[[U,T]];T=0}if(!y.enablePrintButton||!y.psScriptPath){v.setStyles({visibility:"hidden",display:"none",width:"0px"})}if(!y.enableSaveButton||!y.psScriptPath){d.setStyles({visibility:"hidden",display:"none",width:"0px"})}C=J.getScrollTop()+(J.getHeight()/2);R=y.initialWidth;w=y.initialHeight;b.setStyles({top:Math.max(0,C-(w/2)),width:R,height:w,marginLeft:-R/2,display:""});z=p||(N.currentStyle&&(N.currentStyle.position!="fixed"));if(z){N.style.position="absolute"}B.set(0).start(y.overlayOpacity);D();n(1);i=U;y.loop=y.loop&&(i.length>1);return a(T)}}})();
