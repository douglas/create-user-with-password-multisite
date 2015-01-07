jQuery(document).ready(function ($) {
    // controls match of passwords
    $('#createuser input[name="pass1"]').keyup(function () {
        var pass1 = $(this).val();
        var pass2 = $('#createuser input[name="pass2"]').val();

        console.log(pass1 + ' - ' + pass2);
        if (pass1 !== pass2) {
            $('#createuser .pass-error').show();
        } else {
            $('#createuser .pass-error').hide();
        }
    });

    $('#createuser input[name="pass2"]').keyup(function () {
        var pass1 = $(this).val();
        var pass2 = $('#createuser input[name="pass1"]').val();

        console.log(pass1 + ' - ' + pass2);
        if (pass1 !== pass2) {
            $('#createuser .pass-error').show();
        } else {
            $('#createuser .pass-error').hide();
        }
    });


    // passwords are not the same, returns
    $('#createuser input[type="submit"]').click(function (e) {
        var pass1 = $('#createuser input[name="pass1"]').val();
        var pass2 = $('#createuser input[name="pass2"]').val();
        if (pass1 !== pass2) {
            e.preventDefault();
        }
    });
});
