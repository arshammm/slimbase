<!DOCTYPE html><html>
<head>
  <meta charset="utf8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>{% block title %} {{ title }} {% endblock %} - {{ projectname }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

  <!-- Favicon -->
  <link href="/images/favicon.ico" rel="icon" type="image/x-icon" />
  <link href="/images/favicon.ico" rel="shortcut icon" type="image/x-icon" />

  <link href='//fonts.googleapis.com/css?family=Raleway:400,700|Open+Sans:400italic,600italic,700italic,400,700,600' rel='stylesheet' type='text/css'>
  <!-- Bootstrap -->
 
  <link href="/css/bootstrap.min.css" rel="stylesheet">
  <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/js/morris.js/morris.css">
  <link rel="stylesheet" href="/js/iCheck/skins/all.css">
  <link href="/css/styles.css" rel="stylesheet">
  <link href="/css/huaman-styles.css" rel="stylesheet">
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
    <script src="//oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="//oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
  <![endif]-->
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
  <!-- Include all compiled plugins (below), or include individual files as needed -->
  <script src="/js/bootstrap.min.js"></script>
  <script src="/js/footable/footable.js"></script>
  <script src="/js/footable/footable.paginate.js"></script>
  <script src="/js/footable/footable.filter.js"></script>
  <script src="/js/footable/footable.sort.js"></script>
  <script src="/js/datepicker/js/bootstrap-datepicker.js"></script>
  <script src="/js/iCheck/js/icheck.min.js"></script>
  <script src="/js/raphael-min.js"></script>
  <script src="/js/morris.js/morris.min.js"></script>
  <script src="/js/swf/jquery.zclip.js"></script>
  
</head>
<body class="{{bodyclass}}">
    
    
{% autoescape false %}
{{mobile_navigation}}
{% endautoescape %}
	    
{% if user_type is not null %}
    <header id="app-header"><! -- START header -->
        <div class="container app-header-container">
            <div class="row">
            
            
            <div class="col-md-3 col-sm-3 col-xs-12"><! -- START Branding and Mobile Nav Toggle -->
            
                <a href="/" id="app-logo" title="Go to Dashboard">
                    <img src="/images/logo.png" class="logo" alt="logo"/>
                </a>
                
                <button type="button" class="navbar-toggle pull-left" data-toggle="offcanvas" data-target="#main-mobile-nav" data-canvas="body">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            	
            </div><! -- END Branding and Mobile Nav Toggle -->
            
						{% autoescape false %}
						{{navigation}}
						{% endautoescape %}
						                           
            </div>
        </div>
    </header><! -- END header -->
{% endif %}

{% if user_type is not null %}

<div id="shorten-bar">

<div class="container">

  <div class="row" id="header-shorten"> <!--START INPUT ROW-->
		
	</div>

</div>

</div>
{% endif %}
<div id="app-body-wrap"><!-- #app-body-wrap -->
<div class="container" id="app-body"><!-- #app-body -->
   
    {% if success is not null %}   
    <div class="row"> <!--START ERROR ROW-->
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="alert alert-success" id="success_message">
				<i class="fa fa-check-circle alert-icon"></i>
				<ul>
  				{% for message in success %}
  					<li class="message-item">&bull; {{ message }}</li>
  				{% endfor %}   
				</ul>
			</div>
    </div>
    </div><!--END SUCCESS ROW-->
    {% endif %}
		
	  {% if errors is not null %}
		<div class="row"> <!--START ERROR ROW-->
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="alert alert-danger" id="error_message">
				<i class="fa fa-exclamation-circle alert-icon"></i>
				<ul>
  				{% for message in errors %}
  					<li class="message-item">&bull; {{ message }}</li>
  				{% endfor %}
				</ul>
			</div>
		</div>
		</div><!--END ERROR ROW-->
  {% endif %}

    {% block pagecontent %}

    {% endblock %}

{% include 'footer.php' %}
