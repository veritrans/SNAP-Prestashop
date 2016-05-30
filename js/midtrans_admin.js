 $(function() {  

  function sensitiveOptions() {
    var api_version = '2' ;//$('#midtransApiVersion').val();
    var payment_type = $('#midtransPaymentType').val();
    var api_string = 'v' + api_version + '_settings';
    var payment_type_string = payment_type;
    var api_payment_type_string = 'v' + api_version + '_' + payment_type + '_settings';
    
    // 1.6 default back-office theme
    $('.sensitive').closest('.form-group').hide();
    // 1.5 default back-office theme
    $('.sensitive').closest('.margin-form').hide();
    $('.sensitive').closest('.margin-form').prev().hide();

    // 1.6 default back-office theme
    $('.' + api_string).closest('.form-group').show();
    $('.' + payment_type_string).closest('.form-group').show();
    $('.' + api_payment_type_string).closest('.form-group').show();

    // 1.5 default back-office theme
    $('.' + api_string).closest('.margin-form').show();
    $('.' + payment_type_string).closest('.margin-form').show();
    $('.' + api_payment_type_string).closest('.margin-form').show();
    $('.' + api_string).closest('.margin-form').prev().show();
    $('.' + payment_type_string).closest('.margin-form').prev().show();
    $('.' + api_payment_type_string).closest('.margin-form').prev().show();
  }

  $("#midtransPaymentType").on('change', function(e, data) {
    sensitiveOptions();
  });
  
  function setCheckedAttribute(name){
    var $radios = $('input:radio[name='+ name+']');
    $radios.filter('[value=' + 0 +']').attr('checked', true);
  }

  function hideOptions(nameClass, type){
    if(type=="class"){
      $("."+nameClass).closest('.form-group').hide();    
      $("."+nameClass).closest('.margin-form').hide(); 
      $("."+nameClass).closest('.margin-form').prev().hide();    
    } else {
      $("#"+nameClass).closest('.form-group').hide();    
      $("#"+nameClass).closest('.margin-form').hide();
      $("#"+nameClass).closest('.margin-form').prev().hide();    
    }
  }

  function showOptions(nameClass, type){
    if (type=="class"){
      $("."+nameClass).closest('.form-group').show();
      $("."+nameClass).closest('.margin-form').show();
      $("."+nameClass).closest('.margin-form').prev().show();
    }else{
      $("#"+nameClass).closest('.form-group').show();
      $("#"+nameClass).closest('.margin-form').show();
      $("#"+nameClass).closest('.margin-form').prev().show();
    }  
  } 

  function setUpVisibility(){    
    var a = $("#MT_ENABLE_INSTALLMENT").val();      
    switch(a){
      case "off":            
            //alert("off " + a + " " + EnableMandiri + " " + EnableBni);
            setCheckedAttribute('MT_ENABLED_BNI_INSTALLMENT');
            setCheckedAttribute('MT_ENABLED_MANDIRI_INSTALLMENT');
            hideOptions('MT_INSTALLMENTS_MANDIRI', "class");
            hideOptions('MT_ENABLED_MANDIRI_INSTALLMENT_off', "div");                                    
            hideOptions('MT_INSTALLMENTS_BNI', "class");
            hideOptions('MT_ENABLED_BNI_INSTALLMENT_off', "div");                                                                
            break;
      case "certain_product":          
            //alert("cp " + a + " " + EnableMandiri + " " + EnableBni);
            hideOptions('MT_INSTALLMENTS_MANDIRI', "class");
            showOptions('MT_ENABLED_MANDIRI_INSTALLMENT_off', "div");
            hideOptions('MT_INSTALLMENTS_BNI', "class");
            showOptions('MT_ENABLED_BNI_INSTALLMENT_off', "div");            
            break;
      case "all_product":                                
            showOptions('MT_ENABLED_MANDIRI_INSTALLMENT_off', "div");            
            showOptions('MT_ENABLED_BNI_INSTALLMENT_off', "div");    
            var EnableBni = $('input:radio[name=MT_ENABLED_BNI_INSTALLMENT]:checked').val();
            var EnableMandiri = $('input:radio[name=MT_ENABLED_MANDIRI_INSTALLMENT]:checked').val();                     
            
            if (EnableMandiri == 1){              
              showOptions('MT_INSTALLMENTS_MANDIRI', "class");
            } else {
              hideOptions('MT_INSTALLMENTS_MANDIRI', "class");
            }

            if (EnableBni == 1){           
              showOptions('MT_INSTALLMENTS_BNI', "class");
            } else {
              hideOptions('MT_INSTALLMENTS_BNI', "class");
            }
            break;
    }
  }

  $("#MT_ENABLE_INSTALLMENT").on('change', function(e, data) {       
    setUpVisibility();
  });
  
  $("#MT_ENABLED_BNI_INSTALLMENT_off").on('change', function(e, data) {    
     hideOptions('MT_INSTALLMENTS_BNI', "class");   
  });

  $("#MT_ENABLED_BNI_INSTALLMENT_on").on('change', function(e, data) {    
     var a = $("#MT_ENABLE_INSTALLMENT").val();
     if (a == "all_product")
      showOptions('MT_INSTALLMENTS_BNI', "class");   
  });

  $("#MT_ENABLED_MANDIRI_INSTALLMENT_off").on('change', function(e, data) {    
     hideOptions('MT_INSTALLMENTS_MANDIRI', "class");   
  });

  $("#MT_ENABLED_MANDIRI_INSTALLMENT_on").on('change', function(e, data) {    
      var a = $("#MT_ENABLE_INSTALLMENT").val();
      if (a == "all_product")
        showOptions('MT_INSTALLMENTS_MANDIRI', "class");   
  });
  
  setUpVisibility();
  sensitiveOptions();
});