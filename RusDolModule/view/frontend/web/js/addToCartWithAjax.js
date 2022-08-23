//код для проверки доп. задания в 6-м уроке
define([
    "jquery",
    'Magento_Customer/js/customer-data'
],function($, customerData) {
    $(document).ready(function() {
        $('.rus-form').submit(function(e) {

            e.preventDefault();

            var form = $(this);
            var actionUrl = form.attr('action');

            $.ajax({
                type: "POST",
                url: actionUrl,
                data: form.serialize(),
                success: function()
                {
                    console.log('success');
                }
            });
        });
    });
});