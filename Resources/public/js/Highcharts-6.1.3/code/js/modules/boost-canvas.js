/*
 Highcharts JS v6.1.3 (2018-09-12)
 Boost module

 (c) 2010-2017 Highsoft AS
 Author: Torstein Honsi

 License: www.highcharts.com/license
*/
(function(k){"object"===typeof module&&module.exports?module.exports=k:"function"===typeof define&&define.amd?define(function(){return k}):k(Highcharts)})(function(k){(function(c){var k=c.win.document,aa=function(){},ba=c.Color,w=c.Series,e=c.seriesTypes,m=c.each,q=c.extend,x=c.addEvent,ca=c.fireEvent,da=c.isNumber,ea=c.merge,fa=c.pick,y=c.wrap,I;c.initCanvasBoost=function(){c.seriesTypes.heatmap&&c.wrap(c.seriesTypes.heatmap.prototype,"drawPoints",function(){var a=this.getContext();a?(m(this.points,
function(b){var f=b.plotY;void 0===f||isNaN(f)||null===b.y||(f=b.shapeArgs,b=b.series.colorAttribs(b),a.fillStyle=b.fill,a.fillRect(f.x,f.y,f.width,f.height))}),this.canvasToSVG()):this.chart.showLoading("Your browser doesn't support HTML5 canvas, \x3cbr\x3eplease use a modern browser")});c.extend(w.prototype,{getContext:function(){var a=this.chart,b=a.chartWidth,f=a.chartHeight,c=a.seriesGroup||this.group,d=this,e,h=function(a,f,b,d,c,t,e){a.call(this,b,f,d,c,t,e)};a.isChartSeriesBoosting()&&(d=
a,c=a.seriesGroup);e=d.ctx;d.canvas||(d.canvas=k.createElement("canvas"),d.renderTarget=a.renderer.image("",0,0,b,f).addClass("highcharts-boost-canvas").add(c),d.ctx=e=d.canvas.getContext("2d"),a.inverted&&m(["moveTo","lineTo","rect","arc"],function(a){y(e,a,h)}),d.boostCopy=function(){d.renderTarget.attr({href:d.canvas.toDataURL("image/png")})},d.boostClear=function(){e.clearRect(0,0,d.canvas.width,d.canvas.height);d===this&&d.renderTarget.attr({href:""})},d.boostClipRect=a.renderer.clipRect(),d.renderTarget.clip(d.boostClipRect));
d.canvas.width!==b&&(d.canvas.width=b);d.canvas.height!==f&&(d.canvas.height=f);d.renderTarget.attr({x:0,y:0,width:b,height:f,style:"pointer-events: none",href:""});d.boostClipRect.attr(a.getBoostClipRect(d));return e},canvasToSVG:function(){this.chart.isChartSeriesBoosting()?this.boostClear&&this.boostClear():(this.boostCopy||this.chart.boostCopy)&&(this.boostCopy||this.chart.boostCopy)()},cvsLineTo:function(a,b,f){a.lineTo(b,f)},renderCanvas:function(){var a=this,b=a.options,f=a.chart,t=this.xAxis,
d=this.yAxis,e=(f.options.boost||{}).timeRendering||!1,h,k=0,m=a.processedXData,w=a.processedYData,J=b.data,l=t.getExtremes(),z=l.min,A=l.max,l=d.getExtremes(),y=l.min,ga=l.max,K={},B,ha=!!a.sampling,L,C=b.marker&&b.marker.radius,M=this.cvsDrawPoint,D=b.lineWidth?this.cvsLineTo:!1,N=C&&1>=C?this.cvsMarkerSquare:this.cvsMarkerCircle,ia=this.cvsStrokeBatch||1E3,ja=!1!==b.enableMouseTracking,O,l=b.threshold,p=d.getThreshold(l),P=da(l),Q=p,ka=this.fill,R=a.pointArrayMap&&"low,high"===a.pointArrayMap.join(","),
S=!!b.stacking,T=a.cropStart||0,l=f.options.loading,la=a.requireSorting,U,ma=b.connectNulls,V=!m,E,F,r,v,G,n=S?a.data:m||J,na=a.fillOpacity?(new ba(a.color)).setOpacity(fa(b.fillOpacity,.75)).get():a.color,W=function(){ka?(h.fillStyle=na,h.fill()):(h.strokeStyle=a.color,h.lineWidth=b.lineWidth,h.stroke())},Y=function(b,d,c,g){0===k&&(h.beginPath(),D&&(h.lineJoin="round"));f.scroller&&"highcharts-navigator-series"===a.options.className?(d+=f.scroller.top,c&&(c+=f.scroller.top)):d+=f.plotTop;b+=f.plotLeft;
U?h.moveTo(b,d):M?M(h,b,d,c,O):D?D(h,b,d):N&&N.call(a,h,b,d,C,g);k+=1;k===ia&&(W(),k=0);O={clientX:b,plotY:d,yBottom:c}},oa="x"===b.findNearestPointBy,Z=this.xData||this.options.xData||this.processedXData||!1,H=function(a,b,c){G=oa?a:a+","+b;ja&&!K[G]&&(K[G]=!0,f.inverted&&(a=t.len-a,b=d.len-b),L.push({x:Z?Z[T+c]:!1,clientX:a,plotX:a,plotY:b,i:T+c}))};this.renderTarget&&this.renderTarget.attr({href:""});(this.points||this.graph)&&this.destroyGraphics();a.plotGroup("group","series",a.visible?"visible":
"hidden",b.zIndex,f.seriesGroup);a.markerGroup=a.group;x(a,"destroy",function(){a.markerGroup=null});L=this.points=[];h=this.getContext();a.buildKDTree=aa;this.boostClear&&this.boostClear();this.visible&&(99999<J.length&&(f.options.loading=ea(l,{labelStyle:{backgroundColor:c.color("#ffffff").setOpacity(.75).get(),padding:"1em",borderRadius:"0.5em"},style:{backgroundColor:"none",opacity:1}}),c.clearTimeout(I),f.showLoading("Drawing..."),f.options.loading=l),e&&console.time("canvas rendering"),c.eachAsync(n,
function(b,c){var e,g,h,k=!1,l=!1,m=!1,u=!1,X="undefined"===typeof f.index,q=!0;if(!X){V?(e=b[0],g=b[1],n[c+1]&&(m=n[c+1][0]),n[c-1]&&(u=n[c-1][0])):(e=b,g=w[c],n[c+1]&&(m=n[c+1]),n[c-1]&&(u=n[c-1]));m&&m>=z&&m<=A&&(k=!0);u&&u>=z&&u<=A&&(l=!0);R?(V&&(g=b.slice(1,3)),h=g[0],g=g[1]):S&&(e=b.x,g=b.stackY,h=g-b.y);b=null===g;la||(q=g>=y&&g<=ga);if(!b&&(e>=z&&e<=A&&q||k||l))if(e=Math.round(t.toPixels(e,!0)),ha){if(void 0===r||e===B){R||(h=g);if(void 0===v||g>F)F=g,v=c;if(void 0===r||h<E)E=h,r=c}e!==B&&
(void 0!==r&&(g=d.toPixels(F,!0),p=d.toPixels(E,!0),Y(e,P?Math.min(g,Q):g,P?Math.max(p,Q):p,c),H(e,g,v),p!==g&&H(e,p,r)),r=v=void 0,B=e)}else g=Math.round(d.toPixels(g,!0)),Y(e,g,p,c),H(e,g,c);U=b&&!ma;0===c%5E4&&(a.boostCopy||a.chart.boostCopy)&&(a.boostCopy||a.chart.boostCopy)()}return!X},function(){var b=f.loadingDiv,d=f.loadingShown;W();a.canvasToSVG();e&&console.timeEnd("canvas rendering");ca(a,"renderedCanvas");d&&(q(b.style,{transition:"opacity 250ms",opacity:0}),f.loadingShown=!1,I=setTimeout(function(){b.parentNode&&
b.parentNode.removeChild(b);f.loadingDiv=f.loadingSpan=null},250));delete a.buildKDTree;a.buildKDTree()},f.renderer.forExport?Number.MAX_VALUE:void 0))}});e.scatter.prototype.cvsMarkerCircle=function(a,b,c,e){a.moveTo(b,c);a.arc(b,c,e,0,2*Math.PI,!1)};e.scatter.prototype.cvsMarkerSquare=function(a,b,c,e){a.rect(b-e,c-e,2*e,2*e)};e.scatter.prototype.fill=!0;e.bubble&&(e.bubble.prototype.cvsMarkerCircle=function(a,b,c,e,d){a.moveTo(b,c);a.arc(b,c,this.radii&&this.radii[d],0,2*Math.PI,!1)},e.bubble.prototype.cvsStrokeBatch=
1);q(e.area.prototype,{cvsDrawPoint:function(a,b,c,e,d){d&&b!==d.clientX&&(a.moveTo(d.clientX,d.yBottom),a.lineTo(d.clientX,d.plotY),a.lineTo(b,c),a.lineTo(b,e))},fill:!0,fillOpacity:!0,sampling:!0});q(e.column.prototype,{cvsDrawPoint:function(a,b,c,e){a.rect(b-1,c,1,e-c)},fill:!0,sampling:!0});c.Chart.prototype.callbacks.push(function(a){x(a,"predraw",function(){a.renderTarget&&a.renderTarget.attr({href:""});a.canvas&&a.canvas.getContext("2d").clearRect(0,0,a.canvas.width,a.canvas.height)});x(a,
"render",function(){a.boostCopy&&a.boostCopy()})})}})(k)});
//# sourceMappingURL=boost-canvas.js.map
