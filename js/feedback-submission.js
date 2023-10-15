jQuery(document).ready(function($) {
    $('#houzeo-feedback-form').on('submit', function(e) {
        e.preventDefault();
        var phoneField = $('#houzeo-feedback-form input[name="phone"]');
        var phoneRegex = /^\d{3}-\d{3}-\d{4}$/;

       if (!phoneRegex.test(phoneField.val())) {
           //alert('.');
           $('#feedback-message').addClass('err_cls');
           $('#feedback-message').html("Please enter a valid US phone number in the format XXX-XXX-XXXX,eg:555-555-5555");
           return;
       }

        var formData = $(this).serialize();

        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: 'action=submit_feedback&' + formData,
            success: function(response) {
              $('#feedback-message').removeClass('err_cls');
                $('#feedback-message').html(response);
                $('#houzeo-feedback-form')[0].reset();

                $.ajax({
                   type: 'GET',
                   url: ajax_object.ajax_url,
                   data: 'action=refresh_feedback_list',
                   success: function(response) {
                      if ($('#feedback-list').hasClass('hide_table'))
                      {
                          $('#feedback-list').removeClass('hide_table');
                      }
                      $('#feedback-list table tbody').append(response);
                   }
               });



            }
        });
    });
});
