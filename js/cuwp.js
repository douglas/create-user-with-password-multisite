jQuery(document).ready(function ($) {
    // controls match of passwords
    $('#createuser input[name="cuwp_pass1"]').keyup(function () {
        var pass1 = $(this).val();
        var pass2 = $('#createuser input[name="cuwp_pass2"]').val();

        if (pass1 !== pass2) {
            $('#createuser .pass-error').show();
        } else {
            $('#createuser .pass-error').hide();
        }
    });

    $('#createuser input[name="cuwp_pass2"]').keyup(function () {
        var pass1 = $(this).val();
        var pass2 = $('#createuser input[name="cuwp_pass1"]').val();

        if (pass1 !== pass2) {
            $('#createuser .pass-error').show();
        } else {
            $('#createuser .pass-error').hide();
        }
    });


    // passwords are not the same, returns
    $('#createuser input[type="submit"]').click(function (e) {
        var pass1 = $('#createuser input[name="cuwp_pass1"]').val();
        var pass2 = $('#createuser input[name="cuwp_pass2"]').val();
        if (pass1 !== pass2) {
            e.preventDefault();
        }
    });
    
    // check button
    $('#createuser input[name="noconfirmation"]').prop('checked', true);
    
    // hide pass when sending inv
    $('#createuser input[name="noconfirmation"]').click(function(){
        if($(this).is(":checked") === false){
            $('#createuser .hook-pass').slideUp();
        } else {
            $('#createuser .hook-pass').slideDown();
        }
    });
});
