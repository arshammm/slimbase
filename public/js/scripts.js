//hide these elements by default
var el = document.getElementById('dashboard_view');
if(el) {
  el.className += el.className ? ' invisible' : 'invisible';
}

el = document.getElementById('analytics_advanced_container');
if(el) {
  el.className += el.className ? ' hidden' : 'hidden';
}

jQuery(document).ready(function($) {
  
  $.fn.exists = function(){return this.length>0;}
  
  // COPY BUTTONS
  $(".trksit-copy-btn").on('click', function (e) {
  
    //$(".trksit-copy-btn.copied").removeClass('copied');
    e.preventDefault();
    
    
  }).each(function () {
    
    $(this).zclip({
      path: "/js/swf/ZeroClipboard.swf",
      copy: function() { return $(this).data("trksit-link"); },
      afterCopy:function(){ 
        
      },
      clickAfter: false
    });
  
  });

  
  $('input').iCheck({
    checkboxClass: 'icheckbox icheckbox_square',
    radioClass: 'iradio iradio_square',
  });
  
  $('.panel-group > .panel-header').each(function(){
    $(this).on('click', function(e){
      $(this).toggleClass('collapsed');
      $(this).siblings('.panel-content').toggleClass('collapsed');
      $(this).siblings('.panel-content').slideToggle(0);
    });
  });
  
  //listen for when the dashboard footable is done loading
  $(document).on('footable_initialized',function(e){
    //show the trksit dashboard table. this will speed up rendering of the table
    $('.trksit-table').show();
  });

  //Dashboard table
  $('.trksit-table').footable({
    breakpoints: {
      phone: 480,
      tablet: 768,
      standard: 960
    }
  });
  
  $('.has-tooltip').tooltip();
  
  $('#user-info-trigger > a.user-trigger').on('click', function(){
    event.stopPropagation();
    $(this).siblings('#user-details-panel').toggleClass('active');
    $(this).toggleClass('active');
  });
  

  
  
  //get the script that the user added to the form
  $('form#script_create').on('submit', function(event) {
    $('#script_body').val(editor.getValue());
  });
  //selects are image pickers
  $('select[name="image"]').imagepicker();
  //TITLE functions
  //change preview on keyup
  $('input[name="title"]').keyup(function() {
    $('.preview .content .title').text($(this).val());
  });
  //set when page loads
  $('.preview .content .title').text($('input[name="title"]').val());
  //DESCRIPTION functions
  //change preview on keyup
  $('textarea[name="description"]').keyup(function() {
    $('.preview .content .description').text($(this).val());
  });
  //set when page loads
  $('.preview .content .description').text($('textarea[name="description"]').val());
  //IMAGE functions
  //when the image select changes, change the image preview
  $('select[name="image"]').change(function() {
    $('.preview .image img').attr('src', $('select[name="image"] option:selected').val());
  });
  //set when page loads
  $('.preview .image img').attr('src', $('select[name="image"] option:selected').val());
  //shorten URL accordions
  $('#sharing-icon .collapse-head').click(function() {
    $("#sharing-icon .collapsable-icon").toggleClass('collapsed-icon');
  });
  $('#campaign-icon .collapse-head').click(function() {
    $("#campaign-icon .collapsable-icon").toggleClass('collapsed-icon');
  });
  $('#assign-icon .collapse-head').click(function() {
    $("#assign-icon .collapsable-icon").toggleClass('collapsed-icon');
  });
  $('#add-icon .collapse-head').click(function() {
    $("#add-icon .collapsable-icon").toggleClass('collapsed-icon');
  });
  $('input#analytics_advanced').on('ifChecked', function(event){
	  $('div#analytics_advanced_container').removeClass('hidden');
	});
	$('input#analytics_advanced').on('ifUnchecked', function(event){
	  $('div#analytics_advanced_container').addClass('hidden');
	});

  $('input[name=url_type]').on('ifChecked', function(event){
    var url_type = $(this).prop('id');
    var tracking_options = $('#tracking_options').contents();
    if (url_type === "ad"){
      $('.basic,#parse_right').hide();
    }else{
      $('.basic,#parse_right').show();
    }
  });

  //verify the user selected a group to associate a link with
  $('form#shorten_url').submit('submit',function(e){
    if( $('#url_group').is(":visible") ){
      if($('input.group[type=checkbox]:checked').length === 0){
        $('div#group_selector').removeClass('hidden');
        return false;
      }else{
        $('div#group_selector').addClass('hidden');
      }
    }
  });

  
  /***********************************
   * START CAMPAIGNS
   ***********************************/
  
  var $deleteSourceBtn = $("#delete-source-btn");
  var $deleteMediumBtn = $("#delete-medium-btn");
  var $addSourceBtn = $("#add-source-btn");
  var $addMediumBtn = $("#add-medium-btn");
  
  $('input').on('ifChecked',function(e){
    var object_name = $(this).attr('name');

    if( object_name === "mediums[]" || object_name === "sources[]" ){
      deleteSourceMedium();
    }
  });
  
  $('input').on('ifUnchecked',function(e){
    var object_name = $(this).attr('name');

    if( object_name === "mediums[]" || object_name === "sources[]" ){
      deleteSourceMedium();
    }
  });

  
  //hide the delete buttons on load
  $deleteSourceBtn.hide();    
  $deleteMediumBtn.hide();
  
  window.deleteSourceMedium = function(){
  
    if($('input[name="mediums[]"]:checked').length > 0){
      $deleteMediumBtn.show();  
    }else{
      $deleteMediumBtn.hide();    
    }
    
    if($('input[name="sources[]"]:checked').length > 0){
      $deleteSourceBtn.show();  
    }else{
      $deleteSourceBtn.hide();    
    }
  }
  
  
  
  //Check source mediums to clean up screen
  function checkSources(){
    if( !$('form#sources div.form-group').exists() ){
      $addSourceBtn.show();
      $('form#sources').hide();
      deleteSourceMedium();
    }
  }
  function checkMediums(){
    if( !$('form#mediums div.form-group').exists() ){
      $addMediumBtn.show();
      $('form#mediums').hide();
      deleteSourceMedium();
    }
  }
  
  //control the actions on the campaigns page
  $(document).on('click','a.utm',function(e){
  
    e.preventDefault();
      var action = $(this).attr('data-utm');
      
      if( action === "medium add" ){
      
        $('form#mediums').show();
        $addMediumBtn.hide();
        
        $('<div class="form-group input-group"><input class="form-control" type="text" name="medium_name[]"><span class="input-group-addon"><a class="utm" data-utm="medium add"><i class="fa fa-plus"></i></a></span><span class="input-group-addon"><a class="utm" data-utm="medium subtract"><i class="fa fa-minus"></i></a></span></div>').insertBefore('form#mediums input[type="submit"]');
      
      }else if( action === "source add" ){
      
        $('form#sources').show();
        $addSourceBtn.hide(); 
        
        $('<div class="form-group input-group"><input class="form-control" type="text" name="source_name[]"><span class="input-group-addon"><a class="utm" data-utm="source add"><i class="fa fa-plus"></i></a></span><span class="input-group-addon"><a class="utm" data-utm="source subtract"><i class="fa fa-minus"></i></a></span></div>').insertBefore('form#sources input[type="submit"]');
   
    }else if( action === "source subtract"){
    
    $(this).parents('div.form-group').remove();
    checkSources();
      
    }else if( action === "medium subtract"){
    
    $(this).parents('div.form-group').remove();
    checkMediums();
      
    }else if( action === "medium delete" || action === "source delete" ){
    
      //control the sources or mediums that are deleted
      if( action === "medium delete" ){
      
        var submit_url = "/campaigns/mediums";
        var confirmDelete = confirm("Are you sure you want to delete these mediums? This action cannot be undone.");
        if(confirmDelete){
          $('input[name="mediums[]"]:checked').each(function(index){
            submit_url += "/"+$(this).val();
            $(this).parent().remove();
          });
        }
        
      }else if( action === "source delete" ){
      
        var submit_url = "/campaigns/sources";
        var confirmDelete = confirm("Are you sure you want to delete these sources? This action cannot be undone.");
        if(confirmDelete){
          $('input[name="sources[]"]:checked').each(function(index){
            submit_url += "/"+$(this).val();
            $(this).parent().remove();
          });
        }
        
      }

      //delete the selected utm
      $.ajax({
        type: "DELETE",
        url: submit_url,
        dataType: 'json',
        cache: false,
        success: function(data, textStatus){
          //show success or error messages
          //alert_messages(data);
          window.location.reload();
        }
      });
    }

  });
  
  /***********************************
   * END CAMPAIGNS
   ***********************************/

/*
  //update the mediums and sources list
  $(document).on('submit','form#sources,form#mediums',function(e){
    e.preventDefault();

    var submit_url = $(this).attr('action');
    var utm_form = $(this).attr('id');
    var new_utm_json = $(this).serializeObject();
    var form_data = $(this).serialize();
      
    //remove the input fields from the field
    $('form#'+utm_form+' div.form-group').remove();
    $('form#'+utm_form).hide();
    //update the utm after the list appears in the dom so the user doesn't keep submitting
    $.ajax({
      data:  form_data,
      type: "POST",
      url: submit_url,
      dataType: 'json',
      cache: false,
      success: function(data, textStatus){
        var list;
        for(var i=0;i<data.length;i++){
          if( data[i].result ){
            for(var j=0;j<data[i].result.length;j++){
              if( typeof list === "undefined" )
                list = "<li><input type='checkbox' name='"+utm_form+"[]' value='"+data[i].result[j].id+"'> "+data[i].result[j].name+" <a href='/campaigns/"+utm_form+"/"+data[i].result[j].id+"'>edit</a></li>";
              else
                list += "<li><input type='checkbox' name='"+utm_form+"[]' value='"+data[i].result[j].id+"'> "+data[i].result[j].name+" <a href='/campaigns/"+utm_form+"/"+data[i].result[j].id+"'>edit</a></li>";
            }
          }
        }

        if( $('ul#'+utm_form).length === 0 )
          $('div#'+utm_form).append('<ul id="'+utm_form+'">'+list+'</ul>');
        else
          $('ul#'+utm_form).append(list);

        //show success or error messages
        alert_messages(data);
      }

    });
  });
*/
  //control the users page
  $(document).on('click','a.lightbox',function(e){
    e.preventDefault();
    var action = $(this).attr('data-action');
    if( action === "add user" ){
      $('div#add_user.modal').modal({show:true});
    }

    if( action === "add group" ){
      $('div#add_group.modal').modal({show:true});
    }
  });
  
  //add a user using ajax
  $(document).on('submit','form#add_user,form#add_group',function(e){
    e.preventDefault();
    var form_id = $(this).attr('id');
    var submit_url = $(this).attr('action');
    $.ajax({
      url: submit_url,
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(data,textStatus){
        window.location.reload();
      },
      error: function(xhr,textStatus, error){
        var error_message = JSON.parse(xhr.responseText);
        if( form_id === "add_user" )
          $('#add_user_error').show();
        else if( form_id === "add_group" )
          $('#add_group_error').show();

        var display_message;
        for(var i=0;i<error_message.errors.length;i++){
          if( typeof display_message === "undefined" ){
            display_message = "<li>"+error_message.errors[i]+"</li>";
          }else{
            display_message += "<li>"+error_message.errors[i]+"</li>";
          }
        }
        $('ul#'+form_id).append(display_message);
      }
    });
  });

  //dashboard
  $('table#dashboard').dataTable({
    "oLanguage": {
        "sEmptyTable": "You haven't created any URLs. <a class='btn btn-primary' id='create_first_url' data-action='uncloned'>Start Tracking</a>"
    },
    'aaSorting': [[1,'desc']]
  });

  //clone the short URL form to the data table
  $(document).on('click','a#create_first_url',function(){
    var attr = $(this).attr('data-action');
    if( attr === "uncloned" ){
      $('form#parse_url').clone().insertAfter('a#create_first_url');
      $('a#create_first_url').attr('data-action','cloned');
    }
  });

  //dashboard datepickers
  $('#start_date').datepicker({
    autoclose: true,
    format: 'mm/dd/yy',
    todayHighlight: true
  }).on('changeDate',function(e){
    var selected_date = $('#start_date').datepicker('getDate');
    $('#end_date').datepicker('setStartDate',new Date(selected_date));
    $('#start_date').datepicker('hide');
    $('#end_date').datepicker('show');
  });
  $(document).on('click','a.fa-calendar',function(e){
    var action = $(this).attr('data-action');

    if( action === "dashboard_start_date"){
      $('#start_date').datepicker('show');
    }else if( action === "dashboard_end_date"){
      $('#end_date').datepicker('show');
    }
  });

  var start_date = decodeURIComponent(getUrlVars()["start_date"]);

  $('#end_date').datepicker({
    autoclose: true,
    format: 'mm/dd/yy',
    todayHighlight: true
  });
  var end_date = decodeURIComponent(getUrlVars()["end_date"]);
  
  //update the date pickers on the dashboard with the selcted date range
  if( start_date != "undefined" && end_date != "undefined" ){
    $('#start_date').val(start_date);
    $('#end_date').val(end_date);
  }else{
    //if there is no date range in the URL, show the date range dor the past week
    var date = new Date();
    end_date = ("0" + (date.getMonth() + 1).toString()).substr(-2) + "/" + ("0" + date.getDate().toString()).substr(-2)  + "/" + (date.getFullYear().toString()).substr(2);
    $('#start_date').val(getLastWeek());
    $('#end_date').val(end_date);
  }
  
  $('.group-tooltip').tooltip();

  //users table
  $('table.users').dataTable();
  //dashboard select
  $('form#dashboard_select input[type="submit"]').addClass('hidden');
  $('select#dashboard').multiselect({
    onChange: function(element,checked){
      //check if any of the checkboxes are selected
      var anychecked = $('ul.multiselect-container input[type="checkbox"]:checked').length > 0;
      if( anychecked ){
        $('form#dashboard_select input[type="submit"]').removeClass('hidden');
      }else if( !anychecked ){
        $('form#dashboard_select input[type="submit"]').addClass('hidden');
      }
    }
  });

  /***********************************
   * START DASHBOARD DROPDOWN
   ***********************************/
  $('select#dashboard_view').multiselect({
    maxHeight: 200,
    onChange: function(element,checked){
    }
  });

  //
  $('select#dashboard_view + div.btn-group input').on('ifChecked',function(event){

    // get which optgroup user selected
    var optGroup = $('option[value="'+event.currentTarget.value+'"]').closest('optgroup').index();

    if( event.currentTarget.value === "group_all" ){
  		$('optgroup[label="Group Dashboards"] option').each(function(){
  			console.log('go');
  			var val = $(this).val();
  			$('input[value="'+val+'"]').iCheck('check');
  			$('select#dashboard_view').multiselect('select',val);
  		});
		}else{
    	$('select#dashboard_view').multiselect('select',event.currentTarget.value);
  	}
    $('input#dashboard_update').removeClass('hidden');

    var not_selected = null;
    if( optGroup == "0" ){
      if( event.currentTarget.value == "my" ){
        $('form#dashboard_switch').prop('action','/');
        not_selected = 'option#all,option#ad_network,optgroup[label="Group Dashboards"] option,optgroup[label="User Dashboard"] option';
      }else if( event.currentTarget.value == "all"){
        $('form#dashboard_switch').prop('action','/dashboard/urls');
        not_selected = 'option#my,option#ad_network,optgroup[label="Group Dashboards"] option,optgroup[label="User Dashboard"] option';
      }else if( event.currentTarget.value == "ad_network"){
        $('form#dashboard_switch').prop('action','/dashboard/ad-networks');
        not_selected = 'option#my,option#all,optgroup[label="Group Dashboards"] option,optgroup[label="User Dashboards"] option';
      }
    }else if( optGroup == "1" ){
      $('form#dashboard_switch').prop('action','/dashboard/groups');
      not_selected = 'optgroup[label="Main Dashboards"] option, optgroup[label="User Dashboards"] option';
    }else if( optGroup == "2" ){
      $('form#dashboard_switch').prop('action','/dashboard/users');
      not_selected = 'optgroup[label="Main Dashboards"] option,optgroup[label="Group Dashboard"] option';
    }

    //deselect all options of the other groups
    $(not_selected).each(function(index){
      var option_value = $(this).val();
      $('input[value="'+option_value+'"]').iCheck('uncheck');
      $('select#dashboard_view').multiselect('deselect',option_value);
    });
  });

  $('select#dashboard_view + div.btn-group input').on('ifUnchecked',function(event){
    $('select#dashboard_view').multiselect('deselect',event.currentTarget.value);
  });

  $('select#dashboard_view + div.btn-group input').iCheck({
    checkboxClass: 'icheckbox icheckbox_square'
  });

  //show the dashboard select form once the multiselect box is done showing
  $('form#dashboard_select').removeClass('invisible');
  //select the group dashboard, redirect to group dashboard page with parameters
  $(document).on('submit','form#dashboard_select',function(e){
    e.preventDefault();
    var submit_url = $(this).attr('action');
    var group_selector;
    $('ul.multiselect-container input[type="checkbox"]:checked').each(function(index){
      if( typeof group_selector === 'undefined' )
        group_selector = $(this).val();
      else
        group_selector += "/" + $(this).val();
    });
    window.location = submit_url+"/"+group_selector;
  });
  
  //dashboard selector
  $(document).on('submit','form#dashboard_switch',function(e){
    e.preventDefault();
    var submit_url = $(this).attr('action');

    var selected_values = $(this).serializeArray();

    var selector = null;
    //grab the last value of the selector
    $.each(selected_values,function(){
      if( selector === null ){
      	console.log(this.value);
      	if( this.value !== "group_all" ){
	        if( this.value.indexOf("group") != -1 || this.value.indexOf("user") != -1 ){
	          selector = this.value.slice(-1);
	        }else{
	          selector = this.value;
	        }
      	}
      }else{
      	if( this.value !== "group_all" ){
	        if( this.value.indexOf("group") != -1 || this.value.indexOf("user") != -1 ){
	          selector += "/"+this.value.slice(-1);
	        }else{
	          selector = this.value;
	        }
      	}
      }
    });

    if( selector === "ad_network" ){
      window.location = submit_url;
    }else if( selector === "my" ){
      window.location = submit_url;
    }else if( selector === "all" ){
      window.location = submit_url;
    }else if( selector !== "my" || selector !== "ad_network"){
      window.location = submit_url+"/"+selector;
    }
  });
  /***********************************
   * END DASHBOARD DROPDOWN
   ***********************************/

  //set the ace editors to disabled
  $('div.ace_disabled textarea').attr('disabled','disabled');

  //social status dropdown list
  $('#social_network').selectpicker();
  $('#new_social_site').hide();
  //ability to add a new website to the social network list
  $(document).on('click','ul.selectpicker li:last-child',function(e){
    $('#new_social_site').show();
  });
  //

});

