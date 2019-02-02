function getURLParameter(name) { return decodeURI( (RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1] ); }
function diff(a,b){return Math.abs(a-b);}
var MyMap={};
var MapPoint=function(x,y,value,img,id)
{
    this.id=id;
    this.x=x;
    this.y=y;
    this.value=value;
    this.img=img;
    this.LinkedBy=[];
    this.LinkedTo=[];
    this.LinkedByPath=[];
    this.LinkedToPath=[];
    
    this.GroupPath=0;
    this.GroupAngle=0;
    this.LayoutPath=0;
    this.LayoutAngle=0;
    this.selfIndex=-1;
    return this;
}

var NodeMap=function(MapSource)
{    
    $elf=this;
    $elf.MapSource=typeof MapSource !== 'undefined' ? MapSource : 'https://service.myrealestatesite.co/Treedata.php';
    $elf.Stage=Raphael(document.getElementById('Stage'),'100%','100%');
    $elf.DisplayNodes=[];
    $elf.Clusters=[];
    $elf.iter=0;
    $elf.tier=0;
    $elf.tiers=[0];
    $elf.Origin=new MapPoint(0.0,0.0,'cat/default.jpg','Origin');
    $elf.deltaY=72;
    $elf.deltaX=72;
    
    $elf.color="b38";$elf.duoCount=0; $elf.dx=0.0;$elf.dy=0.0;
    $elf.LastMapPoint=false;

    $elf.LineStyle={'stroke':'#777'};
    $elf.glow = {color: '#f00',width: 20};
    $elf.NodeStyle={'fill':'#443344','stroke':'#99AA99','stroke-width':'3px'}
    $elf.images=['https://static.myrealestatesite.co/tools/node/map.html','https://static.myrealestatesite.co/tools/node/map.html','https://static.myrealestatesite.co/tools/node/map.html' ,'https://static.myrealestatesite.co/tools/node/map.html',
    'https://static.myrealestatesite.co/tools/node/map.html','https://static.myrealestatesite.co/tools/node/map.html', 'https://static.myrealestatesite.co/tools/node/map.html', 'https://static.myrealestatesite.co/tools/node/map.html', 'https://static.myrealestatesite.co/tools/node/map.html','https://static.myrealestatesite.co/tools/node/map.html', 'https://static.myrealestatesite.co/tools/node/map.html']

    $elf.click=function(e){ console.log('Click ',this);};
    $elf.move=function(dx,dy)
    {
      this.transform('t'+dx+','+dy);
      this.map.x=dx;
      this.map.y=dy;
      
      $(this.map.LinkedBy).each(function(i,node)
      {
	
      });

      console.log('Move', this);
    };

    $elf.startMove = function()
    {
//	this.odx = 0;
//	this.ody = 0;
    };

    $elf.endMove=function()
    {

    };

    $elf.call=
    {
	init:function()
	{
	    point=new MapPoint( $('#Stage').width()/2, $('#Stage').height()/2, 'Stage', 'https://static.myrealestatesite.co/img/ss_logo.png');
	    $elf.Origin=$elf.Stage.image('https://static.myrealestatesite.co/img/ss_logo.png', point.x-60, point.y-60, 120, 120);
	    $elf.Origin.map=point;
	    $elf.Origin.map.selfIndex = 0;
	    $elf.Origin.map.id = 0;
	    $elf.Origin.map.GroupPath =$elf.Stage.path($elf.call.PolarArc( point.x,point.y,144,144,-0.001,359.999, -90 )).attr({opacity:'0.3', color: '000', fill:'#fff'}).drag($elf.move,$elf.startMove,$elf.endMove);
	    //$elf.call.PathCircle(point.x,point.y,144)
	    
	    $elf.Origin.map.GroupAngle =360;
	    $elf.Origin.map.LayoutPath = $elf.Origin.map.GroupPath;
	    $elf.Origin.map.LayoutAngle = -90;
	    $elf.Origin.map.LinkedByPath[0]=$elf.Origin.map.GroupPath;
	    
	    $elf.Origin.map.value = "Approach";
	    
	    
	    $elf.DisplayNodes[0]=$elf.Origin;

	    $elf.Origin.map.GroupPath.toFront();
	    //$elf.Stage.text($elf.Origin.map.x,$elf.Origin.map.y-19,'approach').attr({stroke: '#000','fill': '#fff','font-size':'48px','font-family':"'Signika', sans-serif",'font-weight': 'bold'});	    

	    $.get($elf.MapSource, function(Response)
	    {
		t={};
		t.links=$.parseJSON(Response);
		
		//This just parses and filters the response, filtering will happen on the
		//Server side when we know how to make map navigation extensible and simple
		$($.parseJSON(Response)).each( function(i,node)
		{
		    if (node.value==$.parseJSON(getURLParameter('json')) ){ t=node; return false;}
		    return true;
		} );
		
		//Create the map from a ready json tree
		$elf.call.ReceiveNodes(t.links,0);
		
		//Create visual connections between nodes
		//Also used in programmatic animations
		$elf.call.ConnectNodes();
		
		//Trying to animate the connections, not working
		$.each($elf.DisplayNodes,function(i,node)
		{
		    //console.log(node);
		    if (node.map.LinkedBy[0]==0) { $elf.Origin.map.LinkedTo.push(node.map.selfIndex); }
		    
		    node.animation=Raphael.animation(node.map.LinkedToPath[0]);
		    node.map.LinkedByPath[0].animate(node.animation.delay(1000).repeat(1000));		
		});
	    });
	},
	ConnectNodes:function()
	{
	    $($elf.DisplayNodes).each(function(id, node)
	    {
		if(id==0) return;
		
		//Maybe put this into ConnectNodes and make this ConnectMap or merge into some kinda recursion?
		//Other viable solutions definitely exist
		for(i=0;i<node.map.LinkedBy.length;i++)
		{
		  ConnectedNode = $elf.DisplayNodes[node.map.LinkedBy[i]];

		  Point1=ConnectedNode.map;
		  Point2=node.map;
	  
		  RegulatorX1 = ((Point1.x+Point2.x)*.5) - 28;		//Regulators detirmine how curvey the connectors are
		  RegulatorX2 = ((Point1.x+Point2.x)*.5) + 28;		//We should use radians or differentials instead ^_^;;
		  RegulatorY1 = ((Point1.y+Point2.y)*.5) - 28;		//Or just a slick correction for approaching 45 degree angles
		  RegulatorY2 = ((Point1.y+Point2.y)*.5) + 28;
	  
		  node.map.LinkedByPath.push($elf.call.curve3(Point1.x,Point1.y,Point2.x, Point2.y,RegulatorX1,RegulatorY1,RegulatorX1,RegulatorY1).toBack().attr($elf.LineStyle).glow({width: 2,color: 'black'}));
		}
		for(i=0;i<node.map.LinkedTo.length;i++)
		{
		  ConnectedNode = $elf.DisplayNodes[node.map.LinkedTo[i]];

		  Point1=ConnectedNode.map;
		  Point2=node.map;
	  
		  RegulatorX1 = ((Point1.x+Point2.x)*.5) - 28;
		  RegulatorX2 = ((Point1.x+Point2.x)*.5) + 28;
		  RegulatorY1 = ((Point1.y+Point2.y)*.5) - 28;
		  RegulatorY2 = ((Point1.y+Point2.y)*.5) + 28;
	  
		  node.map.LinkedToPath.push($elf.call.curve3(Point1.x,Point1.y,Point2.x, Point2.y,RegulatorX1,RegulatorY1,RegulatorX1,RegulatorY1).toBack().attr({opacity:'0.1'}));
		}
	    });
	    
	},
	MeasureCharacter:function(parent)
	{
	    var temp = document.createElement('span');
	    parent = parent || document.body;
	    temp.style.cssText='padding:0; line-height:1;';
	    
	    parent.appendChild(document.createTextNode('M'));
	    parent.appendChild(temp);
	    var result={x:temp.offsetWidth, y:temp.offsetHeight};
	    parent.removeChild(temp);
	    return result;
	},	
	PathCircle:function(x , y, r){ return $elf.Stage.path("M" + (x) + "," + (y-r) + "A"+r+","+r+",0,1,1,"+(x-0.1)+","+(y-r)+"z"); },
	PolarArc:function(cx,cy,rx,ry,theta1,delta,phi)
	{
	    theta2 = delta + theta1;
	    theta1 = theta1 * (Math.PI / 180);   //To Do: make deg2rad JS or find Math.deg(); ?
	    theta2 = theta2 * (Math.PI / 180);
	    
	    phi_r = phi * (Math.PI / 180);
	  
	    x0 = cx + Math.cos(phi_r) * rx * Math.cos(theta1) + Math.sin(-phi_r) * ry * Math.sin(theta1);
	    y0 = cy + Math.sin(phi_r) * rx * Math.cos(theta1) + Math.cos(phi_r) * ry * Math.sin(theta1);
	
	    x1 = cx + Math.cos(phi_r) * rx * Math.cos(theta2) + Math.sin(-phi_r) * ry * Math.sin(theta2);
	    y1 = cy + Math.sin(phi_r) * rx * Math.cos(theta2) + Math.cos(phi_r) * ry * Math.sin(theta2);
	    
	    large_arc = (delta > 180) ? 1 : 0;
	    sweep = (delta > 0) ? 1 : 0;
	    
	    return ( [['M',x0,y0], ['A',rx,ry,phi,large_arc,sweep,x1,y1]] );
	},
        drawHeart:function(x,y,r)
        {
                $elf.heart = [ ['M',x,y+r],['c',0,0,  r*1.75,-r,    r,-2*r ] ,['c',0,0,   -r/2,-r/1.5,  -r,0], ['M',x,y+r], ['c',0,0,  -r*1.75,-r,    -r,-2*r ] ,['c',0,0,   r/2,-r/1.5,  r,0] ];
                $elf.heart=$elf.Stage.path($elf.heart).attr($elf.LineStyle[0]);
        },
	AllNodes:function(callback)
	{
	    $.each($elf.DisplayNodes,callback(i,val))
	},
	CreateNode:function(Where, From)
	{
	    $elf.DisplayNodes.push($elf.Stage.image('https://static.myrealestatesite.co/img/ss_logo.png',Where.x - 16/($elf.tier+1),Where.y-16/($elf.tier+1),32/($elf.tier+1),32/($elf.tier+1)).drag($elf.move,$elf.startMove,$elf.endMove));
            current = $elf.DisplayNodes.length-1;

	    $elf.DisplayNodes[current].map = Where;
	    $elf.DisplayNodes[current].map.selfIndex = current;
	    $elf.DisplayNodes[current].map.LinkedBy.push(From);
	    $elf.Stage.text(Where.x,Where.y+10,Where.value).attr({stroke: '#000','fill': '#fff','font-size':12+4*($elf.tier<5?4/$elf.tier:0)+'px','font-family':"'Quattrocento Sans', sans-serif",'font-weight': 'bold'});	    
	     
	    return $elf.DisplayNodes[current];
	},
	SimpleArc:function(x, y, radius, start, end)
	{
	      var f = ((end - start) > Math.PI) ? 1 : 0;
    
	      var sx = radius * Math.cos(start);
	      var sy = radius * Math.sin(start);
	      var ex = x + radius * Math.cos(end);
	      var ey = y + radius * Math.sin(end);
    
	      var path = "M " + x + " " + y + " l "
		  + sx + " " + sy + " A " + radius
		  + " " + radius + " 0 " + f + " 1 "
		  + ex + " " + ey + " z";
	      return path;
	},
	
	angle:function(center, p1)
	{
	    var p0 = {x: center.x, y: center.y - Math.sqrt(Math.abs(p1.x - center.x) * Math.abs(p1.x - center.x) + Math.abs(p1.y - center.y) * Math.abs(p1.y - center.y))};
	    return (2 * Math.atan2(p1.y - p0.y, p1.x - p0.x)) * 180 / Math.PI;
	},
    ReceiveNodes:function(Response,parentIndex)
    {
	if(Response.length > 0){ $elf.Clusters[parentIndex]=$elf.Stage.set(); $elf.tier +=1; $elf.tiers[$elf.tier] += Response.length; } else return -1;
	$(Response).each( function(i,node)
	{
	    var len=$elf.DisplayNodes[parentIndex].map.LayoutPath.getTotalLength();
	    var p=$elf.DisplayNodes[parentIndex].map.LayoutPath.getPointAtLength(len*((0.5+i)/Response.length));
	    var graphNode={};
	    
	    var mapTo=new MapPoint(p.x, p.y,node.value, 'https://static.myrealestatesite.co/img/ss_logo.png', 'node '+$elf.tier+' '+parentIndex+' '+i);
	    mapTo.GroupAngle = ((($elf.DisplayNodes[parentIndex].map.GroupAngle))/Response.length);
	    mapTo.GroupPath = $elf.DisplayNodes[parentIndex].map.LayoutPath;
	    mapTo.value = node.value;
	    mapTo.LayoutAngle = $elf.DisplayNodes[parentIndex].map.LayoutAngle-(($elf.DisplayNodes[parentIndex].map.GroupAngle))/2 + (0.5+i)*(mapTo.GroupAngle);
	    
	    
	    if (mapTo.GroupAngle > 360) mapTo.GroupAngle-=360;
	    if (mapTo.LayoutAngle > 360) mapTo.LayoutAngle-=360;
	    
	    

	    //144+224*$elf.tier,144+144*$elf.tier,
	    
	    mapTo.LayoutPath = $elf.Stage.path( $elf.call.PolarArc( p.x, p.y,
						    ($elf.DisplayNodes[parentIndex].map.value.length*16+32+node.value.length*16+24*node.links.length)/2,
						    ($elf.DisplayNodes[parentIndex].map.value.length*16+32+node.value.length*16+24*node.links.length)/2,
						    mapTo.LayoutAngle-((mapTo.GroupAngle)/2), mapTo.GroupAngle, 180 )).attr({opacity:'0.0', color: 'fff'});

	    graphNode = $elf.call.CreateNode(mapTo, parentIndex);
	    len=null;
	    p=null;
	    mapTo=null;
	    
	    
	    
	    $elf.Clusters[parentIndex].push(graphNode);
	    $elf.DisplayNodes[parentIndex].map.LinkedTo.push(graphNode.map.selfIndex);
	    
	    //Recursion Management
	    if(node.links.length>0)
	    {
		$elf.call.ReceiveNodes(node.links, graphNode.map.selfIndex);
	    }
	    //else	    console.log('A branch has stopped on tier: ' + $elf.tier);

	    graphNode=null;
	});
	$elf.tier -=1;
       return 1;
    },

	//StartX,StartY,Towardsxd,Towardsyd, curveto in between
	curve3:function(Ax, Ay, Dx, Dy)
	{
	    return $elf.Stage.path('M'+Ax+',' + Ay +','+ "C"+
				   ( ((Ax+Dx)*.5) - 48 ) +','+ ( ((Ay+Dy)*.5) - 48 ) +','+
				   ( ((Ax+Dx)*.5) + 48 ) +','+ ( ((Ay+Dy)*.5) + 48 ) +','+
				   Dx +','+ Dy );	//pure cubic curve normalized by 96px
	},
	updatecurve3:function(curve,xa, ya, xb, yb, xc, yc, xd, yd)
	{
	    var path_parts = ["m"+xa, ya, "c"+xb, yb, xc, yc, xd, yd];
	    var path_string = path_parts.join(" ");
	    var attr={color: '#0ff',width: 20};
	    return $elf.call.curve3(Raphael.transformPath(curve, path_string));
	}
    };

    $elf.call.init();
    return $elf;
}

CirclePlot=function()
{
   var radius = 100;
   var hand = MyMap.Stage.path('M'+(MyMap.Origin.x)+','+MyMap.Origin.y+'L'+(MyMap.Origin.x+radius)+','+MyMap.Origin.y);

}

$(function(){    MyMap=new NodeMap('https://service.myrealestatesite.co/Treedata.php'); });