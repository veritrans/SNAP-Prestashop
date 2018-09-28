 $(function() {  

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

  //  HANDLE SHOW ADVANCED

  function changeViewAdvancedParents(arr,type){
    console.log('changeViewAdvancedParents',type);
    for (var i = 0; i < arr.length; i++) {
      $(arr[i]).parent().parent().parent()[type]();
    }
  }

  function toggleAdvanced(){
    var advanceds = [
      '#MT_ENABLED_INSTALLMENTON_BTN_on',
      '#MT_ENABLED_INSTALLMENTOFF_BTN_on',
      '#MT_ENABLED_PROMO_BTN_on',
      '#MT_ENABLED_MIGS_BTN_on',
      '#MT_ENABLED_INSTALLMENTMIGS_BTN_on',
      '#MT_DISABLE_NON_MIGS_BTN_on',
      '#MT_ENABLED_SAVECARD_on',
      '#MT_ENABLED_FIELDS_on',
      '#MT_ENABLED_CUSTOMVA_BTN_on',
      '#MT_ENABLED_EXPIRY_on',
      '#MT_ENABLED_IGNORE_DENY_on',
      '#MT_ENABLED_REDIRECT_on',
      '#MT_ENABLED_MAP_FINISH_URL_on',
    ];
    if ($("#MT_ENABLED_ADV_on").prop('checked')){
      changeViewAdvancedParents(advanceds,'show'); 
    } 
    else if ($("#MT_ENABLED_ADV_off").prop('checked')) {
      changeViewAdvancedParents(advanceds,'hide'); 
    }
  }

  toggleAdvanced();
  $("#MT_ENABLED_ADV_on").change(function(e, data) {
    toggleAdvanced();
  });
  $("#MT_ENABLED_ADV_off").change(function(e, data) {
    toggleAdvanced();
  });

  // END OF HANDLE SHOW ADVANCED


  function toggleENABLED_INSTALLMENTON_BTN(){
    if ($("#MT_ENABLED_INSTALLMENTON_BTN_on").prop('checked'))
      showOptions('advanced-on', "class");
    else if ($("#MT_ENABLED_INSTALLMENTON_BTN_off").prop('checked'))
      hideOptions('advanced-on', "class");
  }

  function toggleENABLED_INSTALLMENTOFF_BTN(){
    if ($("#MT_ENABLED_INSTALLMENTOFF_BTN_on").prop('checked'))
      showOptions('advanced-off', "class");
    else if ($("#MT_ENABLED_INSTALLMENTOFF_BTN_off").prop('checked'))
      hideOptions('advanced-off', "class");
  }

  function toggleENABLED_PROMO_BTN(){
    if ($("#MT_ENABLED_PROMO_BTN_on").prop('checked'))
      showOptions('advanced-promo', "class");
    else if ($("#MT_ENABLED_PROMO_BTN_off").prop('checked'))
      hideOptions('advanced-promo', "class");
  }

  function toggleENABLED_MIGS_BTN(){
    if ($("#MT_ENABLED_MIGS_BTN_on").prop('checked'))
      showOptions('advanced-migs', "class");
    else if ($("#MT_ENABLED_MIGS_BTN_off").prop('checked'))
      hideOptions('advanced-migs', "class");
  }

  function toggleENABLED_INSTALLMENTMIGS_BTN(){
    if ($("#MT_ENABLED_INSTALLMENTMIGS_BTN_on").prop('checked'))
      showOptions('advanced-insmigs', "class");
    else if ($("#MT_ENABLED_INSTALLMENTMIGS_BTN_off").prop('checked'))
      hideOptions('advanced-insmigs', "class");
  }

  function toggleMT_ENABLED_EXPIRY(){
    if ($("#MT_ENABLED_EXPIRY_on").prop('checked'))
      showOptions('advanced-expiry', "class");
    else if ($("#MT_ENABLED_EXPIRY_off").prop('checked'))
      hideOptions('advanced-expiry', "class");
  }

  function toggleMT_ENABLED_FIELDS(){
    if ($("#MT_ENABLED_FIELDS_on").prop('checked'))
      showOptions('advanced-fields', "class");
    else if ($("#MT_ENABLED_FIELDS_off").prop('checked'))
      hideOptions('advanced-fields', "class");
  }
  function toggleMT_ENABLED_CUSTOMVA_BTN(){
    if ($("#MT_ENABLED_CUSTOMVA_BTN_on").prop('checked'))
      showOptions('advanced-customva', "class");
    else if ($("#MT_ENABLED_CUSTOMVA_BTN_off").prop('checked'))
      hideOptions('advanced-customva', "class");
  }

  
  // Add listener to switch elements
  function addListenerToSwitch(){
    $("#MT_ENABLED_INSTALLMENTON_BTN_on").change(function(e, data) {
      toggleENABLED_INSTALLMENTON_BTN();
    });
    $("#MT_ENABLED_INSTALLMENTOFF_BTN_on").change(function(e, data) {
      toggleENABLED_INSTALLMENTOFF_BTN();
    });
    $("#MT_ENABLED_PROMO_BTN_on").change(function(e, data) {
      toggleENABLED_PROMO_BTN();
    });
    $("#MT_ENABLED_MIGS_BTN_on").change(function(e, data) {
      toggleENABLED_MIGS_BTN();
    });
    $("#MT_ENABLED_INSTALLMENTMIGS_BTN_on").change(function(e, data) {
      toggleENABLED_INSTALLMENTMIGS_BTN();
    });
    $("#MT_ENABLED_EXPIRY_on").change(function(e, data) {
      toggleMT_ENABLED_EXPIRY();
    });
    $("#MT_ENABLED_FIELDS_on").change(function(e, data) {
      toggleMT_ENABLED_FIELDS();
    });
    $("#MT_ENABLED_CUSTOMVA_BTN_on").change(function(e, data) {
      toggleMT_ENABLED_CUSTOMVA_BTN();
    });

    $("#MT_ENABLED_INSTALLMENTON_BTN_off").change(function(e, data) {
      toggleENABLED_INSTALLMENTON_BTN();
    });
    $("#MT_ENABLED_INSTALLMENTOFF_BTN_off").change(function(e, data) {
      toggleENABLED_INSTALLMENTOFF_BTN();
    });
    $("#MT_ENABLED_PROMO_BTN_off").change(function(e, data) {
      toggleENABLED_PROMO_BTN();
    });
    $("#MT_ENABLED_MIGS_BTN_off").change(function(e, data) {
      toggleENABLED_MIGS_BTN();
    });
    $("#MT_ENABLED_INSTALLMENTMIGS_BTN_off").change(function(e, data) {
      toggleENABLED_INSTALLMENTMIGS_BTN();
    });
    $("#MT_ENABLED_EXPIRY_off").change(function(e, data) {
      toggleMT_ENABLED_EXPIRY();
    });
    $("#MT_ENABLED_FIELDS_off").change(function(e, data) {
      toggleMT_ENABLED_FIELDS();
    });
    $("#MT_ENABLED_CUSTOMVA_BTN_off").change(function(e, data) {
      toggleMT_ENABLED_CUSTOMVA_BTN();
    });
  }

  function initializeVisibility(){
    toggleENABLED_INSTALLMENTON_BTN();
    toggleENABLED_INSTALLMENTOFF_BTN();
    toggleENABLED_MIGS_BTN();
    toggleENABLED_PROMO_BTN();
    toggleENABLED_INSTALLMENTMIGS_BTN();
    toggleMT_ENABLED_EXPIRY();
    toggleMT_ENABLED_FIELDS();
    toggleMT_ENABLED_CUSTOMVA_BTN();
  }

  addListenerToSwitch();
  initializeVisibility();

  function hideSavecard(hideSavecard = true){
    if (hideSavecard)
      $('#MT_ENABLED_SAVECARD_on').parent().parent().parent().hide();
  } hideSavecard(false);

});