//get the $ 
function get_utm(form_id,json_object){
  //create the $ Deferred object that will be used
  var deferred = $.Deferred();
  var list;
  //loop through the new key value pairs and attach to dom
  if( form_id === "mediums" ){
    for(var i = 0; i < json_object.medium_name.length; i++) {
      if( json_object.medium_name[i] ){
        if( typeof list === "undefined" )
          list = "<li>"+json_object.medium_name[i]+"</li>";
        else
          list += "<li>"+json_object.medium_name[i]+"</li>"; 
      }
    }
  }else if( form_id === "sources" ){
    for(var i = 0; i < json_object.source_name.length; i++) {
      if( typeof list === "undefined" )
        list = "<li>"+json_object.source_name[i]+"</li>";
      else
        list += "<li>"+json_object.source_name[i]+"</li>"; 
    }
  }

  deferred.resolve(list);

  return deferred.promise();
}
//loop through the error and success messages. show them on screen
function alert_messages(object){

  for(var i=0;i<object.length;i++){
    if( object[i].success ){
      for(var j=0;j<object[i].success.length;j++){
        alertify.success(object[i].success[j]);
      }
    }else if( object[i].error ){
      for(var j=0;j<object[i].error.length;j++){
        alertify.error(object[i].error[j]);
      }
    }
  }
}

//read GET paramaters in javascript
function getUrlVars() {
  var vars = {};
  var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
  vars[key] = value;
  });
  return vars;
}

function getLastWeek(){
  var today = new Date();
  var lastWeek = new Date(today.getFullYear(), today.getMonth(), today.getDate() - 7);

  lastWeek = ("0" + (lastWeek.getMonth() + 1).toString()).substr(-2) + "/" + ("0" + lastWeek.getDate().toString()).substr(-2)  + "/" + (lastWeek.getFullYear().toString()).substr(2)
  return lastWeek ;
}