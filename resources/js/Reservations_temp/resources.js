$(document).ready(function ($) {
    'use strict';

    window.csrfToken = $('meta[name="csrf-token"]').attr('content');

    if( typeof $('input#name').val() !== 'undefined' && $('input#name').val().length !== 0) {
        let updateName = $('input#name').val();
        $('input#name_hid').val(updateName);
    }

    if( typeof $('input#resource_type').val() !== 'undefined' && $('input#resource_type').val().length !== 0) {
        let resourceType = $('input#resource_type').val();
        $('input#resource_type_hid').val(resourceType);
    }

}); // /Document Ready